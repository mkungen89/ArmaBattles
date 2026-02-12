<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HighlightClip;
use App\Notifications\VideoApprovedNotification;
use App\Notifications\VideoRejectedNotification;
use Illuminate\Http\Request;

class HighlightClipAdminController extends Controller
{
    public function index(Request $request)
    {
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
        $clip->update(['status' => 'approved']);

        // Notify the user
        $clip->user->notify(new VideoApprovedNotification($clip));

        return back()->with('success', 'Video has been approved and user notified.');
    }

    public function reject(HighlightClip $clip)
    {
        $reason = request('reason'); // Optional rejection reason
        $clip->update(['status' => 'rejected']);

        // Notify the user
        $clip->user->notify(new VideoRejectedNotification($clip, $reason));

        return back()->with('success', 'Video has been rejected and user notified.');
    }

    public function feature(HighlightClip $clip)
    {
        $clip->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);

        return back()->with('success', 'Video has been featured.');
    }

    public function unfeature(HighlightClip $clip)
    {
        $clip->update([
            'is_featured' => false,
            'featured_at' => null,
        ]);

        return back()->with('success', 'Video removed from featured.');
    }

    public function destroy(HighlightClip $clip)
    {
        $clip->delete();

        return redirect()->route('admin.clips.index')->with('success', 'Video deleted.');
    }
}
