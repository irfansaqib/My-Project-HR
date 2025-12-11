<?php

namespace App\Http\Controllers;

use App\Models\FundContribution;
use App\Models\Employee;
use App\Models\Fund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FundTransactionController extends Controller
{
    /**
     * Display a listing of Fund Contributions (Ledger).
     */
    public function index(Request $request)
    {
        $businessId = Auth::user()->business_id;
        $funds = Fund::where('business_id', $businessId)->get();
        $employees = Employee::where('business_id', $businessId)->orderBy('name')->get();

        $query = FundContribution::with(['employee', 'fund'])
            ->whereHas('fund', fn($q) => $q->where('business_id', $businessId));

        // Filters (similar to the report, but simplified)
        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('fund_id')) $query->where('fund_id', $request->fund_id);
        if ($request->filled('type')) $query->where('type', $request->type);
        
        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(20);

        // Fetch types for the filter dropdown
        $types = ['employee_share', 'employer_share', 'profit_credit', 'withdrawal'];

        return view('funds.transactions.index', compact('transactions', 'employees', 'funds', 'types'));
    }

    /**
     * Show the form for editing a specific transaction (only profit_credit and withdrawal).
     */
    public function edit(FundContribution $transaction)
    {
        // Only allow editing/deleting non-payroll transactions
        if ($transaction->salary_sheet_item_id) {
            return back()->with('error', 'Cannot edit/delete transactions linked to a Payroll Sheet. Delete the Salary Sheet instead.');
        }

        if (!in_array($transaction->type, ['profit_credit', 'withdrawal'])) {
             return back()->with('error', 'Only manual Profit Credits or Withdrawals can be edited.');
        }

        $funds = Fund::where('business_id', Auth::user()->business_id)->get();
        $employees = Employee::where('business_id', Auth::user()->business_id)->orderBy('name')->get();

        return view('funds.transactions.edit', compact('transaction', 'funds', 'employees'));
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, FundContribution $transaction)
    {
        if ($transaction->salary_sheet_item_id) {
            return back()->with('error', 'Cannot edit transactions linked to a Payroll Sheet.');
        }

        $validated = $request->validate([
            'fund_id' => 'required|exists:funds,id',
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
        ]);
        
        // Ensure type remains the same
        $validated['type'] = $transaction->type;

        // Perform the update
        $transaction->update($validated);

        return redirect()->route('funds.transactions.index')->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(FundContribution $transaction)
    {
        if ($transaction->salary_sheet_item_id) {
            return back()->with('error', 'Cannot delete transactions linked to a Payroll Sheet.');
        }
        
        // Deleting the contribution triggers the Loan model event if it was a Refundable Withdrawal (which is managed separately now)
        // If it was a standard withdrawal/profit credit, deleting it recalculates the fund balance.
        $transaction->delete();

        return redirect()->route('funds.transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}