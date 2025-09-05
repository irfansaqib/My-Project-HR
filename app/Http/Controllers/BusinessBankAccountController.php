<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BusinessBankAccountController extends Controller
{
    public function index()
    {
        $business = Business::where('id', Auth::user()->business_id)->firstOrFail();
        // Use the relationship to get the accounts
        $bankAccounts = $business->bankAccounts()->paginate(10);
        return view('business-bank-accounts.index', compact('business', 'bankAccounts'));
    }

    public function create()
    {
        return view('business-bank-accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_title' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        $validated['business_id'] = Auth::user()->business_id;
        $validated['is_default'] = $request->has('is_default');
        
        BusinessBankAccount::create($validated);

        return redirect()->route('business-bank-accounts.index')->with('success', 'Bank account added successfully.');
    }

    public function edit(BusinessBankAccount $businessBankAccount)
    {
        Gate::authorize('update', $businessBankAccount);
        return view('business-bank-accounts.edit', compact('businessBankAccount'));
    }

    public function update(Request $request, BusinessBankAccount $businessBankAccount)
    {
        Gate::authorize('update', $businessBankAccount);
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_title' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);
        
        $validated['is_default'] = $request->has('is_default');
        $businessBankAccount->update($validated);
        
        return redirect()->route('business-bank-accounts.index')->with('success', 'Bank account updated successfully.');
    }

    public function destroy(BusinessBankAccount $businessBankAccount)
    {
        Gate::authorize('delete', $businessBankAccount);
        $businessBankAccount->delete();
        return redirect()->route('business-bank-accounts.index')->with('success', 'Bank account deleted successfully.');
    }
}