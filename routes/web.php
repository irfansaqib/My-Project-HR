<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ClientLoginCredentialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes (Definitive Final Version)
|--------------------------------------------------------------------------
*/

Route::get('/', function () { return view('welcome'); });

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Business & Profile Routes
    Route::get('/business', [BusinessController::class, 'edit'])->name('business.edit');
    Route::put('/business', [BusinessController::class, 'update'])->name('business.update');
    Route::get('/business/show', [BusinessController::class, 'edit'])->name('business.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // All Resource Routes
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('designations', DesignationController::class);
    Route::resource('tax-rates', TaxRateController::class);
    Route::resource('client-login-credentials', ClientLoginCredentialController::class);
    Route::resource('leave-types', LeaveTypeController::class);
    Route::resource('leave-applications', LeaveApplicationController::class);
    Route::resource('payrolls', PayrollController::class);
    Route::resource('salary-components', SalaryComponentController::class);
    
    // Custom Employee Print Routes
    Route::get('employees/{employee}/print', [EmployeeController::class, 'print'])->name('employees.print')->middleware('permission:employee-print');
    Route::get('employees/{employee}/print-contract', [EmployeeController::class, 'printContract'])->name('employees.printContract')->middleware('permission:employee-print-contract');
    
    // Salary Routes
    Route::get('salaries', [SalaryController::class, 'index'])->name('salaries.index');
    Route::get('salaries/create', [SalaryController::class, 'create'])->name('salaries.create');
    Route::post('salaries/generate', [SalaryController::class, 'generate'])->name('salaries.store'); 
    Route::get('salaries/{salarySheet}', [SalaryController::class, 'show'])->name('salaries.show');
    Route::delete('salaries/{salarySheet}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
    Route::get('payslip/{salarySheetItem}', [SalaryController::class, 'payslip'])->name('salaries.payslip');
});

require __DIR__.'/auth.php';