<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Task;

class AdminDocumentController extends Controller
{
    /**
     * Step 1: Show list of Clients to choose from.
     * View Path: resources/views/tasks/documents_clients.blade.php
     */
    public function index()
    {
        $clients = Client::orderBy('business_name', 'asc')->get();
        return view('tasks.documents_clients', compact('clients'));
    }

    /**
     * Step 2: Show Documents for the selected Client.
     * View Path: resources/views/tasks/documents_show.blade.php
     */
    public function show($id)
    {
        $client = Client::findOrFail($id);
        
        // Fetch tasks for this client that have documents
        $tasks = Task::where('client_id', $client->id)
                    ->with('documents')
                    ->orderBy('id', 'desc')
                    ->get();

        return view('tasks.documents_show', compact('client', 'tasks'));
    }
}