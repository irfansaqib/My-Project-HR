<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\ClientAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:client_management-list|client_management-create|client_management-edit|client_management-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:client_management-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:client_management-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:client_management-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $clients = Client::where('business_id', Auth::user()->business_id)
            ->with(['assignments.employee'])
            ->get();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
{
    // 1. Validation Logic matching Portal
    $rules = [
        'business_name'   => 'required',
        'business_type'   => 'required|in:Individual,Partnership,Company',
        'email'           => 'required|email|unique:users,email',
        'contact_person'  => 'required',
    ];

    // Dynamic Validation based on Type
    if ($request->business_type === 'Individual') {
        $rules['cnic'] = 'required|regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/';
        $rules['ntn']  = 'nullable|regex:/^[A-Z0-9]{7}-[0-9]{1}$/';
    } else {
        $rules['registration_number'] = 'required';
        $rules['ntn'] = 'required|regex:/^[A-Z0-9]{7}-[0-9]{1}$/';
    }

    $request->validate($rules);

    DB::beginTransaction();
    try {
        // Create User Login
        $user = User::create([
            'name' => $request->contact_person,
            'email' => $request->email,
            'password' => Hash::make('12345678'),
            'business_id' => Auth::user()->business_id,
            'role' => 'Client',
        ]);
        
        $user->assignRole('Client');

        // Create Client Profile with NEW Fields
        Client::create([
            'business_id' => Auth::user()->business_id,
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'business_type' => $request->business_type, // New Field
            'cnic' => $request->cnic,                   // New Field
            'registration_number' => $request->registration_number, // New Field
            'ntn' => $request->ntn,                     // Separate Field
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'industry' => $request->industry,
            'address' => $request->address,
            'default_employee_id' => $request->default_employee_id,
            'status' => $request->status ?? 'active'
        ]);

        DB::commit();
        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage())->withInput();
    }
}

    // --- NEW: SHOW METHOD (Fixes your 500 Error) ---
    public function show(Client $client)
    {
        // Load assignments so we can see the team in the profile
        $client->load('assignments.employee');
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $employees = \App\Models\Employee::all(); 
        return view('clients.edit', compact('client', 'employees'));
    }

    public function update(Request $request, Client $client)
    {
        // 1. Validation Logic (Matches Store Logic)
        $rules = [
            'business_name'   => 'required',
            'business_type'   => 'required|in:Individual,Partnership,Company',
            'email'           => 'required|email|unique:users,email,' . $client->user_id, // Allow own email
            'contact_person'  => 'required',
        ];

        // Dynamic Validation based on Type
        if ($request->business_type === 'Individual') {
            $rules['cnic'] = 'required|regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/';
            $rules['ntn']  = 'nullable'; 
        } else {
            $rules['registration_number'] = 'required';
            $rules['ntn'] = 'required|regex:/^[A-Z0-9]{7}-[0-9]{1}$/';
        }

        $request->validate($rules);

        // 2. Prepare Data
        $data = $request->except(['_token', '_method']);
        
        // Handle Nulls based on type (Cleaning up data)
        if ($request->business_type === 'Individual') {
            $data['registration_number'] = null; // Individuals don't have this
        } else {
            $data['cnic'] = null; // Companies don't have this
        }

        // 3. Update Client
        $client->update($data);

        // 4. Update Linked User Login (if Name/Email changed)
        if($client->user) {
            $client->user->update([
                'name' => $request->contact_person,
                'email' => $request->email
            ]);
        }

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    // --- NEW: DESTROY METHOD (Required for Delete button) ---
    public function destroy(Client $client)
    {
        try {
            DB::transaction(function () use ($client) {
                // Delete the associated user login if it exists
                if ($client->user) {
                    $client->user->delete();
                }
                // Delete the client record
                $client->delete();
            });
            return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting client: ' . $e->getMessage());
        }
    }
    
    public function assign(Request $request, Client $client)
    {
        ClientAssignment::create([
            'client_id' => $client->id,
            'employee_id' => $request->employee_id,
            'service_type' => $request->service_type,
            'assigned_by' => Auth::id()
        ]);
        return back()->with('success', 'Employee Assigned.');
    }
}