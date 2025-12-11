<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $business = Auth::user()->business;
        $shifts = $business->shifts()->get();

        $filterDate = $request->input('date');
        $filterShiftId = $request->input('shift_id');

        // Start with a base query builder
        $attendancesQuery = Attendance::query();

        // ✅ FIX: Only apply the date filter if a date is actually provided.
        if (!empty($filterDate)) {
            $attendancesQuery->where('date', $filterDate);
        }

        // Apply employee and shift filters
        $attendancesQuery->whereHas('employee', function ($query) use ($business, $filterShiftId, $filterDate) {
            $query->where('business_id', $business->id);

            // If a shift is selected, filter employees assigned to that shift
            if ($filterShiftId) {
                $query->whereHas('shiftAssignments', function ($q) use ($filterShiftId, $filterDate) {
                    $q->where('shift_id', $filterShiftId);

                    // If a date is also provided, make sure the shift assignment is active for that date
                    if (!empty($filterDate)) {
                        $q->where('start_date', '<=', $filterDate)
                          ->where(function ($subQ) use ($filterDate) {
                              $subQ->where('end_date', '>=', $filterDate)
                                   ->orWhereNull('end_date');
                          });
                    }
                });
            }
        })
        ->with('employee');

        $attendances = $attendancesQuery->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(25);
        
        return view('attendances.index', compact('attendances', 'filterDate', 'shifts', 'filterShiftId'));
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

    public function edit(Attendance $attendance)
    {
        $this->authorize('update', $attendance);
        return view('attendances.edit', compact('attendance'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->authorize('update', $attendance);

        $validatedData = $request->validate([
            'status' => 'required|string|in:present,absent,leave,half-day',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
        ]);

        if (in_array($validatedData['status'], ['leave', 'absent'])) {
            $validatedData['check_in'] = null;
            $validatedData['check_out'] = null;
        }

        $attendance->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            $attendance->refresh();

            return response()->json([
                'success' => true,
                'attendance' => [
                    'id' => $attendance->id,
                    'status' => $attendance->status,
                    'check_in' => $attendance->check_in,
                    'check_out' => $attendance->check_out,
                    'work_duration' => $attendance->work_duration ?? null,
                ],
            ]);
        }

        return redirect()->route('attendances.index', ['date' => $attendance->date->format('Y-m-d')])
                         ->with('success', 'Attendance record updated successfully.');
    }

    public function createBulk()
    {
        $shifts = Auth::user()->business->shifts()->get();
        return view('attendances.bulk_create', compact('shifts'));
    }

    public function storeBulk(Request $request)
    {
        $request->validate(['date' => 'required|date', 'attendances' => 'required|array']);
        $date = $request->input('date');
        $businessEmployeeIds = Auth::user()->business->employees()->pluck('id')->all();
        
        foreach ($request->input('attendances', []) as $attendanceData) {
            $employeeId = $attendanceData['employee_id'] ?? null;
            if ($employeeId && in_array($employeeId, $businessEmployeeIds) && !empty($attendanceData['status'])) {
                if (in_array($attendanceData['status'], ['leave', 'absent'])) {
                    $attendanceData['check_in'] = null;
                    $attendanceData['check_out'] = null;
                }
                Attendance::updateOrCreate(
                    ['employee_id' => $employeeId, 'date' => $date],
                    [
                        'status' => $attendanceData['status'],
                        'check_in' => $attendanceData['check_in'] ?? null,
                        'check_out' => $attendanceData['check_out'] ?? null
                    ]
                );
            }
        }
        return redirect()->route('attendances.index')->with('success', 'Bulk attendance has been recorded successfully.');
    }

    public function getEmployeesForAttendance(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift_id' => 'nullable|exists:shifts,id'
        ]);
        
        $date = $request->input('date');
        $shiftId = $request->input('shift_id');

        $employeesQuery = Auth::user()->business->employees()
            ->where('status', 'active');

        if ($shiftId) {
            $employeesQuery->whereHas('shiftAssignments', function ($query) use ($shiftId, $date) {
                $query->where('shift_id', $shiftId)
                      ->where('start_date', '<=', $date)
                      ->where(function ($q) use ($date) {
                          $q->where('end_date', '>=', $date)
                            ->orWhereNull('end_date');
                      });
            });
        }

        $employees = $employeesQuery
            ->with(['attendances' => function ($query) use ($date) { 
                $query->where('date', $date); 
            }])
            ->orderBy('name')->get();
            
        return response()->json($employees);
    }

    /**
     * ✅ NEW: Employee Self-Service - My Attendance
     */
    public function myAttendance(Request $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) abort(403, 'No employee profile linked.');

        // Default to current month if not filtered
        $month = $request->month ? Carbon::parse($request->month) : Carbon::now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->get();

        // Calculate stats for the view
        $stats = [
            'present' => $attendances->whereIn('status', ['present', 'late', 'half-day'])->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'leaves' => $attendances->where('status', 'leave')->count(),
        ];

        return view('attendances.my_attendance', compact('attendances', 'month', 'stats'));
    }
}