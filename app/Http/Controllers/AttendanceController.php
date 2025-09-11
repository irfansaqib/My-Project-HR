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
        $filterDate = $request->input('date');

        $attendancesQuery = Attendance::whereHas('employee', function ($query) use ($business) {
            $query->where('business_id', $business->id);
        })->with('employee');

        if ($filterDate) {
            $attendancesQuery->where('date', $filterDate);
        }

        $attendances = $attendancesQuery->orderBy('date', 'desc')->paginate(25);
        
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
            'check_out' => 'nullable|date_format:H:i',
        ]);

        $businessEmployeeIds = Auth::user()->business->employees()->pluck('id')->all();
        if (in_array($validatedData['employee_id'], $businessEmployeeIds)) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $validatedData['employee_id'],
                    'date' => $validatedData['date']
                ],
                $validatedData
            );
        }

        return redirect()->route('attendances.index')->with('success', 'Attendance recorded successfully.');
    }

    public function bulkCreate()
    {
        return view('attendances.bulk_create');
    }

    /**
     * --- THIS IS THE FINAL FIX ---
     * This function now correctly reads the data structure from your bulk attendance form.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
        ]);
        
        $date = $request->input('date');
        $businessEmployeeIds = Auth::user()->business->employees()->pluck('id')->all();

        // The loop is now corrected to handle the data from your form.
        foreach ($request->input('attendances') as $attendanceData) {
            // Get the employee ID from *inside* the data array.
            $employeeId = $attendanceData['employee_id'] ?? null;

            if ($employeeId && in_array($employeeId, $businessEmployeeIds) && !empty($attendanceData['status'])) {
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date' => $date,
                    ],
                    [
                        'status' => $attendanceData['status'],
                        'check_in' => $attendanceData['check_in'] ?? null,
                        'check_out' => $attendanceData['check_out'] ?? null,
                    ]
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
            ->with(['attendances' => function ($query) use ($date) {
                $query->where('date', $date);
            }])
            ->orderBy('name')
            ->get();
            
        return response()->json($employees);
    }
    
    public function edit(Attendance $attendance)
    {
        $this->authorize('update', $attendance);
        $employees = Auth::user()->business->employees()->orderBy('name')->get();
        return view('attendances.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->authorize('update', $attendance);
        
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'status' => 'required|string',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
        ]);

        $attendance->update($validatedData);

        return redirect()->route('attendances.index')->with('success', 'Attendance record updated successfully.');
    }
}