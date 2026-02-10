<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementShowcase;
use App\Models\PlayerAchievement;
use App\Models\PlayerDistance;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $team = $user->activeTeam;

        // Calculate player statistics
        $stats = [
            'tournaments_played' => 0,
            'matches_played' => 0,
            'wins' => 0,
            'losses' => 0,
            'win_rate' => 0,
        ];

        if ($team) {
            $team->load(['activeMembers', 'captain']);

            // Count tournaments
            $stats['tournaments_played'] = $team->tournaments()->count();

            // Count matches
            $stats['matches_played'] = TournamentMatch::where(function ($q) use ($team) {
                $q->where('team1_id', $team->id)->orWhere('team2_id', $team->id);
            })
                ->where('status', 'completed')
                ->count();

            $stats['wins'] = TournamentMatch::where('winner_id', $team->id)->count();
            $stats['losses'] = $stats['matches_played'] - $stats['wins'];
            $stats['win_rate'] = $stats['matches_played'] > 0
                ? round(($stats['wins'] / $stats['matches_played']) * 100)
                : 0;
        }

        // Get recent matches
        $recentMatches = collect();
        if ($team) {
            $recentMatches = TournamentMatch::where(function ($q) use ($team) {
                $q->where('team1_id', $team->id)->orWhere('team2_id', $team->id);
            })
                ->where('status', 'completed')
                ->with(['tournament', 'team1', 'team2', 'winner'])
                ->orderByDesc('completed_at')
                ->limit(5)
                ->get();
        }

        // Get game stats if user has linked their Arma ID
        $gameStats = $user->gameStats();

        // Additional game data (requires linked Arma ID)
        $topWeapons = collect();
        $recentKillEvents = collect();
        $hitZonesDealt = collect();
        $hitZonesReceived = collect();
        $friendlyFireDealt = 0;
        $friendlyFireReceived = 0;
        $xpByType = collect();
        $killsByVictimType = collect();
        $weaponImages = collect();

        if ($gameStats && $user->player_uuid) {
            $uuid = $user->player_uuid;

            $topWeapons = DB::table('player_kills')
                ->where('killer_uuid', $uuid)
                ->selectRaw('weapon_name, COUNT(*) as total')
                ->groupBy('weapon_name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $recentKillEvents = DB::table('player_kills')
                ->where('killer_uuid', $uuid)
                ->orderByDesc('killed_at')
                ->limit(25)
                ->get();

            $killsByVictimType = DB::table('player_kills')
                ->where('killer_uuid', $uuid)
                ->selectRaw('COALESCE(victim_type, \'UNKNOWN\') as victim_type, COUNT(*) as total')
                ->groupBy('victim_type')
                ->get();

            $hitZonesDealt = DB::table('damage_events')
                ->where('killer_uuid', $uuid)
                ->selectRaw('hit_zone_name, COUNT(*) as count, SUM(damage_amount) as total_damage')
                ->groupBy('hit_zone_name')
                ->get();

            $hitZonesReceived = DB::table('damage_events')
                ->where('victim_uuid', $uuid)
                ->selectRaw('hit_zone_name, COUNT(*) as count, SUM(damage_amount) as total_damage')
                ->groupBy('hit_zone_name')
                ->get();

            $friendlyFireDealt = DB::table('damage_events')
                ->where('killer_uuid', $uuid)->where('is_friendly_fire', true)->count();
            $friendlyFireReceived = DB::table('damage_events')
                ->where('victim_uuid', $uuid)->where('is_friendly_fire', true)->count();

            $xpByType = DB::table('xp_events')
                ->where('player_uuid', $uuid)
                ->selectRaw('reward_type, COUNT(*) as count, SUM(xp_amount) as total_xp')
                ->groupBy('reward_type')
                ->orderByDesc('total_xp')
                ->get();

            $weaponImages = DB::table('weapons')
                ->whereNotNull('image_path')
                ->pluck('image_path', 'name');
        }

        // Vehicle stats from player_distance
        $vehicleStats = $this->getVehicleStats($user->player_uuid);

        // Achievements
        $achievements = Achievement::orderBy('sort_order')->get();
        $playerAchievements = collect();
        if ($user->player_uuid) {
            $playerAchievements = PlayerAchievement::where('player_uuid', $user->player_uuid)
                ->get()
                ->keyBy('achievement_id');
        }

        // Achievement Showcase
        $showcaseAchievements = collect();
        if ($user->player_uuid) {
            $showcase = AchievementShowcase::where('player_uuid', $user->player_uuid)->first();
            if ($showcase) {
                $showcaseAchievements = $showcase->pinnedAchievements();
            }
        }

        // Reputation
        $reputation = $user->reputation()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_score' => 0,
                'positive_votes' => 0,
                'negative_votes' => 0,
                'teamwork_count' => 0,
                'leadership_count' => 0,
                'sportsmanship_count' => 0,
            ]
        );

        // Competitive rating
        $playerRating = $user->playerRating;

        return view('profile.show', [
            'user' => $user,
            'team' => $team,
            'stats' => $stats,
            'recentMatches' => $recentMatches,
            'gameStats' => $gameStats,
            'topWeapons' => $topWeapons,
            'recentKillEvents' => $recentKillEvents,
            'killsByVictimType' => $killsByVictimType,
            'hitZonesDealt' => $hitZonesDealt,
            'hitZonesReceived' => $hitZonesReceived,
            'friendlyFireDealt' => $friendlyFireDealt,
            'friendlyFireReceived' => $friendlyFireReceived,
            'xpByType' => $xpByType,
            'weaponImages' => $weaponImages,
            'vehicleStats' => $vehicleStats,
            'achievements' => $achievements,
            'playerAchievements' => $playerAchievements,
            'showcaseAchievements' => $showcaseAchievements,
            'reputation' => $reputation,
            'playerRating' => $playerRating,
        ]);
    }

    /**
     * Aggregate vehicle stats from player_distance records
     */
    private function getVehicleStats(?string $uuid): array
    {
        if (! $uuid) {
            return ['totalWalkingDistance' => 0, 'totalVehicleDistance' => 0, 'totalWalkingTime' => 0, 'totalVehicleTime' => 0, 'topVehicles' => collect(), 'vehicleImages' => collect()];
        }

        $distances = PlayerDistance::where('player_uuid', $uuid)->get();

        $totalWalkingDistance = $distances->sum('walking_distance');
        $totalVehicleDistance = $distances->sum('total_vehicle_distance');
        $totalWalkingTime = $distances->sum('walking_time_seconds');
        $totalVehicleTime = $distances->sum('total_vehicle_time_seconds');

        // Aggregate per-vehicle data from the JSON column
        $vehicleAgg = [];
        foreach ($distances as $row) {
            if (! is_array($row->vehicles)) {
                continue;
            }
            foreach ($row->vehicles as $v) {
                $name = $v['vehicle'] ?? $v['name'] ?? null;
                if (! $name) {
                    continue;
                }
                if (! isset($vehicleAgg[$name])) {
                    $vehicleAgg[$name] = ['name' => $name, 'distance' => 0, 'time' => 0, 'count' => 0];
                }
                $vehicleAgg[$name]['distance'] += floatval($v['distance'] ?? 0);
                $vehicleAgg[$name]['time'] += floatval($v['timeSeconds'] ?? $v['time_seconds'] ?? 0);
                $vehicleAgg[$name]['count']++;
            }
        }

        $topVehicles = collect($vehicleAgg)->sortByDesc('distance')->take(8)->values();

        $vehicleImages = DB::table('vehicles')
            ->whereNotNull('image_path')
            ->pluck('image_path', 'name');

        return compact('totalWalkingDistance', 'totalVehicleDistance', 'totalWalkingTime', 'totalVehicleTime', 'topVehicles', 'vehicleImages');
    }

    /**
     * Show the settings page where users can link their Arma ID
     */
    public function settings()
    {
        $user = Auth::user();
        $gameStats = $user->gameStats();

        return view('profile.settings', [
            'user' => $user,
            'gameStats' => $gameStats,
        ]);
    }

    /**
     * Link Arma UUID to user profile
     */
    public function linkArmaId(Request $request)
    {
        $validated = $request->validate([
            'player_uuid' => [
                'required',
                'string',
                'regex:/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',
                'unique:users,player_uuid,'.Auth::id(),
            ],
        ], [
            'player_uuid.required' => 'Please enter your Arma Reforger ID.',
            'player_uuid.regex' => 'Invalid ID format. It should look like: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            'player_uuid.unique' => 'This Arma ID is already linked to another account.',
        ]);

        $user = Auth::user();
        $user->update(['player_uuid' => strtolower($validated['player_uuid'])]);

        return back()->with('success', 'Your Arma Reforger ID has been linked successfully!');
    }

    /**
     * Unlink Arma UUID from user profile
     */
    public function unlinkArmaId()
    {
        $user = Auth::user();
        $user->update(['player_uuid' => null]);

        return back()->with('success', 'Your Arma Reforger ID has been unlinked.');
    }

    /**
     * Link Discord to user profile
     */
    public function linkDiscord(Request $request)
    {
        $validated = $request->validate([
            'discord_username' => [
                'required',
                'string',
                'min:2',
                'max:32',
                'regex:/^[a-z0-9_.]+$/i',
            ],
            'discord_id' => [
                'nullable',
                'string',
                'regex:/^\d{17,19}$/',
            ],
        ], [
            'discord_username.required' => 'Please enter your Discord username.',
            'discord_username.regex' => 'Invalid username format. Use only letters, numbers, underscores, and periods.',
            'discord_username.min' => 'Discord username must be at least 2 characters.',
            'discord_username.max' => 'Discord username cannot exceed 32 characters.',
            'discord_id.regex' => 'Invalid Discord ID format. It should be a 17-19 digit number.',
        ]);

        $user = Auth::user();
        $user->update([
            'discord_username' => strtolower($validated['discord_username']),
            'discord_id' => $validated['discord_id'] ?? null,
        ]);

        return back()->with('success', 'Your Discord has been linked successfully!');
    }

    /**
     * Unlink Discord from user profile
     */
    public function unlinkDiscord()
    {
        $user = Auth::user();
        $user->update([
            'discord_username' => null,
            'discord_id' => null,
        ]);

        return back()->with('success', 'Your Discord has been unlinked.');
    }

    /**
     * Update social media links
     */
    public function updateSocialLinks(Request $request)
    {
        $platforms = ['twitch', 'youtube', 'tiktok', 'kick', 'twitter', 'facebook', 'instagram'];

        $rules = [];
        foreach ($platforms as $platform) {
            $rules[$platform] = ['nullable', 'url', 'max:255'];
        }

        $validated = $request->validate($rules);

        $socialLinks = [];
        foreach ($platforms as $platform) {
            $value = trim($validated[$platform] ?? '');
            if ($value !== '') {
                $socialLinks[$platform] = $value;
            }
        }

        $user = Auth::user();
        $user->update([
            'social_links' => ! empty($socialLinks) ? $socialLinks : null,
        ]);

        return back()->with('success', 'Social media links updated successfully!');
    }

    /**
     * Upload a custom avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:512',
        ]);

        $user = Auth::user();

        // Delete old custom avatar if it exists
        if ($user->custom_avatar && Storage::disk('s3')->exists($user->custom_avatar)) {
            Storage::disk('s3')->delete($user->custom_avatar);
        }

        $path = $request->file('avatar')->store('avatars', 's3');
        $user->update(['custom_avatar' => $path]);

        return back()->with('success', 'Avatar uploaded successfully!');
    }

    /**
     * Remove custom avatar (revert to Steam avatar)
     */
    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->custom_avatar && Storage::disk('s3')->exists($user->custom_avatar)) {
            Storage::disk('s3')->delete($user->custom_avatar);
        }

        $user->update(['custom_avatar' => null]);

        return back()->with('success', 'Custom avatar removed. Your Steam avatar will be used.');
    }

    /**
     * Update profile privacy and notification settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'profile_visibility' => 'required|in:public,private',
            'notify_link_arma_id' => 'nullable|boolean',
            'notify_tournament_updates' => 'nullable|boolean',
            'notify_team_invites' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $user->update([
            'profile_visibility' => $validated['profile_visibility'],
            'notification_preferences' => [
                'link_arma_id' => $request->boolean('notify_link_arma_id'),
                'tournament_updates' => $request->boolean('notify_tournament_updates'),
                'team_invites' => $request->boolean('notify_team_invites'),
            ],
        ]);

        return back()->with('success', 'Settings updated successfully!');
    }
}
