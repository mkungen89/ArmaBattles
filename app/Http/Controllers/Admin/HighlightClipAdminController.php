<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HighlightClip;
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

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured === 'yes');
        }

        $clips = $query->orderByDesc('created_at')->paginate(25);

        $stats = [
            'total' => HighlightClip::count(),
            'featured' => HighlightClip::where('is_featured', true)->count(),
            'total_votes' => HighlightClip::sum('votes'),
        ];

        return view('admin.clips.index', compact('clips', 'stats'));
    }

    public function approve(HighlightClip $clip)
    {
        $clip->update(['status' => 'approved']);

        return back()->with('success', 'Clip has been approved.');
    }

    public function reject(HighlightClip $clip)
    {
        $clip->update(['status' => 'rejected']);

        return back()->with('success', 'Clip has been rejected.');
    }

    public function feature(HighlightClip $clip)
    {
        $clip->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);

        return back()->with('success', 'Clip has been featured.');
    }

    public function unfeature(HighlightClip $clip)
    {
        $clip->update([
            'is_featured' => false,
            'featured_at' => null,
        ]);

        return back()->with('success', 'Clip removed from featured.');
    }

    public function destroy(HighlightClip $clip)
    {
        $clip->delete();

        return redirect()->route('admin.clips.index')->with('success', 'Clip deleted.');
    }
}
