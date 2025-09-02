<?php

namespace App\Http\Controllers;

use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxRateController extends Controller
{
    public function index()
    {
        $taxRates = TaxRate::orderBy('tax_year', 'desc')->get();
        return view('tax-rates.index', compact('taxRates'));
    }

    public function create()
    {
        return view('tax-rates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tax_year' => 'required|integer|min:2000|unique:tax_rates,tax_year,NULL,id,business_id,' . Auth::id(),
            'effective_from_date' => 'required|date',
            'effective_to_date' => 'nullable|date|after_or_equal:effective_from_date',
            'surcharge_threshold' => 'nullable|numeric|min:0',
            'surcharge_rate_percentage' => 'nullable|numeric|min:0|max:100',
            'slabs' => 'required|array|min:1',
            'slabs.*.income_from' => 'required|numeric|min:0',
            'slabs.*.income_to' => 'nullable|numeric',
            'slabs.*.fixed_tax_amount' => 'required|numeric|min:0',
            'slabs.*.tax_rate_percentage' => 'required|numeric|min:0|max:100',
        ]);
        
        $validated['business_id'] = Auth::user()->business_id;
        TaxRate::create($validated);

        return redirect()->route('tax-rates.index')->with('success', 'Tax Rates for the year have been created successfully.');
    }

    public function edit(TaxRate $taxRate)
    {
        return view('tax-rates.edit', compact('taxRate'));
    }

    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $request->validate([
            'tax_year' => 'required|integer|min:2000|unique:tax_rates,tax_year,' . $taxRate->id . ',id,business_id,' . Auth::id(),
            'effective_from_date' => 'required|date',
            'effective_to_date' => 'nullable|date|after_or_equal:effective_from_date',
            'surcharge_threshold' => 'nullable|numeric|min:0',
            'surcharge_rate_percentage' => 'nullable|numeric|min:0|max:100',
            'slabs' => 'required|array|min:1',
            'slabs.*.income_from' => 'required|numeric|min:0',
            'slabs.*.income_to' => 'nullable|numeric',
            'slabs.*.fixed_tax_amount' => 'required|numeric|min:0',
            'slabs.*.tax_rate_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $taxRate->update($validated);

        return redirect()->route('tax-rates.index')->with('success', 'Tax Rates for the year have been updated successfully.');
    }

    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();
        return redirect()->route('tax-rates.index')->with('success', 'Tax Rate set has been deleted.');
    }
}