<?php

namespace App\Http\Controllers;

use App\Models\TaxClient;
use App\Models\TaxClientSalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxClientComponentController extends Controller
{
    public function store(Request $request, TaxClient $client)
    {
        // (Same as before)
        $request->validate([
            'name' => ['required', 'string', function($attribute, $value, $fail) { if (in_array(strtolower($value), ['basic salary', 'basic'])) $fail('System default.'); }],
            'type' => 'required|in:allowance,deduction',
            'is_tax_exempt' => 'nullable|boolean',
            'exemption_type' => 'nullable|required_if:is_tax_exempt,1',
            'exemption_value' => 'nullable|required_if:is_tax_exempt,1|numeric'
        ]);
        TaxClientSalaryComponent::create(array_merge($request->all(), ['tax_client_id' => $client->id]));
        return back()->with('success', 'Added.');
    }

    public function update(Request $request, TaxClient $client, TaxClientSalaryComponent $component)
    {
        if($component->tax_client_id != $client->id) abort(403);
        
        $validated = $request->validate([
            'name' => ['required', 'string', function($attribute, $value, $fail) { if (in_array(strtolower($value), ['basic salary', 'basic'])) $fail('System default.'); }],
            'type' => 'required|in:allowance,deduction',
            'is_tax_exempt' => 'nullable|boolean',
            'exemption_type' => 'nullable|required_if:is_tax_exempt,1',
            'exemption_value' => 'nullable|required_if:is_tax_exempt,1|numeric'
        ]);

        $component->update([
            'name' => $request->name,
            'type' => $request->type,
            'is_tax_exempt' => $request->boolean('is_tax_exempt'),
            'exemption_type' => $request->exemption_type,
            'exemption_value' => $request->exemption_value,
        ]);

        return back()->with('success', 'Component updated.');
    }

    public function destroy(TaxClient $client, TaxClientSalaryComponent $component)
    {
        if($component->tax_client_id != $client->id) abort(403);
        $component->delete();
        return back()->with('success', 'Deleted.');
    }
}