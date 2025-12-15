<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function __construct()
{
    $this->middleware('permission:loan-list')->only(['index', 'show']);
    $this->middleware('permission:loan-create')->only(['create', 'store']);
    $this->middleware('permission:loan-edit')->only(['edit', 'update']);
    $this->middleware('permission:loan-delete')->only(['destroy']);
    
    // If you have a specific approval method:
    // $this->middleware('permission:loan-approve')->only(['approve']);
}
    
    public function index()
    {
        $businessId = Auth::user()->business_id;
        
        // ✅ FIX: Eager load designationRelation to fix N/A in the list
        $loans = Loan::with(['employee.designationRelation'])
            ->where('business_id', $businessId)
            ->orderBy('loan_date', 'desc')
            ->paginate(15);
            
        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        $employees = Employee::where('business_id', Auth::user()->business_id)
                        ->where('status', 'active')
                        ->with('designationRelation') // For the dropdown
                        ->orderBy('name')
                        ->get();
        return view('loans.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:advance,loan',
            'amount'      => 'required|numeric|min:1',
            'installments' => 'nullable|required_if:type,loan|integer|min:1',
            'loan_date'    => 'required|date',
            'start_date'   => 'required|date|after_or_equal:loan_date',
        ]);

        $employee = Employee::with(['salaryComponents'])->findOrFail($request->employee_id);
        $amount = (float) $request->amount;
        
        if ($request->type === 'advance') {
            $gross = (float) $employee->gross_salary;
            $fixedDeductions = $employee->salaryComponents->where('type', 'deduction')->sum('pivot.amount');
            $estimatedNetSalary = $gross - $fixedDeductions;
            
            $currentMonth = \Carbon\Carbon::parse($request->start_date)->format('Y-m');
            $existingAdvances = Loan::where('employee_id', $employee->id)
                ->where('type', 'advance')
                ->where('status', 'running')
                ->where('repayment_start_date', 'like', "$currentMonth%")
                ->sum('total_amount');

            $availableBalance = $estimatedNetSalary - $existingAdvances;

            if ($amount > $availableBalance) {
                return back()->withErrors([
                    'amount' => "Advance denied. Request ($amount) exceeds estimated Net Salary ($estimatedNetSalary)."
                ])->withInput();
            }
        }

        $installments = ($request->type === 'advance') ? 1 : (int)$request->installments;
        $installmentAmount = $amount / $installments;

        Loan::create([
            'business_id' => Auth::user()->business_id,
            'employee_id' => $employee->id,
            'type' => $request->type,
            'total_amount' => $amount,
            'installments' => $installments,
            'installment_amount' => $installmentAmount,
            'recovered_amount' => 0,
            'loan_date' => $request->loan_date,
            'repayment_start_date' => $request->start_date,
            'status' => 'running',
            'notes' => $request->notes,
        ]);

        return redirect()->route('loans.index')->with('success', ucfirst($request->type) . ' recorded successfully.');
    }

    public function edit(Loan $loan)
    {
        if ($loan->business_id !== Auth::user()->business_id) { abort(403); }
        
        $employees = Employee::where('business_id', Auth::user()->business_id)
                        ->where('status', 'active')
                        ->with('designationRelation')
                        ->orderBy('name')
                        ->get();
                        
        return view('loans.edit', compact('loan', 'employees'));
    }

    public function update(Request $request, Loan $loan)
    {
        if ($loan->business_id !== Auth::user()->business_id) { abort(403); }

        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'installments' => 'nullable|required_if:type,loan|integer|min:1',
            'loan_date'    => 'required|date',
            'start_date'   => 'required|date',
            'status'       => 'required|in:pending,running,completed,cancelled',
        ]);
        
        // Security Check: Prevent editing financials if repayments exist
        if ($loan->recovered_amount > 0) {
             // If they try to change amount, block it.
             if($request->amount != $loan->total_amount) {
                 return back()->withErrors(['amount' => 'Cannot change Amount because repayments have already started.'])->withInput();
             }
        }

        $amount = (float) $request->amount;
        $installments = ($loan->type === 'advance') ? 1 : (int)$request->installments;
        $installmentAmount = $amount / $installments;

        $loan->update([
            'total_amount' => $amount,
            'installments' => $installments,
            'installment_amount' => $installmentAmount,
            'loan_date' => $request->loan_date,
            'repayment_start_date' => $request->start_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan record updated successfully.');
    }

    public function destroy(Loan $loan)
    {
        if ($loan->business_id !== Auth::user()->business_id) { abort(403); }
        
        if ($loan->recovered_amount > 0) {
            return back()->with('error', 'Cannot delete a loan that has repayments. Please cancel it instead.');
        }

        $loan->delete();

        return redirect()->route('loans.index')->with('success', 'Loan record deleted successfully.');
    }
}