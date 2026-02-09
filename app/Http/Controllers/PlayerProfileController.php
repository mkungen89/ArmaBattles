<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementShowcase;
use App\Models\PlayerAchievement;
use App\Models\PlayerDistance;
use App\Models\PlayerStat;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlayerProfileController extends Controller
{
    public function show(User $user)
    {
        if ($user->profile_visibility === 'private' && (!auth()->check() || auth()->id() !== $user->id)) {
            abort(403, 'This profile is private.');
        }

        $team = $user->activeTeam;

        $stats = [
            'tournaments_played' => 0,
            'matches_played' => 0,
            'wins' => 0,
            'losses' => 0,
            'win_rate' => 0,
        ];

        if ($team) {
            $team->load(['activeMembers', 'captain']);
            $stats['tournaments_played'] = $team->tournaments()->count();
            $stats['matches_played'] = TournamentMatch::where(function ($q) use ($team) {
                    $q->where('team1_id', $team->id)->orWhere('team2_id', $team->id);
                })->where('status', 'completed')->count();
            $stats['wins'] = TournamentMatch::where('winner_id', $team->id)->count();
            $stats['losses'] = $stats['matches_played'] - $stats['wins'];
            $stats['win_rate'] = $stats['matches_played'] > 0
                ? round(($stats['wins'] / $stats['matches_played']) * 100) : 0;
        }

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

        $gameStats = $user->gameStats();
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

        // Vehicle stats
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

        return view('profile.public', [
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
            'isOwner' => auth()->check() && auth()->id() === $user->id,
        ]);
    }

    private function getVehicleStats(?string $uuid): array
    {
        if (!$uuid) {
            return ['totalWalkingDistance' => 0, 'totalVehicleDistance' => 0, 'totalWalkingTime' => 0, 'totalVehicleTime' => 0, 'topVehicles' => collect(), 'vehicleImages' => collect()];
        }

        $distances = PlayerDistance::where('player_uuid', $uuid)->get();

        $totalWalkingDistance = $distances->sum('walking_distance');
        $totalVehicleDistance = $distances->sum('total_vehicle_distance');
        $totalWalkingTime = $distances->sum('walking_time_seconds');
        $totalVehicleTime = $distances->sum('total_vehicle_time_seconds');

        $vehicleAgg = [];
        foreach ($distances as $row) {
            if (!is_array($row->vehicles)) {
                continue;
            }
            foreach ($row->vehicles as $v) {
                $name = $v['vehicle'] ?? $v['name'] ?? null;
                if (!$name) {
                    continue;
                }
                if (!isset($vehicleAgg[$name])) {
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
}
