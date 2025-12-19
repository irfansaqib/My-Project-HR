<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class ClientMessageController extends Controller
{
    public function index()
    {
        // 1. Get the Logged-in Client's ID
        $clientId = Auth::user()->client->id;

        // 2. Fetch Tasks that have messages
        // We eager load 'messages' to avoid 100 database queries
        $tasks = Task::where('client_id', $clientId)
            ->whereHas('messages') // Only get tasks that actually have chat history
            ->with(['messages' => function($query) {
                $query->latest(); // Order messages new to old
            }])
            ->latest('updated_at') // Show recently active tasks first
            ->get();

        return view('client_portal.messages.index', compact('tasks'));
    }
}