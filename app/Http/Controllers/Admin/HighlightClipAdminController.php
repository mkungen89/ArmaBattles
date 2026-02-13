<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HighlightClip;
use App\Notifications\VideoApprovedNotification;
use App\Notifications\VideoRejectedNotification;
use Illuminate\Http\Request;

class HighlightClipAdminController extends Controller
{
    use \App\Traits\LogsAdminActions;

    public function index(Request $request)
    {
        // Eager load user to prevent N+1 queries in approve/reject actions
        $query = HighlightClip::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'ilike', "%{$search}%");
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured === 'yes');
        }

        $clips = $query->orderByDesc('created_at')->paginate(25);

        $stats = [
            'total' => HighlightClip::count(),
            'pending' => HighlightClip::where('status', 'pending')->count(),
            'approved' => HighlightClip::where('status', 'approved')->count(),
            'featured' => HighlightClip::where('is_featured', true)->count(),
            'total_votes' => HighlightClip::sum('votes'),
        ];

        return view('admin.clips.index', compact('clips', 'stats'));
    }

    public function approve(HighlightClip $clip)
    {
        $clip->load('user');
        $clip->update(['status' => 'approved']);

        // Notify the user
        if ($clip->user) {
            $clip->user->notify(new VideoApprovedNotification($clip));
        }

        // Invalidate clip of the week cache
        \Cache::forget('clip_of_the_week');

        $this->logAction('clip.approved', 'HighlightClip', $clip->id, [
            'title' => $clip->title,
            'user_id' => $clip->user_id,
        ]);

        return back()->with('success', 'Video has been approved and user notified.');
    }

    public function reject(HighlightClip $clip)
    {
        $clip->load('user');
        $reason = request('reason'); // Optional rejection reason
        $clip->update(['status' => 'rejected']);

        // Notify the user
        if ($clip->user) {
            $clip->user->notify(new VideoRejectedNotification($clip, $reason));
        }

        $this->logAction('clip.rejected', 'HighlightClip', $clip->id, [
            'title' => $clip->title,
            'user_id' => $clip->user_id,
            'reason' => $reason,
        ]);

        return back()->with('success', 'Video has been rejected and user notified.');
    }

    public function feature(HighlightClip $clip)
    {
        $clip->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);

        // Invalidate clip of the week cache
        \Cache::forget('clip_of_the_week');

        $this->logAction('clip.featured', 'HighlightClip', $clip->id, [
            'title' => $clip->title,
        ]);

        return back()->with('success', 'Video has been featured.');
    }

    public function unfeature(HighlightClip $clip)
    {
        $clip->update([
            'is_featured' => false,
            'featured_at' => null,
        ]);

        $this->logAction('clip.unfeatured', 'HighlightClip', $clip->id, [
            'title' => $clip->title,
        ]);

        return back()->with('success', 'Video removed from featured.');
    }

    public function destroy(HighlightClip $clip)
    {
        $clipTitle = $clip->title;
        $clipId = $clip->id;

        $clip->delete();

        $this->logAction('clip.deleted', 'HighlightClip', $clipId, [
            'title' => $clipTitle,
        ]);

        return redirect()->route('admin.clips.index')->with('success', 'Video deleted.');
    }
}
