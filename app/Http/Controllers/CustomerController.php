<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CustomerController extends Controller
{
    /**
     * ✅ NEW: Add a constructor to authorize resource methods.
     * This automatically uses CustomerPolicy for all methods.
     */
    public function __construct()
    {
        $this->authorizeResource(Customer::class, 'customer');
    }

    public function index()
    {
        $customers = Customer::where('business_id', Auth::user()->business_id)->get();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Individual,Partnership,Company',
            'cnic' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'ntn' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $year = date('Y');
        $lastCustomer = Customer::where('business_id', Auth::user()->business_id)
                                ->where('customer_id', 'like', 'C-'.$year.'-%')
                                ->orderBy('customer_id', 'desc')
                                ->first();

        if ($lastCustomer) {
            $lastSerial = (int) substr($lastCustomer->customer_id, -4);
            $newSerial = $lastSerial + 1;
        } else {
            $newSerial = 1;
        }

        $customerID = 'C-' . $year . '-' . str_pad($newSerial, 4, '0', STR_PAD_LEFT);

        $dataToSave = array_merge($validated, [
            'business_id' => Auth::user()->business_id,
            'customer_id' => $customerID,
            'status' => $request->status ?? 'active',
        ]);

        Customer::create($dataToSave);

        return Redirect::route('customers.index')->with('success', 'Customer created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        // ✅ REMOVED: The manual 'if' check is no longer needed.
        // $this->authorizeResource() in the constructor already handled this.
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public a(Customer $customer)
    {
        // ✅ REMOVED: The manual 'if' check is no longer needed.
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        // ✅ REMOVED: The manual 'if' check is no longer needed.

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Individual,Partnership,Company',
            'cnic' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'ntn' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $customer->update($validated);

        return Redirect::route('customers.index')->with('success', 'Customer updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // ✅ REMOVED: The manual 'if' check is no longer needed.

        $customer->delete();

        return Redirect::route('customers.index')->with('success', 'Customer deleted successfully!');
    }
}