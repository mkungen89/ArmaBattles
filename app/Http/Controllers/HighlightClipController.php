<?php

namespace App\Http\Controllers;

use App\Models\ClipVote;
use App\Models\HighlightClip;
use App\Notifications\VideoSubmittedNotification;
use App\Services\VideoMetadataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HighlightClipController extends Controller
{
    /**
     * Display videos gallery
     */
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'popular'); // popular, recent, featured
        $platform = $request->query('platform');

        $query = HighlightClip::with('user')->where('status', 'approved');

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

        // Get clip of the week (most votes in last 7 days, approved only) - cached for 1 hour
        $clipOfTheWeek = \Cache::remember('clip_of_the_week', 3600, function () {
            return HighlightClip::with('user')
                ->where('status', 'approved')
                ->where('created_at', '>=', now()->subDays(7))
                ->orderByDesc('votes')
                ->first();
        });

        $stats = [
            'total' => HighlightClip::where('status', 'approved')->count(),
            'this_week' => HighlightClip::where('status', 'approved')->where('created_at', '>=', now()->subWeek())->count(),
            'featured' => HighlightClip::where('status', 'approved')->where('is_featured', true)->count(),
        ];

        return view('clips.index', compact('clips', 'clipOfTheWeek', 'stats', 'sort', 'platform'));
    }

    /**
     * Show video submission form
     */
    public function create()
    {
        return view('clips.create');
    }

    /**
     * Fetch video metadata from URL (AJAX endpoint)
     */
    public function fetchMetadata(Request $request, VideoMetadataService $metadataService)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $metadata = $metadataService->fetchMetadata($request->url);

        if (!$metadata) {
            return response()->json([
                'success' => false,
                'message' => 'Could not fetch video metadata. Please enter details manually.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $metadata,
        ]);
    }

    /**
     * Store new video
     */
    public function store(Request $request)
    {
        $maxDuration = (int) site_setting('clip_max_duration_seconds', 120);

        $validated = $request->validate([
            'url' => 'required|url|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'platform' => 'nullable|in:youtube,twitch,tiktok,kick',
            'author' => 'nullable|string|max:255',
            'thumbnail_url' => 'nullable|url|max:500',
            'duration_seconds' => "nullable|integer|max:{$maxDuration}",
        ], [
            'duration_seconds.max' => "Video must be no longer than {$maxDuration} seconds (" . gmdate('i:s', $maxDuration) . ").",
        ]);

        // If metadata not provided, try to fetch it
        if (!isset($validated['title']) || !isset($validated['platform'])) {
            $metadataService = app(VideoMetadataService::class);
            $metadata = $metadataService->fetchMetadata($validated['url']);

            if ($metadata) {
                $validated['title'] = $validated['title'] ?? $metadata['title'];
                $validated['description'] = $validated['description'] ?? $metadata['description'];
                $validated['platform'] = $validated['platform'] ?? $metadata['platform'];
                $validated['author'] = $validated['author'] ?? $metadata['author'];
                $validated['thumbnail_url'] = $validated['thumbnail_url'] ?? $metadata['thumbnail_url'];
            }
        }

        // Platform is required at this point
        if (!isset($validated['platform'])) {
            return back()->withErrors(['url' => 'Could not detect video platform. Please try again.'])->withInput();
        }

        $clip = HighlightClip::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'] ?? 'Untitled Video',
            'url' => $validated['url'],
            'platform' => $validated['platform'],
            'author' => $validated['author'] ?? null,
            'description' => $validated['description'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'duration_seconds' => $validated['duration_seconds'] ?? null,
        ]);

        // Send notification to user
        Auth::user()->notify(new VideoSubmittedNotification($clip));

        return redirect()->route('clips.index')
            ->with('success', 'Video submitted successfully! You will be notified when it\'s reviewed.');
    }

    /**
     * Show video details
     */
    public function show(HighlightClip $clip)
    {
        $clip->load('user', 'clipVotes.user');

        $userHasVoted = Auth::check() && $clip->hasUserVoted(Auth::id());

        return view('clips.show', compact('clip', 'userHasVoted'));
    }

    /**
     * Vote for a video
     */
    public function vote(HighlightClip $clip, Request $request)
    {
        $user = Auth::user();

        // Check if video is approved
        if ($clip->status !== 'approved') {
            abort(403, 'Cannot vote on pending or rejected videos.');
        }

        $validated = $request->validate([
            'vote_type' => 'required|in:upvote,downvote',
        ]);

        DB::transaction(function () use ($clip, $user, $validated) {
            // Lock the clip row to prevent race conditions
            $clip = HighlightClip::where('id', $clip->id)->lockForUpdate()->first();

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

        // Invalidate clip of the week cache
        \Cache::forget('clip_of_the_week');

        return back()->with('success', 'Vote recorded!');
    }

    /**
     * Remove vote from a video
     */
    public function unvote(HighlightClip $clip)
    {
        $user = Auth::user();

        $vote = ClipVote::where('user_id', $user->id)
            ->where('clip_id', $clip->id)
            ->first();

        if (! $vote) {
            return back()->with('error', 'You have not voted for this video.');
        }

        DB::transaction(function () use ($vote, $clip) {
            $vote->delete();
            // Recalculate votes properly (handles both upvote and downvote removal)
            $clip->recalculateVotes();
        });

        // Invalidate clip of the week cache
        \Cache::forget('clip_of_the_week');

        return back()->with('success', 'Vote removed.');
    }

    /**
     * Feature a video (admin only)
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

        return back()->with('success', 'Video featured!');
    }

    /**
     * Unfeature a video (admin only)
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

        return back()->with('success', 'Video unfeatured.');
    }

    /**
     * Delete a video
     */
    public function destroy(HighlightClip $clip)
    {
        $user = Auth::user();

        if ($clip->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'You cannot delete this video.');
        }

        $clip->delete();

        return redirect()->route('clips.index')
            ->with('success', 'Video deleted.');
    }
}
