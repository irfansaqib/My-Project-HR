<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FundController extends Controller
{
    public function index()
    {
        $funds = Fund::where('business_id', Auth::user()->business_id)->with('salaryComponent')->get();
        return view('funds.index', compact('funds'));
    }

    public function create()
    {
        // Only fetch deductions marked as 'Contributory'
        $components = SalaryComponent::where('business_id', Auth::user()->business_id)
            ->where('type', 'deduction')
            ->where('is_contributory', true)
            ->orderBy('name')
            ->get();
            
        return view('funds.create', compact('components'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'salary_component_id' => 'required|exists:salary_components,id',
            'employer_contribution_type' => 'required|in:match_employee,percentage_of_basic,fixed_amount',
            'employer_contribution_value' => 'nullable|required_if:employer_contribution_type,percentage_of_basic,fixed_amount|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $fund = new Fund($validated);
        $fund->business_id = Auth::user()->business_id;
        $fund->save();

        return redirect()->route('funds.index')->with('success', 'Fund configured successfully.');
    }

    /**
     * ✅ ADDED: Show Fund Details & History
     */
    public function show(Fund $fund)
    {
        if ($fund->business_id !== Auth::user()->business_id) abort(403);
        
        // Load recent contributions for this fund
        $recentContributions = $fund->contributions()
            ->with('employee')
            ->orderBy('transaction_date', 'desc')
            ->take(20)
            ->get();

        // Calculate Totals
        $totalEmployeeShare = $fund->contributions->where('type', 'employee_share')->sum('amount');
        $totalEmployerShare = $fund->contributions->where('type', 'employer_share')->sum('amount');
        $totalProfit = $fund->contributions->where('type', 'profit_credit')->sum('amount');
        $totalBalance = $totalEmployeeShare + $totalEmployerShare + $totalProfit;

        return view('funds.show', compact('fund', 'recentContributions', 'totalEmployeeShare', 'totalEmployerShare', 'totalProfit', 'totalBalance'));
    }

    public function edit(Fund $fund)
    {
        if ($fund->business_id !== Auth::user()->business_id) abort(403);

        $components = SalaryComponent::where('business_id', Auth::user()->business_id)
            ->where('type', 'deduction')
            ->where('is_contributory', true)
            ->orderBy('name')
            ->get();

        return view('funds.edit', compact('fund', 'components'));
    }

    public function update(Request $request, Fund $fund)
    {
        if ($fund->business_id !== Auth::user()->business_id) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'salary_component_id' => 'required|exists:salary_components,id',
            'employer_contribution_type' => 'required|in:match_employee,percentage_of_basic,fixed_amount',
            'employer_contribution_value' => 'nullable|required_if:employer_contribution_type,percentage_of_basic,fixed_amount|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $fund->update($validated);

        return redirect()->route('funds.index')->with('success', 'Fund configuration updated.');
    }

    public function destroy(Fund $fund)
    {
        if ($fund->business_id !== Auth::user()->business_id) abort(403);
        $fund->delete();
        return redirect()->route('funds.index')->with('success', 'Fund deleted successfully.');
    }
}