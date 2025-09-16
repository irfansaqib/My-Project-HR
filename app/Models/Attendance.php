<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * ✅ DEFINITIVE FIX: The "Total Hours" logic has been completely upgraded.
     * It now intelligently handles auto-checkout based on shift rules.
     *
     * @return string
     */
    public function getWorkDurationAttribute()
    {
        // Case 1: Handle descriptive statuses first.
        if ($this->status === 'leave') {
            return '<span class="badge badge-info">On Leave</span>';
        }
        if ($this->status === 'absent') {
            return '<span class="badge badge-danger">Absent</span>';
        }

        // Case 2: Both check_in and check_out exist, proceed with normal calculation.
        if ($this->check_in && $this->check_out) {
            try {
                $checkIn = Carbon::parse($this->check_in);
                $checkOut = Carbon::parse($this->check_out);
                
                if ($checkOut->isBefore($checkIn)) {
                    $checkOut->addDay();
                }
                return $checkIn->diff($checkOut)->format('%H hours %I minutes');
            } catch (\Exception $e) {
                return '<span class="text-danger">Invalid Time</span>';
            }
        }

        // Case 3: Only check_in exists (The new logic).
        if ($this->check_in) {
            $attendanceDate = Carbon::parse($this->date);
            
            // Only apply this logic to past days.
            if ($attendanceDate->isPast() && !$attendanceDate->isToday()) {
                
                // Find the employee's shift for that day
                $shift = $this->employee->getActiveShiftForDate($attendanceDate);

                if ($shift && $shift->end_time) {
                    $checkIn = Carbon::parse($this->check_in);
                    
                    // Create the auto-marked checkout time
                    $autoCheckOut = Carbon::parse($shift->end_time)->subMinutes($shift->auto_deduct_minutes);
                    
                    // Handle overnight shifts
                    if ($autoCheckOut->isBefore($checkIn)) {
                        $autoCheckOut->addDay();
                    }
                    
                    $duration = $checkIn->diff($autoCheckOut)->format('%Hh %Im');
                    return $duration . ' <span class="badge badge-warning" style="white-space: normal;">Forgot to check out</span>';
                }

                // Fallback if no shift is found
                return '<span class="text-warning">Forgot to check out</span>';
            }
            
            // For the current day, show "In Progress"
            if ($attendanceDate->isToday()) {
                $checkIn = Carbon::parse($this->check_in);
                return $checkIn->diff(Carbon::now())->format('%Hh %Im') . ' (In Progress)';
            }
        }
        
        // Default case if no check-in exists for 'Present' or 'Half-day'.
        return 'N/A';
    }
}

