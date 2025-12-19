<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskMessage;
use App\Models\ClientDocument; // <--- IMPORT THIS
use App\Models\User;
use App\Notifications\TaskAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TaskMessageController extends Controller
{
    public function store(Request $request, Task $task)
    {
        // 1. Validate Input
        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max limit
        ]);

        // 2. Handle File Upload
        $path = null;
        $fileName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = $file->getClientOriginalName();
            
            // Save file to storage
            $path = $file->storeAs('task_attachments', time() . '_' . $fileName, 'public');

            // ---------------------------------------------------------
            // NEW: Also save to 'ClientDocuments' table so it appears in "My Documents"
            // ---------------------------------------------------------
            ClientDocument::create([
                'client_id'   => $task->client_id, // Link to the Task's Client
                'task_id'     => $task->id,        // Link to this Task
                'title'       => $fileName,
                'description' => 'Uploaded via Chat in Task #' . $task->id,
                'file_path'   => $path,
                'file_type'   => $file->getClientOriginalExtension(),
                'file_size'   => $this->formatSizeUnits($file->getSize()),
            ]);
        }

        // 3. Save Message to Database
        TaskMessage::create([
            'task_id' => $task->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'attachment_path' => $path,
            'file_name' => $fileName,
        ]);

        // 4. Send Notification (Email + Bell)
        try {
            $sender = Auth::user();
            $recipient = null;

            // Check if the sender is the Client
            $isClient = $sender->hasRole('Client'); 

            if ($isClient) {
                // Client sent message -> Notify Assigned Employee
                if ($task->assignedEmployee) {
                    $recipient = $task->assignedEmployee; 
                } else {
                    $recipient = User::role(['Admin', 'Owner'])->first();
                }
            } else {
                // Employee/Admin sent message -> Notify Client
                $recipient = $task->client; 
            }

            // Send the Notification
            if ($recipient) {
                $recipient->notify(new TaskAlert(
                    $task, 
                    'message', 
                    "New Message from {$sender->name}: " . Str::limit($request->message, 30)
                ));
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return back()->with('success', 'Message sent.');
    }

    // Helper to format bytes to KB/MB (Required for ClientDocument table)
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