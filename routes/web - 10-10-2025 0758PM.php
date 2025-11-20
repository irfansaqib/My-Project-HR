<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeExitController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\EmployeeShiftAssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\WarningController;
use App\Http\Controllers\IncentiveController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessBankAccountController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmailConfigurationController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['verify' => true]);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Administrative & Settings Routes ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::resource('business', BusinessController::class)->only(['show', 'edit', 'update']);
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('email-configuration', [EmailConfigurationController::class, 'edit'])->name('email-configuration.edit');
    Route::patch('email-configuration', [EmailConfigurationController::class, 'update'])->name('email-configuration.update');
    Route::post('email-configuration/test', [EmailConfigurationController::class, 'sendTestEmail'])->name('email-configuration.test');
    Route::resource('business-bank-accounts', BusinessBankAccountController::class);

    // --- HR Management Routes ---
    Route::resource('employees', EmployeeController::class);
    Route::get('/employees/{employee}/exit', [EmployeeExitController::class, 'create'])->name('employees.exit.create');
    Route::post('/employees/{employee}/exit', [EmployeeExitController::class, 'store'])->name('employees.exit.store');
    Route::get('/employees/{employee}/print', [EmployeeController::class, 'print'])->name('employees.print');
    Route::get('/employees/{employee}/print-contract', [EmployeeController::class, 'printContract'])->name('employees.printContract');
    Route::resource('departments', DepartmentController::class);
    Route::resource('designations', DesignationController::class);
    Route::resource('shifts', ShiftController::class);
    Route::resource('shift-assignments', EmployeeShiftAssignmentController::class)
        ->only(['create', 'store'])
        ->names('shift-assignments');
    Route::resource('holidays', HolidayController::class);
    Route::resource('leave-types', LeaveTypeController::class);
    Route::resource('leave-requests', LeaveRequestController::class);

    // --- Attendance Routes ---
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('attendances/bulk', [AttendanceController::class, 'createBulk'])->name('attendances.bulk.create');
    Route::post('attendances/bulk', [AttendanceController::class, 'storeBulk'])->name('attendances.bulk.store');
    Route::get('attendances/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');
    Route::match(['PUT', 'PATCH', 'POST'], 'attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');

    // --- Disciplinary Routes ---
    Route::resource('warnings', WarningController::class)->except(['index']);
    Route::get('employees/{employee}/warnings/create', [WarningController::class, 'create'])->name('warnings.create');

    // --- Payroll Routes ---
    Route::resource('salary-components', SalaryComponentController::class);
    Route::resource('tax-rates', TaxRateController::class);
    Route::get('payrolls/history', [SalaryController::class, 'index'])->name('payrolls.history');
    Route::get('/employees/{employee}/incentives', [IncentiveController::class, 'index'])->name('employees.incentives.index');
    Route::get('/employees/{employee}/incentives/create', [IncentiveController::class, 'create'])->name('employees.incentives.create');
    Route::post('/employees/{employee}/incentives', [IncentiveController::class, 'store'])->name('employees.incentives.store');
    Route::resource('salaries', SalaryController::class)->except(['create', 'store', 'show', 'destroy', 'edit', 'update']);
    Route::get('salaries/create', [SalaryController::class, 'create'])->name('salaries.create');
    Route::post('salaries/generate', [SalaryController::class, 'generate'])->name('salaries.generate');
    Route::get('salaries/{salarySheet}', [SalaryController::class, 'show'])->name('salaries.show');
    Route::delete('salaries/{salarySheet}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
    Route::get('salaries/{salarySheet}/print', [SalaryController::class, 'printSheet'])->name('salaries.print');
    Route::get('salary-items/{salarySheetItem}/payslip', [SalaryController::class, 'payslip'])->name('salaries.payslip');
    Route::get('salaries/{salarySheet}/print-all-payslips', [SalaryController::class, 'printAllPayslips'])->name('salaries.payslips.print-all');
    Route::post('salaries/{salarySheet}/send-all-payslips', [SalaryController::class, 'sendAllPayslips'])->name('salaries.payslips.send-all');

    // --- Reports Routes ---
    Route::get('reports/attendance', [ReportController::class, 'attendanceReport'])->name('reports.attendance');
    Route::get('reports/attendance-calendar', [ReportController::class, 'attendanceCalendar'])->name('reports.attendance-calendar');
    Route::get('reports/leave', [ReportController::class, 'leaveReport'])->name('reports.leave');
    Route::get('reports/payroll', [ReportController::class, 'payrollReport'])->name('reports.payroll');
    
    // --- Internal API Routes ---
    Route::get('/api/employee-shift/{employee}/{date}', [EmployeeController::class, 'getShiftForDate'])->name('api.employee.shift');
    Route::get('/api/employees-for-attendance', [AttendanceController::class, 'getEmployeesForAttendance'])->name('api.employees-for-attendance');
    // ✅ FIX: Renamed the route from 'reports.calendar-events' to 'api.calendar-events' to match the view.
    Route::get('/api/calendar-events', [ReportController::class, 'calendarEvents'])->name('api.calendar-events');
});