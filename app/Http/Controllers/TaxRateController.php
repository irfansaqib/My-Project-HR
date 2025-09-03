<?php

namespace App\Http\Controllers; // CORRECTED: Replaced hyphen with backslash

use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $validated = $this->validateRequest($request);
        
        $validated['business_id'] = Auth::user()->business_id;
        TaxRate::create($validated);

        return redirect()->route('tax-rates.index')->with('success', 'Tax Rates created successfully.');
    }

    public function show(TaxRate $taxRate)
    {
        $this->authorize('view', $taxRate);
        return view('tax-rates.show', compact('taxRate'));
    }

    public function edit(TaxRate $taxRate)
    {
        return view('tax-rates.edit', compact('taxRate'));
    }

    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $this->validateRequest($request, $taxRate);

        $taxRate->update($validated);

        return redirect()->route('tax-rates.index')->with('success', 'Tax Rates updated successfully.');
    }

    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();
        return redirect()->route('tax-rates.index')->with('success', 'Tax Rate set has been deleted.');
    }

    private function validateRequest(Request $request, TaxRate $taxRate = null): array
    {
        $businessId = Auth::user()->business_id;

        $validated = $request->validate([
            'tax_year' => ['required', 'integer', 'min:2000'],
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
        
        $fromDate = $validated['effective_from_date'];
        $toDate = $validated['effective_to_date'] ?? '9999-12-31';

        $query = TaxRate::where('business_id', $businessId)
            ->where(function($q) use ($fromDate, $toDate) {
                $q->where('effective_from_date', '<=', $toDate)
                  ->where(function($subQ) use ($fromDate) {
                      $subQ->where('effective_to_date', '>=', $fromDate)
                           ->orWhereNull('effective_to_date');
                  });
            });

        if ($taxRate) {
            $query->where('id', '!=', $taxRate->id);
        }

        if ($query->exists()) {
            abort(422, 'The effective date range overlaps with an existing tax period. Please adjust the dates.');
        }

        return $validated;
    }
}