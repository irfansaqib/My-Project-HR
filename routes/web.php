<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\TaxRateController; // Correct controller is used

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['check_business_details'])->group(function () {
        Route::prefix('business')->group(function () {
            Route::get('/create', [BusinessController::class, 'create'])->name('business.create');
            Route::post('/', [BusinessController::class, 'store'])->name('business.store');
            Route::get('/{business}', [BusinessController::class, 'show'])->name('business.show');
            Route::get('/{business}/edit', [BusinessController::class, 'edit'])->name('business.edit');
            Route::put('/{business}', [BusinessController::class, 'update'])->name('business.update');
        });

        Route::middleware(['check_tenant_ownership'])->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('roles', RoleController::class);
            Route::resource('designations', DesignationController::class)->except(['show']);
            Route::resource('departments', DepartmentController::class)->except(['show']);
            
            Route::resource('employees', EmployeeController::class);
            Route::get('/employees/{employee}/print', [EmployeeController::class, 'print'])->name('employees.print');
            Route::get('/employees/{employee}/print-contract', [EmployeeController::class, 'printContract'])->name('employees.printContract');

            Route::resource('leave-requests', LeaveRequestController::class);
            Route::post('leave-requests/{leave_request}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
            Route::post('leave-requests/{leave_request}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
            Route::get('extra-leave/create', [LeaveRequestController::class, 'extraCreate'])->name('leave-requests.extra-create');
            Route::post('extra-leave', [LeaveRequestController::class, 'extraStore'])->name('leave-requests.extra-store');

            // SALARY & PAYROLL ROUTES
            Route::resource('salary-components', SalaryComponentController::class)->except(['show']);
            Route::get('salaries', [SalaryController::class, 'index'])->name('salaries.index');
            Route::get('salaries/create', [SalaryController::class, 'create'])->name('salaries.create');
            Route::post('salaries/generate', [SalaryController::class, 'generate'])->name('salaries.generate');
            Route::get('salaries/{year}/{month}', [SalaryController::class, 'show'])->name('salaries.show');
            Route::delete('salaries/{year}/{month}', [SalaryController::class, 'destroy'])->name('salaries.destroy');
            Route::get('payslip/{payslip}', [SalaryController::class, 'showPayslip'])->name('salaries.payslip');
            
            // Replaced the old tax-slabs route with the new one
            Route::resource('tax-rates', TaxRateController::class)->except(['show']);
        });
    });
});