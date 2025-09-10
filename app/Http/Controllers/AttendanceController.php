<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $business = Auth::user()->business;

        $query = Attendance::whereHas('employee', function ($query) use ($business) {
            $query->where('business_id', $business->id);
        })->with('employee');

        $filterDate = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $query->where('date', $filterDate);

        $attendances = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate work duration for each record
        $attendances->getCollection()->transform(function ($attendance) {
            if ($attendance->check_in && $attendance->check_out) {
                $checkIn = Carbon::parse($attendance->check_in);
                $checkOut = Carbon::parse($attendance->check_out);
                $duration = $checkIn->diff($checkOut);
                $attendance->work_duration = $duration->format('%h h %i m');
            } else {
                $attendance->work_duration = 'N/A';
            }
            return $attendance;
        });

        return view('attendances.index', compact('attendances', 'filterDate'));
    }

    public function create()
    {
        $business = Auth::user()->business;
        $employees = $business->employees()->where('status', 'active')->orderBy('name')->get();
        return view('attendances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'check_in'    => 'required|date_format:H:i',
            'check_out'   => 'required|date_format:H:i|after:check_in',
            'status'      => 'required|in:present,absent,leave,half-day',
        ]);

        Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            ['check_in' => $validated['check_in'], 'check_out' => $validated['check_out'], 'status' => $validated['status']]
        );

        return redirect()->route('attendances.index', ['date' => $validated['date']])->with('success', 'Attendance recorded successfully.');
    }

    public function update(Request $request, Attendance $attendance)
    {
        if ($attendance->employee->business_id !== Auth::user()->business_id) {
            abort(403);
        }

        $validated = $request->validate([
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i|after_or_equal:check_in',
            'status'      => 'required|in:present,absent,leave,half-day',
        ]);

        if ($validated['status'] === 'absent' || $validated['status'] === 'leave') {
            $validated['check_in'] = null;
            $validated['check_out'] = null;
        }

        $attendance->update($validated);
        
        return redirect()->route('attendances.index', ['date' => $attendance->date->format('Y-m-d')])->with('success', 'Attendance updated successfully.');
    }

    public function bulkCreate()
    {
        return view('attendances.bulk_create');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.employee_id' => 'required|exists:employees,id',
            'attendances.*.check_in' => 'nullable|date_format:H:i',
            'attendances.*.check_out' => 'nullable|date_format:H:i|after_or_equal:attendances.*.check_in',
            'attendances.*.status' => 'required|in:present,absent,leave,half-day',
        ]);

        foreach ($request->attendances as $attData) {
            if (empty($attData['check_in']) && empty($attData['check_out'])) {
                if ($attData['status'] === 'absent' || $attData['status'] === 'leave') {
                    Attendance::updateOrCreate(
                        ['employee_id' => $attData['employee_id'], 'date' => $request->date],
                        ['status' => $attData['status'], 'check_in' => null, 'check_out' => null]
                    );
                }
                continue;
            }
            
            Attendance::updateOrCreate(
                ['employee_id' => $attData['employee_id'], 'date' => $request->date],
                [
                    'check_in'  => $attData['check_in'] ?? null,
                    'check_out' => $attData['check_out'] ?? null,
                    'status'    => $attData['status'],
                ]
            );
        }

        return redirect()->route('attendances.index', ['date' => $request->date])->with('success', 'Attendance sheet saved successfully.');
    }

    public function getEmployeesForAttendance(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $business = Auth::user()->business;
        $employees = $business->employees()->where('status', 'active')
            ->with(['attendances' => function ($query) use ($request) {
                $query->where('date', $request->date);
            }])
            ->orderBy('name')->get();
        
        return response()->json($employees);
    }
}