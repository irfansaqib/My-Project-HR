<?php

namespace App\Http\Controllers;

use App\Models\ClientCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ClientCredentialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ClientCredential::where('user_id', Auth::id());

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('user_name', 'LIKE', "%{$search}%")
                  ->orWhere('login_id', 'LIKE', "%{$search}%")
                  ->orWhere('portal_url', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('company_email', 'LIKE', "%{$search}%")
                  ->orWhere('contact_number', 'LIKE', "%{$search}%");
            });
        }

        $credentials = $query->get();

        return view('client-credentials.index', compact('credentials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('client-credentials.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'portal_url' => 'required|string|max:255',
            'user_name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'pin' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'company_email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:255',
            'director_email' => 'nullable|email|max:255',
            'director_email_password' => 'nullable|string|max:255',
            'ceo_name' => 'nullable|string|max:255',
            'ceo_cnic' => 'nullable|string|max:255',
        ]);

        $dataToSave = array_merge($validated, ['user_id' => Auth::id()]);
        ClientCredential::create($dataToSave);
        return Redirect::route('client-credentials.index')->with('success', 'Credential created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClientCredential $clientCredential)
    {
        if ($clientCredential->user_id !== Auth::id()) {
            abort(403);
        }
        return view('client-credentials.show', ['credential' => $clientCredential]);
    }

    public function edit(ClientCredential $clientCredential)
    {
        if ($clientCredential->user_id !== Auth::id()) {
            abort(403);
        }
        return view('client-credentials.edit', ['credential' => $clientCredential]);
    }

    public function update(Request $request, ClientCredential $clientCredential)
    {
        if ($clientCredential->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'portal_url' => 'required|string|max:255',
            'user_name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'pin' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'company_email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:255',
            'director_email' => 'nullable|email|max:255',
            'director_email_password' => 'nullable|string|max:255',
            'ceo_name' => 'nullable|string|max:255',
            'ceo_cnic' => 'nullable|string|max:255',
        ]);

        $clientCredential->update($validated);
        return Redirect::route('client-credentials.index')->with('success', 'Credential updated successfully!');
    }

    public function destroy(ClientCredential $clientCredential)
    {
        if ($clientCredential->user_id !== Auth::id()) {
            abort(403);
        }

        $clientCredential->delete();
        return Redirect::route('client-credentials.index')->with('success', 'Credential deleted successfully!');
    }
}