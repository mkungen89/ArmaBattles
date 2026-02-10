<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatsExportController extends Controller
{
    /**
     * Export player stats to CSV
     */
    public function exportPlayerStats(Request $request, string $uuid): StreamedResponse
    {
        $stats = DB::table('player_stats')
            ->where('player_uuid', $uuid)
            ->first();

        if (! $stats) {
            abort(404, 'Player not found');
        }

        $fileName = "player_stats_{$uuid}_".now()->format('Y-m-d').'.csv';

        return $this->generateCsv($fileName, function () use ($stats) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['Metric', 'Value']);

            // Basic stats
            fputcsv($handle, ['Player Name', $stats->player_name]);
            fputcsv($handle, ['Player UUID', $stats->player_uuid]);
            fputcsv($handle, ['Kills', $stats->kills]);
            fputcsv($handle, ['Deaths', $stats->deaths]);
            fputcsv($handle, ['K/D Ratio', $stats->deaths > 0 ? round($stats->kills / $stats->deaths, 2) : $stats->kills]);
            fputcsv($handle, ['Headshots', $stats->headshots]);
            fputcsv($handle, ['Headshot %', $stats->kills > 0 ? round(($stats->headshots / $stats->kills) * 100, 1).'%' : '0%']);
            fputcsv($handle, ['Team Kills', $stats->team_kills]);
            fputcsv($handle, ['Roadkills', $stats->total_roadkills]);

            // Distance & Time
            fputcsv($handle, ['Total Distance (m)', number_format($stats->total_distance, 2)]);
            fputcsv($handle, ['Playtime (hours)', round($stats->playtime_seconds / 3600, 1)]);

            // Combat
            fputcsv($handle, ['Shots Fired', $stats->shots_fired]);
            fputcsv($handle, ['Grenades Thrown', $stats->grenades_thrown]);
            fputcsv($handle, ['Total Hits', $stats->total_hits]);
            fputcsv($handle, ['Total Damage Dealt', number_format($stats->total_damage_dealt, 1)]);
            fputcsv($handle, ['Hits (Head)', $stats->hits_head]);
            fputcsv($handle, ['Hits (Torso)', $stats->hits_torso]);
            fputcsv($handle, ['Hits (Arms)', $stats->hits_arms]);
            fputcsv($handle, ['Hits (Legs)', $stats->hits_legs]);

            // Support
            fputcsv($handle, ['Heals Given', $stats->heals_given]);
            fputcsv($handle, ['Heals Received', $stats->heals_received]);
            fputcsv($handle, ['Supplies Delivered', $stats->supplies_delivered]);
            fputcsv($handle, ['Bases Captured', $stats->bases_captured]);

            // XP
            fputcsv($handle, ['Total XP', number_format($stats->xp_total)]);

            // Timestamps
            fputcsv($handle, ['Last Seen', $stats->last_seen]);
            fputcsv($handle, ['First Seen', $stats->created_at]);

            fclose($handle);
        });
    }

    /**
     * Export leaderboard to CSV
     */
    public function exportLeaderboard(Request $request, string $type): StreamedResponse
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:1000',
        ]);

        $limit = $validated['limit'] ?? 100;

        $query = DB::table('player_stats')
            ->select('player_name', 'player_uuid', 'kills', 'deaths', 'playtime_seconds', 'xp_total', 'total_distance', 'total_roadkills');

        // Order by type
        $query = match ($type) {
            'kills' => $query->orderByDesc('kills'),
            'deaths' => $query->orderByDesc('deaths'),
            'kd' => $query->whereRaw('deaths > 0')->orderByRaw('CAST(kills AS FLOAT) / CAST(deaths AS FLOAT) DESC'),
            'playtime' => $query->orderByDesc('playtime_seconds'),
            'xp' => $query->orderByDesc('xp_total'),
            'distance' => $query->orderByDesc('total_distance'),
            'roadkills' => $query->orderByDesc('total_roadkills'),
            default => abort(400, 'Invalid leaderboard type'),
        };

        $players = $query->limit($limit)->get();

        $fileName = "leaderboard_{$type}_".now()->format('Y-m-d').'.csv';

        return $this->generateCsv($fileName, function () use ($players) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['Rank', 'Player Name', 'UUID', 'Kills', 'Deaths', 'K/D', 'Playtime (h)', 'XP', 'Distance (m)', 'Roadkills']);

            // Data
            $rank = 1;
            foreach ($players as $player) {
                fputcsv($handle, [
                    $rank++,
                    $player->player_name,
                    $player->player_uuid,
                    $player->kills,
                    $player->deaths,
                    $player->deaths > 0 ? round($player->kills / $player->deaths, 2) : $player->kills,
                    round($player->playtime_seconds / 3600, 1),
                    $player->xp_total,
                    round($player->total_distance, 2),
                    $player->total_roadkills,
                ]);
            }

            fclose($handle);
        });
    }

    /**
     * Export leaderboard to JSON
     */
    public function exportLeaderboardJson(Request $request, string $type)
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:1000',
        ]);

        $limit = $validated['limit'] ?? 100;

        $query = DB::table('player_stats')
            ->select('player_name', 'player_uuid', 'kills', 'deaths', 'playtime_seconds', 'xp_total', 'total_distance', 'total_roadkills', 'headshots', 'shots_fired', 'grenades_thrown');

        // Order by type
        $query = match ($type) {
            'kills' => $query->orderByDesc('kills'),
            'deaths' => $query->orderByDesc('deaths'),
            'kd' => $query->whereRaw('deaths > 0')->orderByRaw('CAST(kills AS FLOAT) / CAST(deaths AS FLOAT) DESC'),
            'playtime' => $query->orderByDesc('playtime_seconds'),
            'xp' => $query->orderByDesc('xp_total'),
            'distance' => $query->orderByDesc('total_distance'),
            'roadkills' => $query->orderByDesc('total_roadkills'),
            default => abort(400, 'Invalid leaderboard type'),
        };

        $players = $query->limit($limit)->get()->map(function ($player, $index) {
            return [
                'rank' => $index + 1,
                'player_name' => $player->player_name,
                'player_uuid' => $player->player_uuid,
                'kills' => $player->kills,
                'deaths' => $player->deaths,
                'kd_ratio' => $player->deaths > 0 ? round($player->kills / $player->deaths, 2) : (float) $player->kills,
                'playtime_hours' => round($player->playtime_seconds / 3600, 1),
                'xp' => $player->xp_total,
                'distance_meters' => round($player->total_distance, 2),
                'roadkills' => $player->total_roadkills,
                'headshots' => $player->headshots,
                'shots_fired' => $player->shots_fired,
                'grenades_thrown' => $player->grenades_thrown,
            ];
        });

        $fileName = "leaderboard_{$type}_".now()->format('Y-m-d').'.json';

        return response()->json([
            'type' => $type,
            'exported_at' => now()->toIso8601String(),
            'total_players' => $players->count(),
            'players' => $players,
        ])
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"")
            ->header('Content-Type', 'application/json');
    }

    /**
     * Export match history for a player
     */
    public function exportMatchHistory(Request $request, string $uuid): StreamedResponse
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:10000',
        ]);

        $limit = $validated['limit'] ?? 1000;

        // Get recent kills (as killer)
        $kills = DB::table('player_kills')
            ->where('killer_uuid', $uuid)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        // Get recent deaths (as victim)
        $deaths = DB::table('player_kills')
            ->where('victim_uuid', $uuid)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $player = DB::table('player_stats')->where('player_uuid', $uuid)->first();
        $fileName = "match_history_{$uuid}_".now()->format('Y-m-d').'.csv';

        return $this->generateCsv($fileName, function () use ($kills, $deaths) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['Type', 'Date', 'Killer', 'Victim', 'Weapon', 'Distance', 'Headshot', 'Roadkill', 'Team Kill']);

            // Kills
            foreach ($kills as $kill) {
                fputcsv($handle, [
                    'Kill',
                    $kill->created_at,
                    $kill->killer_name,
                    $kill->victim_name,
                    $kill->weapon_name ?? 'Unknown',
                    $kill->kill_distance ?? 0,
                    $kill->is_headshot ? 'Yes' : 'No',
                    $kill->is_roadkill ? 'Yes' : 'No',
                    $kill->is_team_kill ? 'Yes' : 'No',
                ]);
            }

            // Deaths
            foreach ($deaths as $death) {
                fputcsv($handle, [
                    'Death',
                    $death->created_at,
                    $death->killer_name,
                    $death->victim_name,
                    $death->weapon_name ?? 'Unknown',
                    $death->kill_distance ?? 0,
                    $death->is_headshot ? 'Yes' : 'No',
                    $death->is_roadkill ? 'Yes' : 'No',
                    $death->is_team_kill ? 'Yes' : 'No',
                ]);
            }

            fclose($handle);
        });
    }

    /**
     * Helper: Generate CSV response
     */
    protected function generateCsv(string $fileName, callable $callback): StreamedResponse
    {
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
