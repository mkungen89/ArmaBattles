<?php

namespace App\Http\Controllers;

use App\Models\ContentCreator;
use App\Models\HighlightClip;
use App\Services\ContentCreatorInfoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContentCreatorController extends Controller
{
    /**
     * Display content creator directory
     */
    public function index(Request $request)
    {
        $platform = $request->query('platform');
        $filter = $request->query('filter', 'all'); // all, live, verified

        $query = ContentCreator::with('user');

        // Filter by platform
        if ($platform && in_array($platform, ['twitch', 'youtube', 'tiktok', 'kick'])) {
            $query->platform($platform);
        }

        // Filter by status
        if ($filter === 'live') {
            $query->live();
        } elseif ($filter === 'verified') {
            $query->verified();
        }

        $creators = $query->orderByDesc('is_live')
            ->orderByDesc('is_verified')
            ->orderByDesc('follower_count')
            ->paginate(12);

        $stats = [
            'total' => ContentCreator::count(),
            'live' => ContentCreator::where('is_live', true)->count(),
            'verified' => ContentCreator::where('is_verified', true)->count(),
        ];

        return view('content-creators.index', compact('creators', 'stats', 'platform', 'filter'));
    }

    /**
     * Show registration form
     */
    public function create()
    {
        $user = Auth::user();

        if ($user->isContentCreator()) {
            return redirect()->route('content-creators.show', $user->contentCreator)
                ->with('info', 'You are already registered as a content creator.');
        }

        return view('content-creators.create');
    }

    /**
     * Store content creator registration
     */
    public function store(Request $request, ContentCreatorInfoService $infoService)
    {
        $user = Auth::user();

        if ($user->isContentCreator()) {
            return back()->with('error', 'You are already registered as a content creator.');
        }

        $validated = $request->validate([
            'platform' => 'required|in:twitch,youtube,tiktok,kick',
            'channel_url' => 'required|url|max:255|unique:content_creators,channel_url',
            'channel_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
        ]);

        // Fetch channel info from platform API if not provided
        $channelName = $validated['channel_name'] ?? null;
        $bio = $validated['bio'] ?? null;
        $followerCount = null;

        if (!$channelName || !$bio) {
            $channelInfo = $infoService->fetchChannelInfo($validated['platform'], $validated['channel_url']);

            if ($channelInfo) {
                $channelName = $channelName ?? $channelInfo['channel_name'];
                $bio = $bio ?? $channelInfo['bio'];
                $followerCount = $channelInfo['follower_count'] ?? null;

                Log::info("Auto-fetched channel info for {$validated['platform']}: " . json_encode($channelInfo));
            } else {
                Log::warning("Could not auto-fetch channel info for {$validated['platform']}: {$validated['channel_url']}");
            }
        }

        try {
            ContentCreator::create([
                'user_id' => $user->id,
                'platform' => $validated['platform'],
                'channel_url' => $validated['channel_url'],
                'channel_name' => $channelName,
                'bio' => $bio,
                'follower_count' => $followerCount,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle race condition - unique constraint on user_id
            if ($e->getCode() === '23505' || str_contains($e->getMessage(), 'unique')) {
                return back()->with('error', 'You are already registered as a content creator.');
            }
            throw $e;
        }

        $message = 'Content creator profile created! An admin will review your application for verification.';
        if ($channelName) {
            $message = "Welcome, {$channelName}! Your content creator profile has been created. An admin will review your application for verification.";
        }

        return redirect()->route('content-creators.index')
            ->with('success', $message);
    }

    /**
     * Show content creator profile
     */
    public function show(ContentCreator $contentCreator)
    {
        $contentCreator->load('user');

        // Fetch top clips separately (no wasted eager loading)
        $clips = HighlightClip::where('user_id', $contentCreator->user_id)
            ->where('status', 'approved')
            ->orderByDesc('votes')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('content-creators.show', compact('contentCreator', 'clips'));
    }

    /**
     * Show edit form
     */
    public function edit(ContentCreator $contentCreator)
    {
        $user = Auth::user();

        if ($contentCreator->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'You cannot edit this content creator profile.');
        }

        return view('content-creators.edit', compact('contentCreator'));
    }

    /**
     * Update content creator profile
     */
    public function update(Request $request, ContentCreator $contentCreator)
    {
        $user = Auth::user();

        if ($contentCreator->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'You cannot edit this content creator profile.');
        }

        $validated = $request->validate([
            'channel_url' => 'required|url|max:255|unique:content_creators,channel_url,' . $contentCreator->id,
            'channel_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
        ]);

        $contentCreator->update($validated);

        return back()->with('success', 'Content creator profile updated!');
    }

    /**
     * Delete content creator profile
     */
    public function destroy(ContentCreator $contentCreator)
    {
        $user = Auth::user();

        if ($contentCreator->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'You cannot delete this content creator profile.');
        }

        $contentCreator->delete();

        return redirect()->route('content-creators.index')
            ->with('success', 'Content creator profile deleted.');
    }

    /**
     * Verify content creator (admin only)
     */
    public function verify(ContentCreator $contentCreator)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $contentCreator->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Content creator verified!');
    }

    /**
     * Unverify content creator (admin only)
     */
    public function unverify(ContentCreator $contentCreator)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $contentCreator->update([
            'is_verified' => false,
            'verified_at' => null,
        ]);

        return back()->with('success', 'Content creator verification removed.');
    }

    /**
     * Get live creators for AJAX polling
     */
    public function liveStatus()
    {
        $liveCreators = ContentCreator::with('user')
            ->live()
            ->orderByDesc('live_viewers')
            ->get()
            ->map(function ($creator) {
                return [
                    'id' => $creator->id,
                    'channel_name' => $creator->channel_name,
                    'avatar' => $creator->user->avatar_display,
                    'url' => route('content-creators.show', $creator),
                    'platform' => $creator->platform,
                    'platform_name' => $creator->platform_name,
                    'platform_color' => $creator->platform_color,
                    'is_verified' => $creator->is_verified,
                    'live_platform' => $creator->live_platform,
                    'live_title' => $creator->live_title,
                    'live_viewers' => $creator->live_viewers ?? 0,
                    'live_started_at' => $creator->live_started_at?->diffForHumans(null, true),
                    'bio' => $creator->bio,
                ];
            });

        return response()->json([
            'count' => $liveCreators->count(),
            'creators' => $liveCreators->take(6),
            'total' => ContentCreator::where('is_live', true)->count(),
        ]);
    }
}
