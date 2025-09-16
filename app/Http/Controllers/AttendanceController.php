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
        $filterDate = $request->input('date', now()->format('Y-m-d'));

        $attendancesQuery = Attendance::whereHas('employee', function ($query) use ($business) {
            $query->where('business_id', $business->id);
        })->with('employee');

        if ($filterDate) {
            $attendancesQuery->where('date', $filterDate);
        }

        $attendances = $attendancesQuery->orderBy('created_at', 'desc')->paginate(25);
        return view('attendances.index', compact('attendances', 'filterDate'));
    }

    public function create()
    {
        $employees = Auth::user()->business->employees()->orderBy('name')->get();
        return view('attendances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'status' => 'required|string',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
        ]);
        
        // ✅ DEFINITIVE FIX: Automatically clear times for 'Leave' or 'Absent'.
        if (in_array($validatedData['status'], ['leave', 'absent'])) {
            $validatedData['check_in'] = null;
            $validatedData['check_out'] = null;
        }

        $businessEmployeeIds = Auth::user()->business->employees()->pluck('id')->all();
        if (in_array($validatedData['employee_id'], $businessEmployeeIds)) {
            Attendance::updateOrCreate(
                ['employee_id' => $validatedData['employee_id'], 'date' => $validatedData['date']],
                $validatedData
            );
        }

        return redirect()->route('attendances.index')->with('success', 'Attendance recorded successfully.');
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->authorize('update', $attendance);
        
        $validatedData = $request->validate([
            'status' => 'required|string',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
        ]);

        // ✅ DEFINITIVE FIX: Automatically clear times for 'Leave' or 'Absent'.
        if (in_array($validatedData['status'], ['leave', 'absent'])) {
            $validatedData['check_in'] = null;
            $validatedData['check_out'] = null;
        }

        $attendance->update($validatedData);
        return redirect()->route('attendances.index', ['date' => $attendance->date->format('Y-m-d')])->with('success', 'Attendance record updated successfully.');
    }
    
    public function bulkCreate()
    {
        return view('attendances.bulk_create');
    }

    public function bulkStore(Request $request)
    {
        $request->validate(['date' => 'required|date', 'attendances' => 'required|array']);
        $date = $request->input('date');
        $businessEmployeeIds = Auth::user()->business->employees()->pluck('id')->all();
        
        foreach ($request->input('attendances', []) as $attendanceData) {
            $employeeId = $attendanceData['employee_id'] ?? null;
            if ($employeeId && in_array($employeeId, $businessEmployeeIds) && !empty($attendanceData['status'])) {
                // ✅ DEFINITIVE FIX: Also apply the logic to the bulk store function.
                if (in_array($attendanceData['status'], ['leave', 'absent'])) {
                    $attendanceData['check_in'] = null;
                    $attendanceData['check_out'] = null;
                }
                Attendance::updateOrCreate(
                    ['employee_id' => $employeeId, 'date' => $date],
                    ['status' => $attendanceData['status'], 'check_in' => $attendanceData['check_in'] ?? null, 'check_out' => $attendanceData['check_out'] ?? null]
                );
            }
        }
        return redirect()->route('attendances.index')->with('success', 'Bulk attendance has been recorded successfully.');
    }
    
    public function getEmployeesForAttendance(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->input('date');
        $employees = Auth::user()->business->employees()->where('status', 'active')
            ->with(['attendances' => function ($query) use ($date) { $query->where('date', $date); }])
            ->orderBy('name')->get();
        return response()->json($employees);
    }
    
    public function edit(Attendance $attendance)
    {
        $this->authorize('update', $attendance);
        return view('attendances.edit', compact('attendance'));
    }
}

