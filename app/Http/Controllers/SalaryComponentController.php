<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SalaryComponentController extends Controller
{
    public function index()
    {
        $businessId = Auth::user()->business_id;
        $components = SalaryComponent::where('business_id', $businessId)
            ->orderBy('type')->orderBy('name')->get();
        return view('salary-components.index', compact('components'));
    }

    public function create()
    {
        return view('salary-components.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:allowance,deduction',
            'is_tax_exempt'     => 'nullable|boolean',
            'exemption_type'    => 'nullable|required_if:is_tax_exempt,1|string',
            'exemption_value'   => 'nullable|required_if:is_tax_exempt,1|numeric|min:0',
            'is_advance'        => 'nullable|boolean',
            'is_loan'           => 'nullable|boolean',
            'is_contributory'   => 'nullable|boolean', // ✅ Added
            'is_tax_component'  => 'nullable|boolean',
        ]);

        $businessId = Auth::user()->business_id;

        if ($request->input('type') !== 'deduction') {
            $validated['is_advance'] = false;
            $validated['is_loan'] = false;
            $validated['is_contributory'] = false;
            $validated['is_tax_component'] = false;
        }
        
        // Enforce uniqueness for Tax/Advance/Loan components
        if ($request->boolean('is_advance')) SalaryComponent::where('business_id', $businessId)->update(['is_advance' => false]);
        if ($request->boolean('is_loan')) SalaryComponent::where('business_id', $businessId)->update(['is_loan' => false]);
        if ($request->boolean('is_tax_component')) SalaryComponent::where('business_id', $businessId)->update(['is_tax_component' => false]);

        $component = new SalaryComponent($validated);
        $component->business_id = $businessId;
        $component->is_tax_exempt = $request->boolean('is_tax_exempt');
        $component->is_advance = $request->boolean('is_advance');
        $component->is_loan = $request->boolean('is_loan');
        $component->is_contributory = $request->boolean('is_contributory'); // ✅ Added
        $component->is_tax_component = $request->boolean('is_tax_component');
        $component->save();

        return redirect()->route('salary-components.index')->with('success', 'Salary component created successfully.');
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        return view('salary-components.edit', compact('salaryComponent'));
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('salary_components')->where('business_id', auth()->user()->business_id)->ignore($salaryComponent->id)],
            'type' => ['required', Rule::in(['allowance', 'deduction'])],
            'is_tax_exempt' => 'sometimes|boolean',
            'exemption_type' => 'nullable|required_if:is_tax_exempt,1|string',
            'exemption_value' => 'nullable|required_if:is_tax_exempt,1|numeric|min:0',
            'is_advance'        => 'nullable|boolean',
            'is_loan'           => 'nullable|boolean',
            'is_contributory'   => 'nullable|boolean', // ✅ Added
            'is_tax_component'  => 'nullable|boolean',
        ]);

        $businessId = Auth::user()->business_id;

        if ($request->input('type') !== 'deduction') {
            $validated['is_advance'] = false;
            $validated['is_loan'] = false;
            $validated['is_contributory'] = false;
            $validated['is_tax_component'] = false;
        }

        if ($request->boolean('is_advance')) SalaryComponent::where('business_id', $businessId)->where('id', '<>', $salaryComponent->id)->update(['is_advance' => false]);
        if ($request->boolean('is_loan')) SalaryComponent::where('business_id', $businessId)->where('id', '<>', $salaryComponent->id)->update(['is_loan' => false]);
        if ($request->boolean('is_tax_component')) SalaryComponent::where('business_id', $businessId)->where('id', '<>', $salaryComponent->id)->update(['is_tax_component' => false]);

        $salaryComponent->fill($validated);
        $salaryComponent->is_tax_exempt = $request->boolean('is_tax_exempt');
        $salaryComponent->is_advance = $request->boolean('is_advance');
        $salaryComponent->is_loan = $request->boolean('is_loan');
        $salaryComponent->is_contributory = $request->boolean('is_contributory'); // ✅ Added
        $salaryComponent->is_tax_component = $request->boolean('is_tax_component');
        
        if (!$salaryComponent->is_tax_exempt) {
            $salaryComponent->exemption_type = null;
            $salaryComponent->exemption_value = null;
        }

        $salaryComponent->save();

        return redirect()->route('salary-components.index')->with('success', 'Salary component updated successfully.');
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        try {
            $salaryComponent->delete();
            return redirect()->route('salary-components.index')->with('success', 'Salary component deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('SalaryComponent delete failed: ' . $e->getMessage());
            return back()->withErrors('Error deleting component: ' . $e->getMessage());
        }
    }
}