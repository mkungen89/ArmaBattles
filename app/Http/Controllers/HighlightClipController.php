<?php

namespace App\Http\Controllers;

use App\Models\ClipVote;
use App\Models\HighlightClip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HighlightClipController extends Controller
{
    /**
     * Display highlight clips gallery
     */
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'popular'); // popular, recent, featured
        $platform = $request->query('platform');

        $query = HighlightClip::with('user');

        // Filter by platform
        if ($platform && in_array($platform, ['twitch', 'youtube', 'tiktok', 'kick'])) {
            $query->platform($platform);
        }

        // Sort
        if ($sort === 'recent') {
            $query->recent();
        } elseif ($sort === 'featured') {
            $query->featured()->orderByDesc('featured_at');
        } else {
            $query->popular();
        }

        $clips = $query->paginate(12);

        // Get clip of the week (most votes in last 7 days)
        $clipOfTheWeek = HighlightClip::with('user')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('votes')
            ->first();

        $stats = [
            'total' => HighlightClip::count(),
            'this_week' => HighlightClip::where('created_at', '>=', now()->subWeek())->count(),
            'featured' => HighlightClip::where('is_featured', true)->count(),
        ];

        return view('clips.index', compact('clips', 'clipOfTheWeek', 'stats', 'sort', 'platform'));
    }

    /**
     * Show clip submission form
     */
    public function create()
    {
        return view('clips.create');
    }

    /**
     * Store new highlight clip
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'platform' => 'required|in:youtube,twitch,tiktok,kick',
            'description' => 'nullable|string|max:1000',
        ]);

        HighlightClip::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'url' => $validated['url'],
            'platform' => $validated['platform'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('clips.index')
            ->with('success', 'Highlight clip submitted!');
    }

    /**
     * Show clip details
     */
    public function show(HighlightClip $clip)
    {
        $clip->load('user', 'clipVotes.user');

        $userHasVoted = Auth::check() && $clip->hasUserVoted(Auth::id());

        return view('clips.show', compact('clip', 'userHasVoted'));
    }

    /**
     * Vote for a clip
     */
    public function vote(HighlightClip $clip, Request $request)
    {
        $user = Auth::user();

        // Check if clip is approved
        if ($clip->status !== 'approved') {
            abort(403, 'Cannot vote on pending or rejected clips.');
        }

        $validated = $request->validate([
            'vote_type' => 'required|in:upvote,downvote',
        ]);

        DB::transaction(function () use ($clip, $user, $validated) {
            // Update or create vote
            ClipVote::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'clip_id' => $clip->id,
                ],
                [
                    'vote_type' => $validated['vote_type'],
                ]
            );

            $clip->recalculateVotes();
        });

        return back()->with('success', 'Vote recorded!');
    }

    /**
     * Remove vote from a clip
     */
    public function unvote(HighlightClip $clip)
    {
        $user = Auth::user();

        $vote = ClipVote::where('user_id', $user->id)
            ->where('clip_id', $clip->id)
            ->first();

        if (! $vote) {
            return back()->with('error', 'You have not voted for this clip.');
        }

        DB::transaction(function () use ($vote, $clip) {
            $vote->delete();
            $clip->decrementVotes();
        });

        return back()->with('success', 'Vote removed.');
    }

    /**
     * Feature a clip (admin only)
     */
    public function feature(HighlightClip $clip)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $clip->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);

        return back()->with('success', 'Clip featured!');
    }

    /**
     * Unfeature a clip (admin only)
     */
    public function unfeature(HighlightClip $clip)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $clip->update([
            'is_featured' => false,
            'featured_at' => null,
        ]);

        return back()->with('success', 'Clip unfeatured.');
    }

    /**
     * Delete a clip
     */
    public function destroy(HighlightClip $clip)
    {
        $user = Auth::user();

        if ($clip->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'You cannot delete this clip.');
        }

        $clip->delete();

        return redirect()->route('clips.index')
            ->with('success', 'Clip deleted.');
    }
}
