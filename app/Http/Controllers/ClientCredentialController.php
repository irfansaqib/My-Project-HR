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

        // Apply filters from the request
        if ($request->filled('company_name')) {
            $query->where('company_name', 'LIKE', '%' . $request->company_name . '%');
        }
        if ($request->filled('user_name')) {
            $query->where('user_name', 'LIKE', '%' . $request->user_name . '%');
        }
        if ($request->filled('portal_url')) {
            $query->where('portal_url', 'LIKE', '%' . $request->portal_url . '%');
        }

        $credentials = $query->latest()->paginate(15);

        // If the request is an AJAX request, return only the table partial
        if ($request->ajax()) {
            return view('client-credentials._credentials_table', compact('credentials'))->render();
        }

        // For regular page loads, return the full view
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

