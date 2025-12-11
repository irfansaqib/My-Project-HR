<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(LeaveType::class, 'leave_type');
    }

    public function index()
    {
        $leaveTypes = Auth::user()->business->leaveTypes()->paginate(15);
        return view('leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('leave-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_encashable' => 'nullable|boolean',
            'encashment_variable' => 'required_if:is_encashable,1|in:basic_salary,gross_salary',
            'encashment_divisor' => 'required_if:is_encashable,1|integer|min:1',
            'min_balance_required' => 'nullable|integer|min:0',
            'max_days_encashable' => 'nullable|integer|min:0',
        ]);

        $data = $request->only('name');
        
        // Handle Policy Fields
        $data['is_encashable'] = $request->boolean('is_encashable');
        if ($data['is_encashable']) {
            $data['encashment_variable'] = $request->encashment_variable;
            $data['encashment_divisor'] = $request->encashment_divisor;
            $data['min_balance_required'] = $request->min_balance_required ?? 0;
            $data['max_days_encashable'] = $request->max_days_encashable ?? 0;
        }

        Auth::user()->business->leaveTypes()->create($data);

        return redirect()->route('leave-types.index')->with('success', 'Leave Type created successfully.');
    }

    public function edit(LeaveType $leaveType)
    {
        return view('leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_encashable' => 'nullable|boolean',
            'encashment_variable' => 'required_if:is_encashable,1|in:basic_salary,gross_salary',
            'encashment_divisor' => 'required_if:is_encashable,1|integer|min:1',
            'min_balance_required' => 'nullable|integer|min:0',
            'max_days_encashable' => 'nullable|integer|min:0',
        ]);

        $data = $request->only('name');
        
        // Handle Policy Fields
        $data['is_encashable'] = $request->boolean('is_encashable');
        if ($data['is_encashable']) {
            $data['encashment_variable'] = $request->encashment_variable;
            $data['encashment_divisor'] = $request->encashment_divisor;
            $data['min_balance_required'] = $request->min_balance_required ?? 0;
            $data['max_days_encashable'] = $request->max_days_encashable ?? 0;
        } else {
            // Reset logic if turned off
            $data['encashment_variable'] = 'basic_salary';
            $data['encashment_divisor'] = 30;
            $data['min_balance_required'] = 0;
            $data['max_days_encashable'] = 0;
        }

        $leaveType->update($data);

        return redirect()->route('leave-types.index')->with('success', 'Leave Type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();
        return redirect()->route('leave-types.index')->with('success', 'Leave Type deleted successfully.');
    }
}