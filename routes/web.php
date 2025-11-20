<?php

use Illuminate\Support\Facades\Route;

// App Controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeExitController;
use App\Http\Controllers\SalaryRevisionController;
use App\Http\Controllers\SalaryApprovalController;
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
use App\Http\Controllers\TaxCalculatorController;

// ** AUTH Controllers **
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// ===========================
// 🔒 Authentication Routes
// ===========================
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});

// ===========================
// 📦 Authenticated Application
// ===========================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Profile & Settings ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('business', BusinessController::class)->only(['show', 'edit', 'update']);
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    Route::get('email-configuration', [EmailConfigurationController::class, 'edit'])->name('email-configuration.edit');
    Route::patch('email-configuration', [EmailConfigurationController::class, 'update'])->name('email-configuration.update');
    Route::get('email-configuration/test', [EmailConfigurationController::class, 'test'])->name('email-configuration.test');

    Route::resource('business-bank-accounts', BusinessBankAccountController::class);

    // ===========================
    // 👥 HR Management
    // ===========================
    Route::resource('employees', EmployeeController::class);
    Route::get('/employees/{employee}/exit', [EmployeeExitController::class, 'create'])->name('employees.exit.create');
    Route::post('/employees/{employee}/exit', [EmployeeExitController::class, 'store'])->name('employees.exit.store');
    Route::get('/employees/{employee}/print', [EmployeeController::class, 'print'])->name('employees.print');
    Route::get('/employees/{employee}/print-contract', [EmployeeController::class, 'printContract'])->name('employees.printContract');

    // Incentives
    Route::resource('employees.incentives', IncentiveController::class)->except(['show', 'destroy']);

    // Salary Revisions
    Route::resource('employees.revisions', SalaryRevisionController::class);

    // Departments / Designations / Shifts
    Route::resource('departments', DepartmentController::class);
    Route::resource('designations', DesignationController::class);
    Route::resource('shifts', ShiftController::class);

    Route::resource('shift-assignments', EmployeeShiftAssignmentController::class)
        ->only(['create', 'store'])
        ->names('shift-assignments');

    Route::resource('holidays', HolidayController::class);
    Route::resource('leave-types', LeaveTypeController::class);
    Route::resource('leave-requests', LeaveRequestController::class);

    // ===========================
    // 📅 Attendance
    // ===========================
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('attendances/bulk', [AttendanceController::class, 'createBulk'])->name('attendances.bulk.create');
    Route::post('attendances/bulk', [AttendanceController::class, 'storeBulk'])->name('attendances.bulk.store');
    Route::get('attendances/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');
    Route::match(['PUT', 'PATCH', 'POST'], 'attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');

    // ===========================
    // ⚠️ Warnings / Disciplinary
    // ===========================
    Route::resource('warnings', WarningController::class)->except(['index']);
    Route::get('employees/{employee}/warnings/create', [WarningController::class, 'create'])->name('warnings.create');

    // ===========================
    // 💰 Payroll & Salary
    // ===========================
    Route::resource('salary-components', SalaryComponentController::class);
    Route::resource('tax-rates', TaxRateController::class);

    Route::get('payrolls/history', [SalaryController::class, 'index'])->name('payrolls.history');
    Route::resource('salaries', SalaryController::class)->except(['create', 'store', 'show', 'destroy', 'edit', 'update']);

    Route::get('salaries/create', [SalaryController::class, 'create'])->name('salaries.create');
    Route::post('salaries/generate', [SalaryController::class, 'generate'])->name('salaries.generate');
    Route::get('salaries/{salarySheet}', [SalaryController::class, 'show'])->name('salaries.show');
    Route::delete('salaries/{salarySheet}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
    Route::get('salaries/{salarySheet}/print', [SalaryController::class, 'printSheet'])->name('salaries.print');
    Route::get('salary-items/{salarySheetItem}/payslip', [SalaryController::class, 'payslip'])->name('salaries.payslip');
    Route::get('salaries/{salarySheet}/print-all-payslips', [SalaryController::class, 'printAllPayslips'])->name('salaries.payslips.print-all');
    Route::post('salaries/{salarySheet}/send-all-payslips', [SalaryController::class, 'sendAllPayslips'])->name('salaries.payslips.send-all');

    // ===========================
    // 🧮 Tax Calculator Tool (Per Employee)
    // ===========================

    // 🟩 Flexible GET route — works with or without employee ID
    Route::get('/tools/tax-calculator/{employee?}', [TaxCalculatorController::class, 'show'])
    ->name('tools.taxCalculator');

    // 🟩 Single POST route (not tied to employee) for manual calculation
    Route::post('/tools/tax-calculator', [TaxCalculatorController::class, 'calculate'])
    ->name('tools.taxCalculator.calculate');

    // ✅ AJAX API endpoint for inline or popup tax calculator
    Route::post('api/tax-calculator', [TaxCalculatorController::class, 'apiCalculate'])
    ->name('api.taxCalculator');

    // ===========================
    // 🧮 Client Credentials
    // ===========================
    // ✅ Client Credentials Routes
    Route::middleware(['auth'])->group(function () {
        Route::resource('client-credentials', \App\Http\Controllers\ClientCredentialController::class);
    });

    // ===========================
    // 📊 Reports
    // ===========================
    Route::get('reports/attendance', [ReportController::class, 'attendanceReport'])->name('reports.attendance');
    Route::get('reports/attendance-calendar', [ReportController::class, 'attendanceCalendar'])->name('reports.attendance-calendar');
    Route::get('reports/leave', [ReportController::class, 'leaveReport'])->name('reports.leave');
    Route::get('reports/payroll', [ReportController::class, 'payrollReport'])->name('reports.payroll');

    // ===========================
    // ⚙️ Internal APIs
    // ===========================
    Route::get('/api/employee-shift/{employee}/{date}', [EmployeeController::class, 'getShiftForDate'])->name('api.employee.shift');
    Route::get('/api/employees-for-attendance', [AttendanceController::class, 'getEmployeesForAttendance'])->name('api.employees-for-attendance');
    Route::get('/api/calendar-events', [ReportController::class, 'calendarEvents'])->name('api.calendar-events');

    /**
     * ✅ This is the single, functional API route for the inline tax calculator
     * in the Employee form (_form.blade.php).
     */
    Route::post('/api/tax/calculate', function (\Illuminate\Http\Request $request, \App\Services\TaxCalculatorService $taxCalculator) {
        $gross = $request->gross_salary;
        $employee = \App\Models\Employee::find($request->employee_id);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
        }

        $tax = $taxCalculator->calculate($employee, now(), $gross);
        return response()->json(['success' => true, 'monthly_tax' => $tax]);
    })->name('api.tax.calculate');

    // ===========================
    // ✅ Salary Revision Approvals
    // ===========================
    Route::get('/salary-revisions/{id}/approve-view', [SalaryRevisionController::class, 'showForApproval'])
        ->name('salary.revisions.approve.view');

    Route::post('/salary-revisions/{structure}/approve', [SalaryRevisionController::class, 'approve'])
        ->name('salary.revisions.approve');

    Route::post('/salary-revisions/{structure}/reject', [SalaryRevisionController::class, 'reject'])
        ->name('salary.revisions.reject');

    Route::get('/approvals/salary-revisions', [SalaryRevisionController::class, 'listPending'])
        ->name('salary.revisions.pending');

    // --- Salary Approval Routes ---
    Route::prefix('approvals/salary')->name('approvals.salary.')->group(function () {
        Route::get('/', [SalaryApprovalController::class, 'index'])->name('index');
        Route::get('/{structure}', [SalaryApprovalController::class, 'show'])->name('show');
        Route::post('/{structure}/approve', [SalaryApprovalController::class, 'approve'])->name('approve');
        Route::post('/{structure}/reject', [SalaryApprovalController::class, 'reject'])->name('reject');
    });
});