<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\ClientMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskMessageController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'attachment' => 'nullable|file|max:2048' // 2MB Max
        ]);

        // Security: Ensure User is allowed to comment on this task
        $user = Auth::user();
        $isClient = $user->hasRole('Client');
        $isAdminOrStaff = $user->hasRole(['Admin', 'Owner']) || $user->employee;

        if ($isClient && $task->client_id !== $user->client->id) {
            abort(403, 'Unauthorized');
        }
        
        // Save Attachment
        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('task-attachments', 'public');
        }

        ClientMessage::create([
            'task_id' => $task->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'attachment_path' => $path
        ]);

        $sender = Auth::user();
        $isClient = $sender->hasRole('Client');

        if ($isClient) {
            // Client sent message -> Notify Assigned Employee OR Admin
            $recipient = $task->assignedEmployee->email ?? User::role('Admin')->first()->email;
        } else {
            // Employee sent message -> Notify Client
            $recipient = $task->client->email; // The client contact email
        }

        if ($recipient) {
            Mail::to($recipient)->send(new TaskNotification($task, 'message', $request->message));
        }
        
        return back()->with('success', 'Message sent.');
    }
}