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
use App\Http\Controllers\FinalSettlementController;
use App\Http\Controllers\LeaveEncashmentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\FundTransactionController;
use App\Http\Controllers\FundWithdrawalController;
use App\Http\Controllers\FundProfitController;
use App\Http\Controllers\ClientCredentialController;
use App\Http\Controllers\BulkTaxCalculatorController;
use App\Http\Controllers\TaxServicesController; 
use App\Http\Controllers\TaxClientComponentController;

// ** NEW MODULE IMPORTS **
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskMessageController;
use App\Http\Controllers\TaskWorkflowController;
use App\Http\Controllers\RecurringTaskController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientPortalController;

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
    
    // --- Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    
    // --- Loans & Advances ---
    Route::resource('loans', LoanController::class);
    
    // --- Final Settlement (F&F) ---
    Route::get('/settlements/{employee}/create', [FinalSettlementController::class, 'create'])->name('settlements.create');
    Route::post('/settlements/{employee}', [FinalSettlementController::class, 'store'])->name('settlements.store');
    Route::resource('final-settlements', FinalSettlementController::class);
    Route::get('final-settlements/{id}/pdf', [FinalSettlementController::class, 'downloadPdf'])->name('final-settlements.pdf');
    
    // --- Funds Management ---
    Route::resource('fund-transactions', FundTransactionController::class)
        ->names('funds.transactions') 
        ->parameters(['fund-transactions' => 'transaction'])
        ->only(['index', 'edit', 'update', 'destroy']);
   
    Route::get('funds/withdraw', [FundWithdrawalController::class, 'create'])->name('funds.withdraw.create');
    Route::post('funds/withdraw', [FundWithdrawalController::class, 'store'])->name('funds.withdraw.store');

    Route::get('funds/distribute-profit', [FundProfitController::class, 'create'])->name('funds.profit.create');
    Route::post('funds/distribute-profit', [FundProfitController::class, 'store'])->name('funds.profit.store');
    
    Route::resource('funds', FundController::class);

    // --- Profile & Settings ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
    
    Route::post('employees/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::get('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    
    Route::resource('employee-exits', EmployeeExitController::class);

    Route::resource('employees.incentives', IncentiveController::class)->except(['show', 'destroy']);
    Route::resource('employees.revisions', SalaryRevisionController::class);
    Route::resource('salary-revisions', SalaryRevisionController::class);
    
    Route::get('/salary-revisions/{id}/approve-view', [SalaryRevisionController::class, 'showForApproval'])
        ->name('salary.revisions.approve.view');
    Route::post('/salary-revisions/{structure}/approve', [SalaryRevisionController::class, 'approve'])
        ->name('salary.revisions.approve');
    Route::post('/salary-revisions/{structure}/reject', [SalaryRevisionController::class, 'reject'])
        ->name('salary.revisions.reject');
    Route::get('/approvals/salary-revisions', [SalaryRevisionController::class, 'listPending'])
        ->name('salary.revisions.pending');

    Route::prefix('approvals/salary')->name('approvals.salary.')->group(function () {
        Route::get('/', [SalaryApprovalController::class, 'index'])->name('index');
        Route::get('/{structure}', [SalaryApprovalController::class, 'show'])->name('show');
        Route::post('/{structure}/approve', [SalaryApprovalController::class, 'approve'])->name('approve');
        Route::post('/{structure}/reject', [SalaryApprovalController::class, 'reject'])->name('reject');
    });

    Route::resource('departments', DepartmentController::class);
    Route::resource('designations', DesignationController::class);
    Route::resource('shifts', ShiftController::class);

    Route::resource('shift-assignments', EmployeeShiftAssignmentController::class)
        ->only(['create', 'store'])
        ->names('shift-assignments');

    Route::resource('holidays', HolidayController::class);
    
    // ===========================
    // 📝 Leave Management
    // ===========================
    Route::resource('leave-types', LeaveTypeController::class);

    Route::post('leave-encashments/{leaveEncashment}/approve', [LeaveEncashmentController::class, 'approve'])->name('leave-encashments.approve');
    Route::post('leave-encashments/{leaveEncashment}/reject', [LeaveEncashmentController::class, 'reject'])->name('leave-encashments.reject');
    Route::resource('leave-encashments', LeaveEncashmentController::class);
    Route::post('leave-encashments/{encashment}/pay', [LeaveEncashmentController::class, 'markPaid'])->name('leave-encashments.pay');
    Route::post('api/encashment/estimate', [LeaveEncashmentController::class, 'getEstimate'])->name('api.encashment.estimate');

    Route::get('leave-requests/extra', [LeaveRequestController::class, 'extraCreate'])->name('leave-requests.extra-create');
    Route::post('leave-requests/extra', [LeaveRequestController::class, 'extraStore'])->name('leave-requests.extra-store');
    
    Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    
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
    
    Route::resource('incentives', IncentiveController::class);

    // ===========================
    // 💰 Payroll & Salary
    // ===========================
    Route::resource('salary-components', SalaryComponentController::class);
    Route::resource('tax-rates', TaxRateController::class);
    Route::get('salaries/generate', [App\Http\Controllers\SalaryController::class, 'create'])->name('salaries.generate.form');
    Route::get('payrolls/history', [SalaryController::class, 'index'])->name('payrolls.history');
    Route::post('salaries/{salarySheet}/finalize', [SalaryController::class, 'finalize'])->name('salaries.finalize');
    Route::post('salary-items/{item}/update', [SalaryController::class, 'updateItem'])->name('salaries.item.update');
    
    Route::resource('salaries', SalaryController::class)->except(['create', 'store', 'show', 'destroy', 'edit', 'update']);
    Route::get('salaries/create', [SalaryController::class, 'create'])->name('salaries.create');
    Route::post('salaries/generate', [SalaryController::class, 'generate'])->name('salaries.generate');
    Route::get('salaries/{salarySheet}', [SalaryController::class, 'show'])->name('salaries.show');
    Route::delete('salaries/{salarySheet}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
    
    Route::get('salaries/{salarySheet}/print', [SalaryController::class, 'printSheet'])->name('salaries.print');
    Route::get('salary-items/{salarySheetItem}/payslip', [SalaryController::class, 'payslip'])->name('salaries.payslip');
    Route::get('salaries/{salarySheet}/print-all-payslips', [SalaryController::class, 'printAllPayslips'])->name('salaries.payslips.print-all');
    Route::post('salaries/{salarySheet}/send-all-payslips', [SalaryController::class, 'sendAllPayslips'])->name('salaries.payslips.send-all');
    Route::get('salaries/{salarySheet}/export-bank', [SalaryController::class, 'exportBankTransfer'])->name('salaries.export-bank');
    
    Route::resource('salary-approvals', SalaryApprovalController::class);
    Route::resource('tax-calculator', TaxCalculatorController::class);

    // ===========================
    // 🧮 Tax Tools & Services
    // ===========================
    Route::get('/tools/tax-calculator/{employee?}', [TaxCalculatorController::class, 'show'])->name('tools.taxCalculator');
    Route::post('/tools/tax-calculator', [TaxCalculatorController::class, 'calculate'])->name('tools.taxCalculator.calculate');
    Route::post('api/tax-calculator', [TaxCalculatorController::class, 'apiCalculate'])->name('api.taxCalculator');
    
    Route::get('tools/bulk-tax-calculator', [BulkTaxCalculatorController::class, 'index'])->name('tools.bulk-tax');
    Route::get('tools/bulk-tax-calculator/template', [BulkTaxCalculatorController::class, 'downloadTemplate'])->name('tools.bulk-tax.template');
    Route::post('tools/bulk-tax-calculator/process', [BulkTaxCalculatorController::class, 'process'])->name('tools.bulk-tax.process');

    // ===========================
    // ⚙️ My Portal
    // ===========================
    Route::get('my-attendance', [AttendanceController::class, 'myAttendance'])->name('attendances.my');
    Route::get('salaries/my-history', [SalaryController::class, 'myHistory'])->name('salaries.my-history');
    Route::get('my-tax-certificate', [SalaryController::class, 'myTaxCertificate'])->name('salaries.my-tax');
    
    // ✅ THIS IS THE MISSING ROUTE FIXING YOUR ERROR
    Route::get('my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my'); 
    
    Route::post('tax-certificate/view', [SalaryController::class, 'viewTaxCertificate'])->name('salaries.tax.view');
    Route::post('tax-certificate/download', [SalaryController::class, 'downloadTaxCertificate'])->name('salaries.tax.download');
    Route::post('tax-certificate/email', [SalaryController::class, 'emailTaxCertificates'])->name('salaries.tax.email');
    Route::post('tax-certificate/print-all', [SalaryController::class, 'printAllTaxCertificates'])->name('salaries.tax.print-all');

    // ===========================
    // 🔐 Client Credentials
    // ===========================
    Route::resource('client-credentials', ClientCredentialController::class);

    // ====================================================
    // 🚀 NEW MODULES: CLIENT, TASKS & RECURRING
    // ====================================================
    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/assign', [ClientController::class, 'assign'])->name('clients.assign');
    
    Route::resource('tasks', TaskController::class);
    Route::post('tasks/{task}/message', [TaskMessageController::class, 'store'])->name('tasks.messages.store');
    
    // Workflow Routes
    Route::post('tasks/{task}/timer/start', [TaskWorkflowController::class, 'startTimer'])->name('tasks.timer.start');
    Route::post('tasks/{task}/timer/stop', [TaskWorkflowController::class, 'stopTimer'])->name('tasks.timer.stop');
    Route::post('tasks/{task}/execute', [TaskWorkflowController::class, 'markExecuted'])->name('tasks.execute');
    Route::post('tasks/{task}/supervisor', [TaskWorkflowController::class, 'addSupervisor'])->name('tasks.supervisor');
    Route::post('tasks/{task}/accept', [TaskWorkflowController::class, 'acceptTask'])->name('tasks.accept');
    Route::post('tasks/{task}/reject', [TaskWorkflowController::class, 'rejectTask'])->name('tasks.reject');
    // Workflow Actions
    Route::post('tasks/{task}/accept', [TaskWorkflowController::class, 'acceptTask'])->name('tasks.employee.accept');
    Route::post('tasks/{task}/reject-emp', [TaskWorkflowController::class, 'employeeRejectTask'])->name('tasks.employee.reject');
    Route::post('tasks/{task}/reassign', [TaskWorkflowController::class, 'reassignTask'])->name('tasks.reassign');
    Route::post('tasks/{task}/finalize', [TaskWorkflowController::class, 'finalizeTask'])->name('tasks.finalize');
    Route::post('tasks/{task}/reject-admin', [TaskWorkflowController::class, 'adminRejectExecution'])->name('tasks.admin.reject');

    // Admin Report
    Route::get('tasks-analytics/report', [App\Http\Controllers\TaskController::class, 'report'])->name('tasks.report');


    // Recurring Tasks
    Route::resource('recurring-tasks', RecurringTaskController::class);

    // ====================================================
    // 💰 TAX SERVICES MODULE
    // ====================================================
    Route::prefix('tax-services')->name('tax-services.')->group(function() {
        Route::get('/', [TaxServicesController::class, 'index'])->name('index');
        Route::post('/clients', [TaxServicesController::class, 'storeClient'])->name('clients.store');
        Route::get('/clients/{client}', function ($client) {
            return redirect()->route('tax-services.clients.employees', $client);
        })->name('clients.show');

        // TABS
        Route::get('/clients/{client}/employees', [TaxServicesController::class, 'tabEmployees'])->name('clients.employees');
        Route::get('/clients/{client}/components', [TaxServicesController::class, 'tabComponents'])->name('clients.components');
        Route::get('/clients/{client}/salary', [TaxServicesController::class, 'tabSalary'])->name('clients.salary');
        Route::get('/clients/{client}/reports', [TaxServicesController::class, 'tabReports'])->name('clients.reports');
        Route::get('/clients/{client}/certificates', [TaxServicesController::class, 'tabCertificates'])->name('clients.certificates');

        // Settings & Onboarding
        Route::post('/clients/{client}/settings', [TaxServicesController::class, 'updateClientSettings'])->name('clients.settings');
        Route::get('/clients/{client}/unlock-onboarding', [TaxServicesController::class, 'unlockOnboarding'])->name('clients.unlock-onboarding');
        
        // New Year Routes
        Route::get('/clients/{client}/new-year', [TaxServicesController::class, 'showNewYearForm'])->name('clients.new-year');
        Route::post('/clients/{client}/new-year', [TaxServicesController::class, 'processNewYear'])->name('clients.process-new-year');

        // Components
        Route::post('/clients/{client}/components', [TaxServicesController::class, 'storeComponent'])->name('components.store');
        Route::delete('/clients/{client}/components/{component}', [TaxServicesController::class, 'destroyComponent'])->name('components.destroy');
        Route::put('/clients/{client}/components/{component}', [TaxServicesController::class, 'updateComponent'])->name('components.update');

        // Employee Management
        Route::post('/clients/{client}/employees', [TaxServicesController::class, 'storeEmployee'])->name('employees.store');
        Route::get('/clients/{client}/export-employees', [TaxServicesController::class, 'exportEmployees'])->name('clients.export-employees');
        Route::post('/clients/{client}/import-employees', [TaxServicesController::class, 'importEmployees'])->name('employees.import');
        Route::delete('/clients/{client}/employees/{employee}', [TaxServicesController::class, 'deleteEmployee'])->name('employees.delete');

        // --- SALARY INPUT ---
        Route::post('/clients/{client}/save-salary-draft', [TaxServicesController::class, 'saveSalaryDraft'])->name('clients.save-salary-draft');
        Route::post('/clients/{client}/finalize-salary-input', [TaxServicesController::class, 'finalizeSalaryInput'])->name('clients.finalize-salary-input');
        Route::get('/clients/{client}/get-month-data', [TaxServicesController::class, 'getMonthlyInputData'])->name('clients.get-month-data');
        Route::post('/clients/{client}/preview-calculation', [TaxServicesController::class, 'previewTaxCalculation'])->name('clients.preview-calculation');
        Route::post('/clients/{client}/bulk-update-salary', [TaxServicesController::class, 'bulkUpdateEmployeeSalary'])->name('clients.bulk-update-salary');

        // Export/Import Salary Data
        Route::get('/clients/{client}/export-salary', [TaxServicesController::class, 'exportSalaryData'])->name('clients.export-salary');
        Route::post('/clients/{client}/import-salary', [TaxServicesController::class, 'importSalaryData'])->name('clients.import-salary');

        // Salary Sheet Generation
        Route::get('/clients/{client}/generate-sheet', [TaxServicesController::class, 'generateSheet'])->name('clients.generate-sheet');
        Route::get('/sheets/{sheet}', [TaxServicesController::class, 'showSheet'])->name('sheet.show');
        Route::post('/sheets/{sheet}/finalize', [TaxServicesController::class, 'finalizeSheet'])->name('sheet.finalize');
        Route::delete('/sheets/{sheet}', [TaxServicesController::class, 'destroySheet'])->name('sheet.destroy');
        Route::get('/sheets/{sheet}/export', [TaxServicesController::class, 'exportSheet'])->name('sheet.export');

        // Reports
        Route::get('/clients/{client}/projection', [TaxServicesController::class, 'ProjectionReport'])->name('clients.projection');
        Route::get('/clients/{client}/reports/tax-deduction-csv', [TaxServicesController::class, 'exportTaxDeductionReport'])->name('reports.tax-deduction-csv');
        Route::get('/clients/{client}/reports/tax-deduction-data', [TaxServicesController::class, 'getTaxDeductionData'])->name('reports.tax-deduction-data');
        Route::get('/clients/{client}/reports/tax-deduction-view', [TaxServicesController::class, 'viewTaxDeductionReport'])->name('reports.tax-deduction-view');
        Route::post('/clients/{client}/certificates/print', [TaxServicesController::class, 'printTaxCertificates'])->name('clients.certificates.print');
    });

    // ===========================
    // 📊 Reports (Global)
    // ===========================
    Route::get('reports/attendance', [ReportController::class, 'attendanceReport'])->name('reports.attendance');
    Route::get('reports/attendance-calendar', [ReportController::class, 'attendanceCalendar'])->name('reports.attendance-calendar');
    Route::get('reports/leave', [ReportController::class, 'leaveReport'])->name('reports.leave');
    Route::get('reports/payroll', [ReportController::class, 'payrollReport'])->name('reports.payroll');
    Route::get('reports/loans', [ReportController::class, 'loanReport'])->name('reports.loans');
    Route::get('reports/funds', [ReportController::class, 'fundReport'])->name('reports.funds');
    Route::get('reports/tax-deductions', [ReportController::class, 'taxDeductionReport'])->name('reports.tax');

    // ===========================
    // ⚙️ Internal APIs
    // ===========================
    Route::get('/api/employee-shift/{employee}/{date}', [EmployeeController::class, 'getShiftForDate'])->name('api.employee.shift');
    Route::get('/api/employees-for-attendance', [AttendanceController::class, 'getEmployeesForAttendance'])->name('api.employees-for-attendance');
    Route::get('/api/calendar-events', [ReportController::class, 'calendarEvents'])->name('api.calendar-events');

    Route::post('/api/tax/calculate', function (\Illuminate\Http\Request $request, \App\Services\TaxCalculatorService $taxCalculator) {
        $gross = $request->gross_salary;
        $employee = \App\Models\Employee::find($request->employee_id);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
        }

        $tax = $taxCalculator->calculate($employee, now(), $gross);
        return response()->json(['success' => true, 'monthly_tax' => $tax]);
    })->name('api.tax.calculate');

}); // ✅ Closing Authenticated Middleware Group


