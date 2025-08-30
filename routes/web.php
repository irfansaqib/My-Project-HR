<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ClientCredentialController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LeaveRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Business routes are handled by its specific Policy
    Route::resource('business', BusinessController::class)->except(['index', 'destroy']);
    
    // All other tenant-specific modules are grouped here
    Route::middleware(['tenant.owner'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('client-credentials', ClientCredentialController::class);
        Route::resource('customers', CustomerController::class);
        
        // HR Management
        Route::resource('designations', DesignationController::class)->except(['show']);
        Route::resource('departments', DepartmentController::class)->except(['show']);
        
        Route::get('employees/{employee}/contract', [EmployeeController::class, 'printContract'])->name('employees.contract');
        Route::get('employees/{employee}/print', [EmployeeController::class, 'print'])->name('employees.print');
        Route::resource('employees', EmployeeController::class);

        // ========================================================================
        // === YOUR CUSTOM LEAVE ROUTES ARE RESTORED HERE                     ===
        // ========================================================================
        Route::get('leave-requests/extra', [LeaveRequestController::class, 'extraCreate'])->name('leave-requests.extra-create');
        Route::post('leave-requests/extra', [LeaveRequestController::class, 'extraStore'])->name('leave-requests.extra-store');
        Route::get('leave-requests/{leaveRequest}/print', [LeaveRequestController::class, 'print'])->name('leave-requests.print');
        Route::patch('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
        Route::patch('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
        Route::resource('leave-requests', LeaveRequestController::class);
    });
});

require __DIR__.'/auth.php';