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
        // 1. Define Rules based on Selection
        $idRules = ['required', 'unique:clients,ntn_cnic'];
        
        if ($request->id_type === 'CNIC') {
            // 13 Digits, Numeric Only
            $idRules[] = 'regex:/^[0-9]{13}$/'; 
        } else {
            // NTN: 7 AlphaNum - 1 AlphaNum (Total 9 chars)
            $idRules[] = 'regex:/^[A-Za-z0-9]{7}-[A-Za-z0-9]{1}$/';
        }

        $request->validate([
            'business_name' => 'required',
            'id_type'       => 'required|in:NTN,CNIC',
            'ntn_cnic'      => $idRules,
            'email'         => 'required|email|unique:users,email',
            'contact_person'=> 'required',
        ], [
            'ntn_cnic.regex' => $request->id_type === 'CNIC' 
                ? 'CNIC must be exactly 13 numeric digits (without dashes).' 
                : 'NTN must be in format XXXXXXX-X (e.g. A123456-8).'
        ]);

        DB::beginTransaction();
        try {
            // Create Login User
            $user = User::create([
                'name' => $request->contact_person,
                'email' => $request->email,
                'password' => Hash::make('12345678'),
                'business_id' => Auth::user()->business_id,
                'role' => 'Client', 
            ]);
            
            // Check Role exists before assigning (Safety)
            if (\Spatie\Permission\Models\Role::where('name', 'Client')->exists()) {
                $user->assignRole('Client');
            }

            // Create Client Profile
            Client::create([
                'business_id' => Auth::user()->business_id,
                'user_id' => $user->id,
                'business_name' => $request->business_name,
                'id_type' => $request->id_type, // Saved Here
                'ntn_cnic' => $request->ntn_cnic,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'industry' => $request->industry,
                'address' => $request->address,
                'status' => $request->status ?? 'active'
            ]);

            DB::commit();
            return redirect()->route('clients.index')->with('success', 'Client created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        // 1. Define Rules based on Selection
        $idRules = ['required', \Illuminate\Validation\Rule::unique('clients')->ignore($client->id)];
        
        if ($request->id_type === 'CNIC') {
            $idRules[] = 'regex:/^[0-9]{13}$/'; 
        } else {
            $idRules[] = 'regex:/^[A-Za-z0-9]{7}-[A-Za-z0-9]{1}$/';
        }

        $request->validate([
            'business_name' => 'required',
            'id_type'       => 'required|in:NTN,CNIC',
            'ntn_cnic'      => $idRules,
            'contact_person'=> 'required',
        ], [
            'ntn_cnic.regex' => $request->id_type === 'CNIC' 
                ? 'CNIC must be exactly 13 numeric digits.' 
                : 'NTN must be in format XXXXXXX-X.'
        ]);

        $client->update([
            'business_name' => $request->business_name,
            'id_type' => $request->id_type,
            'ntn_cnic' => $request->ntn_cnic,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'industry' => $request->industry,
            'address' => $request->address,
            'status' => $request->status,
        ]);

        if($client->user) {
            $client->user->update(['name' => $request->contact_person]);
        }

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
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