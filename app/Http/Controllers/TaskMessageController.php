<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskMessage;
use App\Models\User;
use App\Notifications\TaskAlert; // ✅ Correct Import for Notifications
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('task_attachments', 'public');
        }

        // 3. Save Message to Database
        TaskMessage::create([
            'task_id' => $task->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'attachment_path' => $path,
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
                    // ✅ We must select the User Object, NOT the email string
                    $recipient = $task->assignedEmployee; 
                } else {
                    // If no employee assigned, notify an Admin/Owner
                    $recipient = User::role(['Admin', 'Owner'])->first();
                }
            } else {
                // Employee/Admin sent message -> Notify Client
                // ✅ We must select the User Object, NOT the email string
                $recipient = $task->client; 
            }

            // Send the Notification
            if ($recipient) {
                // This sends BOTH the Email and adds it to the Database for the Bell Icon
                // Ensure App\Notifications\TaskAlert's via() method returns ['mail', 'database']
                $recipient->notify(new TaskAlert(
                    $task, 
                    'message', 
                    "New Message: " . Str::limit($request->message, 30)
                ));
            }
        } catch (\Exception $e) {
            // Log error if needed, prevents crash if mail server fails
            // \Illuminate\Support\Facades\Log::error("Notification Error: " . $e->getMessage());
        }

        return back()->with('success', 'Message sent.');
    }
}