<?php

namespace App\Http\Controllers;

use App\Models\ContentCreator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->isContentCreator()) {
            return back()->with('error', 'You are already registered as a content creator.');
        }

        $validated = $request->validate([
            'platform' => 'required|in:twitch,youtube,tiktok,kick',
            'channel_url' => 'required|url|max:255',
            'channel_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
        ]);

        ContentCreator::create([
            'user_id' => $user->id,
            'platform' => $validated['platform'],
            'channel_url' => $validated['channel_url'],
            'channel_name' => $validated['channel_name'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        return redirect()->route('content-creators.index')
            ->with('success', 'Content creator profile created! An admin will review your application for verification.');
    }

    /**
     * Show content creator profile
     */
    public function show(ContentCreator $contentCreator)
    {
        $contentCreator->load('user', 'user.highlightClips');

        $clips = $contentCreator->user->highlightClips()
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

        if ($contentCreator->user_id !== $user->id && !$user->isAdmin()) {
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

        if ($contentCreator->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'You cannot edit this content creator profile.');
        }

        $validated = $request->validate([
            'channel_url' => 'required|url|max:255',
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

        if ($contentCreator->user_id !== $user->id && !$user->isAdmin()) {
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
        if (!Auth::user()->isAdmin()) {
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
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $contentCreator->update([
            'is_verified' => false,
            'verified_at' => null,
        ]);

        return back()->with('success', 'Content creator verification removed.');
    }
}
