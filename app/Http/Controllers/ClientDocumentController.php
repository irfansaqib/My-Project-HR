<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\Task; // Assumed you have a Task model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientDocumentController extends Controller
{
    // Show the list of documents for a specific client
    public function index($clientId)
    {
        $client = Client::findOrFail($clientId);
        // Get all documents for this client, latest first
        $documents = ClientDocument::where('client_id', $clientId)->latest()->get();

        return view('client_documents.index', compact('client', 'documents'));
    }

    // NEW: Show documents linked to a specific Task
    public function byTask($taskId)
    {
        // Fetch the task to ensure it exists
        $task = Task::findOrFail($taskId);

        // Get documents specifically linked to this task
        $documents = ClientDocument::where('task_id', $taskId)
                                   ->latest()
                                   ->get();

        // You can create a specific view for task docs, or reuse the client index
        // sending 'task' instead of 'client'
        return view('client_documents.task_index', compact('task', 'documents'));
    }

    // Store a new document (Updated to handle Task ID)
    public function store(Request $request, $clientId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_file' => 'required|file|max:10240', // Max 10MB
            'description' => 'nullable|string',
            'task_id' => 'nullable|integer|exists:tasks,id', // Validate Task ID if provided
        ]);

        $client = Client::findOrFail($clientId);

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            
            // Generate a safe filename
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Store in the 'public/client_docs' folder
            $path = $file->storeAs('client_docs', $filename, 'public');

            // Save to Database with Task ID link
            ClientDocument::create([
                'client_id'   => $client->id,
                'task_id'     => $request->task_id ?? null, // Link to task if ID is present
                'title'       => $request->title,
                'description' => $request->description,
                'file_path'   => $path,
                'file_type'   => $file->getClientOriginalExtension(),
                'file_size'   => $this->formatSizeUnits($file->getSize()),
            ]);

            return redirect()->back()->with('success', 'Document uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Please select a file.');
    }

    // Download the document
    public function download($id)
    {
        $document = ClientDocument::findOrFail($id);
        
        if (Storage::disk('public')->exists($document->file_path)) {
            return Storage::disk('public')->download($document->file_path, $document->title . '.' . $document->file_type);
        }

        return redirect()->back()->with('error', 'File not found on server.');
    }

    // Delete the document
    public function destroy($id)
    {
        $document = ClientDocument::findOrFail($id);

        // Delete the physical file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete the database record
        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    // Helper to format bytes to KB/MB
    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}