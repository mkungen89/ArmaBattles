<?php

namespace App\Http\Controllers;

use App\Services\DiscordPresenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscordPresenceController extends Controller
{
    public function __construct(
        private DiscordPresenceService $presenceService
    ) {}

    /**
     * Show Discord presence settings
     */
    public function settings()
    {
        $user = Auth::user();
        $presence = $user->discordPresence;

        return view('discord.settings', compact('user', 'presence'));
    }

    /**
     * Enable Discord presence for the logged-in user
     */
    public function enable(Request $request)
    {
        $request->validate([
            'discord_user_id' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        $this->presenceService->enablePresence(
            $user,
            $request->discord_user_id
        );

        return back()->with('success', 'Discord Rich Presence enabled successfully!');
    }

    /**
     * Disable Discord presence for the logged-in user
     */
    public function disable()
    {
        $this->presenceService->disablePresence(Auth::user());

        return back()->with('success', 'Discord Rich Presence disabled.');
    }

    /**
     * Get current presence data for the logged-in user (API endpoint)
     */
    public function current(Request $request)
    {
        $user = Auth::user();
        $presence = $user->discordPresence;

        if (! $presence || ! $presence->enabled) {
            return response()->json([
                'enabled' => false,
                'message' => 'Discord Rich Presence is not enabled',
            ]);
        }

        return response()->json([
            'enabled' => true,
            'presence' => $this->presenceService->getDiscordPayload($presence),
            'current_activity' => $presence->current_activity,
            'started_at' => $presence->started_at?->toIso8601String(),
            'elapsed_time' => $presence->getElapsedTime(),
        ]);
    }

    /**
     * Update presence based on user activity
     */
    public function updateActivity(Request $request)
    {
        $request->validate([
            'activity' => 'required|in:playing,watching_tournament,browsing,clear',
            'server_id' => 'nullable|exists:servers,id',
            'tournament_id' => 'nullable|exists:tournaments,id',
            'details' => 'nullable|array',
        ]);

        $user = Auth::user();

        switch ($request->activity) {
            case 'playing':
                if (! $request->server_id) {
                    return response()->json(['error' => 'Server ID required for playing activity'], 422);
                }
                $server = \App\Models\Server::findOrFail($request->server_id);
                $this->presenceService->updatePlayingPresence($user, $server, $request->details ?? []);
                break;

            case 'watching_tournament':
                if (! $request->tournament_id) {
                    return response()->json(['error' => 'Tournament ID required for watching activity'], 422);
                }
                $tournament = \App\Models\Tournament::findOrFail($request->tournament_id);
                $this->presenceService->updateWatchingPresence($user, $tournament, $request->details ?? []);
                break;

            case 'browsing':
                $this->presenceService->updateBrowsingPresence($user, $request->details ?? []);
                break;

            case 'clear':
                $this->presenceService->clearPresence($user);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Presence updated successfully',
        ]);
    }

    /**
     * Get all active presences (public API for Discord bot)
     */
    public function active()
    {
        $presences = $this->presenceService->getActivePresences();

        return response()->json([
            'count' => $presences->count(),
            'presences' => $presences->map(function ($presence) {
                return [
                    'user_id' => $presence->user_id,
                    'discord_user_id' => $presence->discord_user_id,
                    'username' => $presence->user->name,
                    'activity' => $presence->current_activity,
                    'status' => $presence->getActivityStatus(),
                    'state' => $presence->getActivityState(),
                    'started_at' => $presence->started_at?->toIso8601String(),
                    'payload' => $this->presenceService->getDiscordPayload($presence),
                ];
            }),
        ]);
    }
}
