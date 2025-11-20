<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SalaryComponentController extends Controller
{
    /**
     * Display a listing of the salary components.
     */
    public function index()
    {
        $businessId = Auth::user()->business_id;

        $components = SalaryComponent::where('business_id', $businessId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('salary-components.index', compact('components'));
    }

    /**
     * Show the form for creating a new salary component.
     */
    public function create()
    {
        return view('salary-components.create');
    }

    /**
     * Store a newly created salary component in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:allowance,deduction',
            'is_tax_exempt'     => 'nullable|boolean',
            'exemption_type'    => 'nullable|string|max:255',
            'exemption_value'   => 'nullable|numeric',
            'is_tax_component'  => 'nullable|boolean',
        ]);

        $businessId = Auth::user()->business_id;

        // ✅ Enforce only one deduction as tax component
        if ($request->boolean('is_tax_component') && $request->input('type') === 'deduction') {
            SalaryComponent::where('business_id', $businessId)
                ->where('type', 'deduction')
                ->update(['is_tax_component' => false]);
        }

        $component = new SalaryComponent($validated);
        $component->business_id = $businessId;
        $component->is_tax_exempt = $request->boolean('is_tax_exempt');
        $component->is_tax_component = $request->boolean('is_tax_component');
        $component->save();

        return redirect()->route('salary-components.index')
            ->with('success', 'Salary component created successfully.');
    }

    /**
     * Show the form for editing the specified salary component.
     */
    public function edit(SalaryComponent $salaryComponent)
    {
        $this->authorize('update', $salaryComponent);
        return view('salary-components.edit', compact('salaryComponent'));
    }

    /**
     * Update the specified salary component in storage.
     */
    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $this->authorize('update', $salaryComponent);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:allowance,deduction',
            'is_tax_exempt'     => 'nullable|boolean',
            'exemption_type'    => 'nullable|string|max:255',
            'exemption_value'   => 'nullable|numeric',
            'is_tax_component'  => 'nullable|boolean',
        ]);

        $businessId = Auth::user()->business_id;

        // ✅ If this deduction is being marked as the tax component
        if ($request->boolean('is_tax_component') && $request->input('type') === 'deduction') {
            SalaryComponent::where('business_id', $businessId)
                ->where('type', 'deduction')
                ->where('id', '<>', $salaryComponent->id)
                ->update(['is_tax_component' => false]);
        }

        $salaryComponent->fill($validated);
        $salaryComponent->is_tax_exempt = $request->boolean('is_tax_exempt');
        $salaryComponent->is_tax_component = $request->boolean('is_tax_component');
        $salaryComponent->save();

        return redirect()->route('salary-components.index')
            ->with('success', 'Salary component updated successfully.');
    }

    /**
     * Remove the specified salary component from storage.
     */
    public function destroy(SalaryComponent $salaryComponent)
    {
        try {
            $this->authorize('delete', $salaryComponent);
            $salaryComponent->delete();
            return redirect()->route('salary-components.index')
                ->with('success', 'Salary component deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('SalaryComponent delete failed: ' . $e->getMessage());
            return back()->withErrors('Error deleting component: ' . $e->getMessage());
        }
    }
}
