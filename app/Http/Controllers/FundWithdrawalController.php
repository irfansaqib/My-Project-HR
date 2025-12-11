<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\FundContribution;
use App\Models\Employee;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FundWithdrawalController extends Controller
{
    public function create()
    {
        $funds = Fund::where('business_id', Auth::user()->business_id)->get();
        $employees = Employee::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        return view('funds.withdraw', compact('funds', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fund_id' => 'required|exists:funds,id',
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'type' => 'required|in:permanent,refundable',
            // Conditional validation for refundable
            'installments' => 'nullable|required_if:type,refundable|integer|min:1',
            'repayment_start_date' => 'nullable|required_if:type,refundable|date',
            'description' => 'nullable|string',
        ]);

        // 1. Check Fund Balance
        $currentBalance = FundContribution::where('fund_id', $request->fund_id)
            ->where('employee_id', $request->employee_id)
            ->get()
            ->sum(fn($row) => in_array($row->type, ['employee_share', 'employer_share', 'profit_credit']) ? $row->amount : -$row->amount);

        if ($request->amount > $currentBalance) {
            return back()->withErrors(['amount' => 'Insufficient fund balance. Available: ' . number_format($currentBalance)])->withInput();
        }

        DB::transaction(function () use ($request) {
            
            // 2. Record Withdrawal (Debit from Fund)
            FundContribution::create([
                'fund_id' => $request->fund_id,
                'employee_id' => $request->employee_id,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'transaction_date' => $request->transaction_date,
                'description' => $request->description ?? ($request->type === 'refundable' ? 'Refundable Loan' : 'Permanent Withdrawal'),
            ]);

            // 3. If Refundable, Create a Loan Record
            if ($request->type === 'refundable') {
                $installments = (int) $request->installments;
                $installmentAmount = $request->amount / $installments;

                Loan::create([
                    'business_id' => Auth::user()->business_id,
                    'employee_id' => $request->employee_id,
                    'fund_id' => $request->fund_id, // Link to fund
                    'type' => 'loan', // Treated as a standard loan for payroll logic
                    'total_amount' => $request->amount,
                    'installments' => $installments,
                    'installment_amount' => $installmentAmount,
                    'recovered_amount' => 0,
                    'loan_date' => $request->transaction_date,
                    'repayment_start_date' => $request->repayment_start_date,
                    'status' => 'running',
                    'notes' => "Loan against Fund: " . ($request->description ?? ''),
                ]);
            }
        });

        return redirect()->route('funds.index')->with('success', 'Withdrawal processed successfully.');
    }
}