// ====================================================
// 🌍 CLIENT PORTAL ROUTES (External Interface)
// ====================================================

Route::prefix('portal')->name('client.')->group(function () {
    
    // Guest Routes (Login/Register)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [ClientAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [ClientAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [ClientAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [ClientAuthController::class, 'register'])->name('register.submit');
        
        // Google Auth
        Route::get('/auth/google', [ClientAuthController::class, 'redirectToGoogle'])->name('login.google');
        Route::get('/auth/google/callback', [ClientAuthController::class, 'handleGoogleCallback']);
    });

    // Authenticated Client Routes
    Route::middleware(['auth', 'role:Client'])->group(function () {
        Route::get('/dashboard', [ClientPortalController::class, 'dashboard'])->name('dashboard');
        
        // Task Management (Client Side)
        Route::get('/tasks', [ClientPortalController::class, 'indexTasks'])->name('tasks.index');
        Route::get('/tasks/create', [ClientPortalController::class, 'createTask'])->name('tasks.create');
        Route::post('/tasks/store', [ClientPortalController::class, 'storeTask'])->name('tasks.store');
        Route::get('/tasks/{task}', [ClientPortalController::class, 'showTask'])->name('tasks.show');
        Route::get('/tasks/{task}/edit', [ClientPortalController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [ClientPortalController::class, 'update'])->name('tasks.update');
        Route::post('/tasks/{task}/message', [TaskMessageController::class, 'store'])->name('tasks.messages.store');
    });
});