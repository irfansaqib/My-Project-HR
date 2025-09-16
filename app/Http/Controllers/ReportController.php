<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    public function attendanceReport(Request $request)
    {
        $business = Auth::user()->business;
        $employees = $business->employees()->orderBy('name')->get();
        $shifts = $business->shifts()->orderBy('name')->get();
        $query = Attendance::whereHas('employee', fn($q) => $q->where('business_id', $business->id))->with('employee');
        
        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('shift_id')) $query->whereHas('employee.shiftAssignments', fn($q) => $q->where('shift_id', $request->shift_id));
        if ($request->filled('date_from')) $query->where('date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('date', '<=', $request->date_to);

        // ✅ DEFINITIVE FIX: The old, incorrect calculation logic has been removed.
        // The view will now automatically use the intelligent `getWorkDurationAttribute`
        // from the Attendance model, ensuring consistency across the application.
        $attendances = $query->orderBy('date', 'desc')->get();
        
        return view('reports.attendance', compact('attendances', 'employees', 'shifts'));
    }

    // --- NO CHANGES HAVE BEEN MADE TO THE FUNCTIONS BELOW ---

    public function attendanceCalendar()
    {
        $employees = Auth::user()->business->employees()->orderBy('name')->get();
        return view('reports.calendar', compact('employees'));
    }

    public function calendarEvents(Request $request)
    {
        if (!$request->filled('employee_id')) {
            return response()->json([]);
        }

        $business = Auth::user()->business;
        $start = Carbon::parse($request->start)->startOfDay();
        $end = Carbon::parse($request->end)->endOfDay();
        $today = Carbon::today();

        $holidays = $business->holidays()->whereBetween('date', [$start, $end])->get()->keyBy(fn($h) => $h->date->format('Y-m-d'));
        $employee = $business->employees()->where('id', $request->employee_id)
            ->with(['shiftAssignments.shift', 'attendances' => fn($q) => $q->whereBetween('date', [$start, $end]), 'leaveRequests' => fn($q) => $q->where('status', 'approved')->where('start_date', '<=', $end)->where('end_date', '>=', $start)])
            ->firstOrFail();
            
        $events = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $dayName = $date->format('l');

            $attendance = $employee->attendances->first(fn($att) => $att->date->isSameDay($date));
            $approvedLeave = $employee->leaveRequests->first(fn($l) => $date->betweenOrEquals($l->start_date, $l->end_date));
            $shiftAssignment = $employee->shiftAssignments->first(fn($a) => $date->between($a->start_date, $a->end_date ?? now()->addYear()));
            $weeklyOffDays = ($shiftAssignment && $shiftAssignment->shift) ? explode(',', $shiftAssignment->shift->weekly_off) : [];

            if ($holidays->has($dateString)) {
                $holiday = $holidays->get($dateString);
                $events[] = ['start' => $dateString, 'allDay' => true, 'display' => 'background', 'color' => '#cfe2ff'];
                $events[] = ['title' => "PUBLIC HOLIDAY\n\"{$holiday->title}\"", 'start' => $dateString, 'allDay' => true, 'className' => 'fc-event-holiday-text'];
            
            } elseif (in_array($dayName, $weeklyOffDays)) {
                $events[] = ['start' => $dateString, 'allDay' => true, 'display' => 'background', 'color' => '#d1e7dd'];
                $events[] = ['title' => 'WEEKLY OFF', 'start' => $dateString, 'allDay' => true, 'className' => 'fc-event-offday-text'];
            
            } elseif ($approvedLeave) {
                $events[] = ['title' => 'Leave', 'start' => $dateString, 'allDay' => true, 'backgroundColor' => '#0d6efd', 'borderColor' => '#0d6efd'];
            
            } elseif ($attendance) {
                $eventTitle = '';
                $color = '#198754';
                
                if ($attendance->status == 'present' || $attendance->status == 'late' || $attendance->status == 'half-day') {
                    $checkInTime = $attendance->check_in ? Carbon::parse($attendance->check_in)->format('h:i A') : 'N/A';
                    $checkOutTime = $attendance->check_out ? Carbon::parse($attendance->check_out)->format('h:i A') : 'N/A';
                    $timeString = "{$checkInTime} - {$checkOutTime}";
                    
                    if ($attendance->status == 'half-day') {
                        $eventTitle = "Half Day\n{$timeString}";
                        $color = '#0dcaf0';
                    } else {
                        $eventTitle = $timeString;
                    }
                    
                    if ($attendance->status == 'late') {
                        $color = '#fd7e14';
                    }
                } else {
                    $eventTitle = ucfirst($attendance->status);
                }
                
                $events[] = ['title' => $eventTitle, 'start' => $dateString, 'allDay' => true, 'backgroundColor' => $color, 'borderColor' => $color];
            
            } elseif ($date->lte($today)) {
                $events[] = ['title' => 'Absent', 'start' => $dateString, 'allDay' => true, 'backgroundColor' => '#dc3545', 'borderColor' => '#dc3545'];
            }
        }
        
        return response()->json($events);
    }
}

