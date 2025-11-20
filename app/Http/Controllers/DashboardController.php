<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- 1. Import the Auth facade

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 2. Get the authenticated user's business ID
        $business_id = Auth::user()->business_id;

        // 3. Scope all queries to that specific business ID
        $employeesCount = Employee::where('business_id', $business_id)->count();
        $departmentsCount = Department::where('business_id', $business_id)->count();
        $designationsCount = Designation::where('business_id', $business_id)->count();

        return view('dashboard', compact('employeesCount', 'departmentsCount', 'designationsCount'));
    }
}