<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnnouncementController extends Controller
{
    use \App\Traits\LogsAdminActions;

    public function index()
    {
        $announcements = Announcement::orderByDesc('created_at')->paginate(20);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,success,danger',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $announcement = Announcement::create($validated);

        // Clear cache
        Cache::forget('active_announcements');

        $this->logAction('announcement.created', 'Announcement', $announcement->id, [
            'type' => $announcement->type,
            'message' => $announcement->message,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,success,danger',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $announcement->update($validated);

        // Clear cache
        Cache::forget('active_announcements');

        $this->logAction('announcement.updated', 'Announcement', $announcement->id, [
            'type' => $announcement->type,
            'message' => $announcement->message,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        $announcementId = $announcement->id;
        $announcementMessage = $announcement->message;

        $announcement->delete();

        // Clear cache
        Cache::forget('active_announcements');

        $this->logAction('announcement.deleted', 'Announcement', $announcementId, [
            'message' => $announcementMessage,
        ]);

        return back()->with('success', 'Announcement deleted successfully!');
    }

    public function toggle(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);

        // Clear cache
        Cache::forget('active_announcements');

        $this->logAction('announcement.toggled', 'Announcement', $announcement->id, [
            'is_active' => $announcement->is_active,
            'message' => $announcement->message,
        ]);

        return back()->with('success', 'Announcement ' . ($announcement->is_active ? 'activated' : 'deactivated') . ' successfully!');
    }
}
