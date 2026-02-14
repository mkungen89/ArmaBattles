<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\ContentCreator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreatorDashboardController extends Controller
{
    /**
     * Show creator dashboard
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isContentCreator()) {
            return redirect()->route('content-creators.create')
                ->with('info', 'You need to register as a content creator first.');
        }

        $creator = $user->contentCreator;

        // Calculate stats
        $stats = [
            'followers' => $creator->follower_count ?? 0,
            'live_viewers' => $creator->live_viewers ?? 0,
            'is_live' => $creator->is_live,
            'is_verified' => $creator->is_verified,
            'is_approved' => $creator->is_approved,
            'is_featured' => $creator->is_featured,
            'last_live' => $creator->last_live_at,
            'total_views' => $creator->viewer_count ?? 0,
        ];

        // Get recent activity (mock data for now - can be expanded)
        $recentActivity = collect([]);

        return view('creator.dashboard', compact('creator', 'stats', 'recentActivity'));
    }

    /**
     * Show creator profile edit form
     */
    public function edit()
    {
        $user = Auth::user();

        if (!$user->isContentCreator()) {
            return redirect()->route('content-creators.create');
        }

        $creator = $user->contentCreator;

        return view('creator.edit', compact('creator'));
    }

    /**
     * Update creator profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user->isContentCreator()) {
            return redirect()->route('content-creators.create');
        }

        $creator = $user->contentCreator;

        $validated = $request->validate([
            'channel_name' => 'required|string|max:255',
            'channel_url' => 'required|url',
            'bio' => 'nullable|string|max:500',
            'follower_count' => 'nullable|integer|min:0',
        ]);

        $creator->update($validated);

        return redirect()->route('creator.dashboard')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show stats page
     */
    public function stats()
    {
        $user = Auth::user();

        if (!$user->isContentCreator()) {
            return redirect()->route('content-creators.create');
        }

        $creator = $user->contentCreator;

        // Calculate detailed stats
        $stats = [
            'total_followers' => $creator->follower_count ?? 0,
            'current_viewers' => $creator->live_viewers ?? 0,
            'total_views' => $creator->viewer_count ?? 0,
            'is_live' => $creator->is_live,
            'live_duration' => $creator->is_live && $creator->live_started_at
                ? $creator->live_started_at->diffForHumans(null, true)
                : null,
            'last_live' => $creator->last_live_at,
            'total_streams' => 0, // Can be implemented later with stream history
            'avg_viewers' => 0, // Can be calculated from stream history
        ];

        // Platform-specific stats
        $platformStats = [
            'platform' => $creator->platform,
            'platform_name' => $creator->platform_name,
            'verified' => $creator->is_verified,
            'featured' => $creator->is_featured,
        ];

        return view('creator.stats', compact('creator', 'stats', 'platformStats'));
    }

    /**
     * Check live status manually
     */
    public function checkLiveStatus()
    {
        $user = Auth::user();

        if (!$user->isContentCreator()) {
            return redirect()->route('content-creators.create');
        }

        \Artisan::call('creators:check-live');

        return back()->with('success', 'Live status updated!');
    }

    /**
     * Admin view: Show any creator's dashboard
     */
    public function adminView(ContentCreator $creator)
    {
        // Calculate stats
        $stats = [
            'followers' => $creator->follower_count ?? 0,
            'live_viewers' => $creator->live_viewers ?? 0,
            'is_live' => $creator->is_live,
            'is_verified' => $creator->is_verified,
            'is_approved' => $creator->is_approved,
            'is_featured' => $creator->is_featured,
            'last_live' => $creator->last_live_at,
            'total_views' => $creator->viewer_count ?? 0,
        ];

        // Get recent activity
        $recentActivity = collect([]);

        return view('creator.dashboard', compact('creator', 'stats', 'recentActivity'));
    }

    /**
     * Admin view: Show any creator's stats
     */
    public function adminStats(ContentCreator $creator)
    {
        // Calculate detailed stats
        $stats = [
            'total_followers' => $creator->follower_count ?? 0,
            'current_viewers' => $creator->live_viewers ?? 0,
            'total_views' => $creator->viewer_count ?? 0,
            'is_live' => $creator->is_live,
            'live_duration' => $creator->is_live && $creator->live_started_at
                ? $creator->live_started_at->diffForHumans(null, true)
                : null,
            'last_live' => $creator->last_live_at,
            'total_streams' => 0,
            'avg_viewers' => 0,
        ];

        // Platform-specific stats
        $platformStats = [
            'platform' => $creator->platform,
            'platform_name' => $creator->platform_name,
            'verified' => $creator->is_verified,
            'featured' => $creator->is_featured,
        ];

        return view('creator.stats', compact('creator', 'stats', 'platformStats'));
    }
}
