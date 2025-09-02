<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class SalaryComponentController extends Controller
{
    public function index()
    {
        $allowances = SalaryComponent::where('type', 'allowance')->orderBy('name')->get();
        $deductions = SalaryComponent::where('type', 'deduction')->orderBy('name')->get();
        return view('salary-components.index', compact('allowances', 'deductions'));
    }

    public function create()
    {
        return view('salary-components.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('salary_components')->where('business_id', auth()->user()->business_id)],
            'type' => ['required', Rule::in(['allowance', 'deduction'])],
            'is_tax_exempt' => 'sometimes|boolean',
            'exemption_type' => 'nullable|required_if:is_tax_exempt,1|string',
            'exemption_value' => 'nullable|required_if:is_tax_exempt,1|numeric|min:0',
        ]);
        
        $validated['is_tax_exempt'] = $request->has('is_tax_exempt');

        $dataToSave = array_merge($validated, ['business_id' => Auth::user()->business_id]);
        
        SalaryComponent::create($dataToSave);

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
        ]);

        $validated['is_tax_exempt'] = $request->has('is_tax_exempt');
        
        if (!$validated['is_tax_exempt']) {
            $validated['exemption_type'] = null;
            $validated['exemption_value'] = null;
        }

        $salaryComponent->update($validated);

        return redirect()->route('salary-components.index')->with('success', 'Salary component updated successfully.');
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        $salaryComponent->delete();
        return redirect()->route('salary-components.index')->with('success', 'Salary component deleted successfully.');
    }
}