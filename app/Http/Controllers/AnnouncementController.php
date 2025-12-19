<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * 1. LIST & CREATE PAGE
     */
    public function index()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->get();
        return view('announcements.create', compact('announcements'));
    }

    /**
     * 2. STORE NEW ANNOUNCEMENT
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
        ]);

        Announcement::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_by' => Auth::id(),
            'is_active' => true,
            // Checkbox: If checked returns 'on'/'1', if unchecked it is missing
            'is_client_visible' => $request->has('is_client_visible') ? 1 : 0,
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement published!');
    }

    /**
     * 3. EDIT PAGE
     */
    public function edit($id)
    {
        $announcement = Announcement::findOrFail($id);
        return view('announcements.edit', compact('announcement'));
    }

    /**
     * 4. UPDATE ANNOUNCEMENT
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
        ]);

        $announcement = Announcement::findOrFail($id);
        
        $announcement->update([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            // Checkbox handling
            'is_client_visible' => $request->has('is_client_visible') ? 1 : 0,
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated!');
    }

    /**
     * 5. DELETE ANNOUNCEMENT
     */
    public function destroy($id)
    {
        Announcement::findOrFail($id)->delete();
        return back()->with('success', 'Announcement deleted.');
    }
}