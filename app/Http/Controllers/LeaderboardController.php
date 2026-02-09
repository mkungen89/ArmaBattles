<?php

namespace App\Http\Controllers;

use App\Models\PlayerStat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'kills');
        $period = $request->get('period', 'all');
        $validSorts = site_setting('leaderboard_categories', ['kills', 'deaths', 'headshots', 'playtime_seconds', 'total_distance', 'bases_captured', 'heals_given', 'supplies_delivered', 'xp_total']);

        if (!in_array($sort, $validSorts)) {
            $sort = 'kills';
        }

        if (!in_array($period, ['all', 'monthly', 'weekly'])) {
            $period = 'all';
        }

        if ($period === 'all') {
            $query = PlayerStat::where($sort, '>', 0);

            $minPlaytime = site_setting('leaderboard_min_playtime', 0);
            if ($minPlaytime > 0) {
                $query->where('playtime_seconds', '>=', $minPlaytime);
            }

            $players = $query->orderByDesc($sort)
                ->paginate(site_setting('leaderboard_per_page', 50));
        } else {
            $since = $period === 'weekly' ? now()->subWeek() : now()->subMonth();
            $players = $this->getTimeBasedLeaderboard($sort, $since);
        }

        $uuids = $players->pluck('player_uuid')->filter()->toArray();
        $linkedUsers = User::whereIn('player_uuid', $uuids)
            ->get(['id', 'player_uuid', 'avatar'])
            ->keyBy('player_uuid');

        if ($request->wantsJson()) {
            $perPage = $players->perPage();
            $currentPage = $players->currentPage();
            $data = $players->getCollection()->map(function ($player, $index) use ($linkedUsers, $sort, $currentPage, $perPage) {
                $rank = ($currentPage - 1) * $perPage + $index + 1;
                $user = $linkedUsers[$player->player_uuid] ?? null;
                $kd = $player->deaths > 0 ? round($player->kills / $player->deaths, 2) : $player->kills;

                return [
                    'rank' => $rank,
                    'player_name' => $player->player_name,
                    'player_uuid' => $player->player_uuid,
                    'avatar' => $user?->avatar_display ?? null,
                    'profile_url' => $user ? route('players.show', $user->id) : null,
                    'kills' => (int) $player->kills,
                    'deaths' => (int) $player->deaths,
                    'kd' => $kd,
                    'headshots' => (int) $player->headshots,
                    'playtime_seconds' => (int) $player->playtime_seconds,
                    'total_distance' => (int) $player->total_distance,
                    'bases_captured' => (int) $player->bases_captured,
                    'heals_given' => (int) $player->heals_given,
                    'supplies_delivered' => (int) $player->supplies_delivered,
                    'xp_total' => (int) $player->xp_total,
                    'sort_active' => $sort,
                ];
            });

            return response()->json([
                'data' => $data,
                'current_page' => $currentPage,
                'next_page' => $players->hasMorePages() ? $currentPage + 1 : null,
                'total' => $players->total(),
            ]);
        }

        return view('leaderboard', [
            'players' => $players,
            'sort' => $sort,
            'period' => $period,
            'validSorts' => $validSorts,
            'linkedUsers' => $linkedUsers,
        ]);
    }

    private function getTimeBasedLeaderboard(string $sort, $since)
    {
        $perPage = site_setting('leaderboard_per_page', 50);

        $query = match ($sort) {
            'kills' => DB::table('player_kills')
                ->select('killer_uuid as player_uuid', 'killer_name as player_name', DB::raw('COUNT(*) as value'))
                ->where('killed_at', '>=', $since)
                ->whereNotNull('killer_uuid')
                ->groupBy('killer_uuid', 'killer_name'),

            'deaths' => DB::table('player_kills')
                ->select('victim_uuid as player_uuid', 'victim_name as player_name', DB::raw('COUNT(*) as value'))
                ->where('killed_at', '>=', $since)
                ->whereNotNull('victim_uuid')
                ->where('victim_type', '!=', 'AI')
                ->groupBy('victim_uuid', 'victim_name'),

            'headshots' => DB::table('player_kills')
                ->select('killer_uuid as player_uuid', 'killer_name as player_name', DB::raw('COUNT(*) as value'))
                ->where('killed_at', '>=', $since)
                ->where('is_headshot', true)
                ->whereNotNull('killer_uuid')
                ->groupBy('killer_uuid', 'killer_name'),

            'playtime_seconds' => DB::table('player_distance')
                ->select('player_uuid', 'player_name', DB::raw('COALESCE(SUM(playtime_seconds), 0) as value'))
                ->where('created_at', '>=', $since)
                ->whereNotNull('player_uuid')
                ->groupBy('player_uuid', 'player_name'),

            'xp_total' => DB::table('xp_events')
                ->select('player_uuid', 'player_name', DB::raw('COALESCE(SUM(xp_amount), 0) as value'))
                ->where('created_at', '>=', $since)
                ->whereNotNull('player_uuid')
                ->groupBy('player_uuid', 'player_name'),

            'total_distance' => DB::table('player_distance')
                ->select('player_uuid', 'player_name', DB::raw('COALESCE(SUM(walking_distance), 0) as value'))
                ->where('created_at', '>=', $since)
                ->whereNotNull('player_uuid')
                ->groupBy('player_uuid', 'player_name'),

            'heals_given' => DB::table('player_healing_rjs')
                ->select('player_uuid', 'player_name', DB::raw('COUNT(*) as value'))
                ->where('created_at', '>=', $since)
                ->whereNotNull('player_uuid')
                ->groupBy('player_uuid', 'player_name'),

            'bases_captured' => DB::table('base_events')
                ->select('player_uuid', 'player_name', DB::raw('COUNT(*) as value'))
                ->where('created_at', '>=', $since)
                ->whereIn('event_type', ['CAPTURED', 'CAPTURE', 'BASE_SEIZED', 'BASE_CAPTURE'])
                ->whereNotNull('player_uuid')
                ->groupBy('player_uuid', 'player_name'),

            'supplies_delivered' => DB::table('supply_deliveries')
                ->select('player_uuid', 'player_name', DB::raw('COUNT(*) as value'))
                ->where('created_at', '>=', $since)
                ->whereNotNull('player_uuid')
                ->groupBy('player_uuid', 'player_name'),

            default => DB::table('player_kills')
                ->select('killer_uuid as player_uuid', 'killer_name as player_name', DB::raw('COUNT(*) as value'))
                ->where('killed_at', '>=', $since)
                ->whereNotNull('killer_uuid')
                ->groupBy('killer_uuid', 'killer_name'),
        };

        $results = $query->orderByDesc('value')->paginate($perPage);

        // Map value back to the expected column names for the view
        $results->getCollection()->transform(function ($item) use ($sort) {
            // Fill in the stat-specific column for the view
            $item->{$sort} = $item->value;
            // Fill zero defaults for all display columns
            $item->kills = $item->kills ?? 0;
            $item->deaths = $item->deaths ?? 0;
            $item->headshots = $item->headshots ?? 0;
            $item->playtime_seconds = $item->playtime_seconds ?? 0;
            $item->total_distance = $item->total_distance ?? 0;
            $item->bases_captured = $item->bases_captured ?? 0;
            $item->heals_given = $item->heals_given ?? 0;
            $item->supplies_delivered = $item->supplies_delivered ?? 0;
            $item->xp_total = $item->xp_total ?? 0;
            return $item;
        });

        return $results;
    }
}
