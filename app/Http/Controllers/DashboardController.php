<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employeesCount = Employee::count();
        $departmentsCount = Department::count();
        $designationsCount = Designation::count();

        return view('dashboard', compact('employeesCount', 'departmentsCount', 'designationsCount'));
    }
}