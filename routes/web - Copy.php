<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessBankAccountController;
use App\Http\Controllers\ClientCredentialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailConfigurationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () { return view('welcome'); });

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Business & Profile Routes
    Route::resource('business', BusinessController::class)->except(['index', 'destroy']);
    Route::resource('business-bank-accounts', BusinessBankAccountController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource Routes
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('designations', DesignationController::class);
    Route::resource('tax-rates', TaxRateController::class);
    Route::resource('client-credentials', ClientCredentialController::class);
    Route::resource('leave-types', LeaveTypeController::class);
    Route::resource('leave-applications', LeaveRequestController::class)->names('leave-requests');
    Route::resource('payrolls', PayrollController::class)->except(['show', 'edit', 'update', 'create']);
    Route::resource('salary-components', SalaryComponentController::class);

    // Email Configuration Route for Admins/Owners
    Route::get('email-configuration', [EmailConfigurationController::class, 'edit'])->name('email-configuration.edit');
    Route::post('email-configuration', [EmailConfigurationController::class, 'update'])->name('email-configuration.update');
    // ** THIS IS THE NEW TEST ROUTE **
    Route::get('email-configuration/test', [EmailConfigurationController::class, 'test'])->name('email-configuration.test');
    
    // Custom Employee Print Routes
    Route::get('employees/{employee}/print', [EmployeeController::class, 'print'])->name('employees.print');
    Route::get('employees/{employee}/print-contract', [EmployeeController::class, 'printContract'])->name('employees.printContract');
    
    // Custom Payroll Routes
    Route::get('payrolls/history', [PayrollController::class, 'history'])->name('payrolls.history');
    Route::get('payrolls/{payroll}/download', [PayrollController::class, 'downloadBankFile'])->name('payrolls.download');
    Route::post('/payroll/run-by-bank', [PayrollController::class, 'runByBank'])->name('payrolls.run-by-bank');

    // Salary Routes
    Route::get('salaries', [SalaryController::class, 'index'])->name('salaries.index');
    Route::get('salaries/create', [SalaryController::class, 'create'])->name('salaries.create');
    Route::post('salaries/generate', [SalaryController::class, 'generate'])->name('salaries.store'); 
    Route::get('salaries/{salarySheet}', [SalaryController::class, 'show'])->name('salaries.show');
    Route::delete('salaries/{salarySheet}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
    Route::get('payslip/{salarySheetItem}', [SalaryController::class, 'payslip'])->name('salaries.payslip');
    Route::get('salaries/{salarySheet}/print-all-payslips', [SalaryController::class, 'printAllPayslips'])->name('salaries.payslips.print-all');
    Route::post('salaries/{salarySheet}/send-all-payslips', [SalaryController::class, 'sendAllPayslips'])->name('salaries.payslips.send-all');
    Route::get('salaries/{salarySheet}/print', [SalaryController::class, 'printSheet'])->name('salaries.print');
});

require __DIR__.'/auth.php';