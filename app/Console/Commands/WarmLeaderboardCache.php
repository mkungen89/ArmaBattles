<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarmLeaderboardCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaderboards:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm leaderboard caches by pre-loading common queries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Warming leaderboard caches...');

        $commonLimits = [25, 50, 100];
        $minKillsValues = [5, 10, 20, 50];
        $warmed = 0;

        foreach ($commonLimits as $limit) {
            // Kills leaderboard
            $this->warmCache("leaderboard:kills:limit_{$limit}", function () use ($limit) {
                return DB::table('player_stats')
                    ->orderByDesc('kills')
                    ->limit($limit)
                    ->get(['player_uuid', 'player_name', 'kills', 'deaths', 'playtime_seconds']);
            });
            $warmed++;

            // Deaths leaderboard
            $this->warmCache("leaderboard:deaths:limit_{$limit}", function () use ($limit) {
                return DB::table('player_stats')
                    ->orderByDesc('deaths')
                    ->limit($limit)
                    ->get(['player_uuid', 'player_name', 'kills', 'deaths', 'playtime_seconds']);
            });
            $warmed++;

            // K/D leaderboards with different min_kills
            foreach ($minKillsValues as $minKills) {
                $this->warmCache("leaderboard:kd:limit_{$limit}:min_{$minKills}", function () use ($limit, $minKills) {
                    return DB::table('player_stats')
                        ->where('player_kills_count', '>=', $minKills)
                        ->selectRaw('player_uuid, player_name, kills, player_kills_count, deaths, playtime_seconds, CASE WHEN deaths > 0 THEN ROUND(player_kills_count::numeric / deaths, 2) ELSE player_kills_count END as kd_ratio')
                        ->orderByDesc('kd_ratio')
                        ->limit($limit)
                        ->get();
                });
                $warmed++;
            }

            // Playtime leaderboard
            $this->warmCache("leaderboard:playtime:limit_{$limit}", function () use ($limit) {
                return DB::table('player_stats')
                    ->orderByDesc('playtime_seconds')
                    ->limit($limit)
                    ->get(['player_uuid', 'player_name', 'playtime_seconds', 'kills', 'deaths']);
            });
            $warmed++;

            // XP leaderboard
            $this->warmCache("leaderboard:xp:limit_{$limit}", function () use ($limit) {
                return DB::table('player_stats')
                    ->orderByDesc('xp_total')
                    ->limit($limit)
                    ->get(['player_uuid', 'player_name', 'xp_total', 'kills', 'deaths']);
            });
            $warmed++;

            // Distance leaderboard
            $this->warmCache("leaderboard:distance:limit_{$limit}", function () use ($limit) {
                return DB::table('player_stats')
                    ->orderByDesc('total_distance')
                    ->limit($limit)
                    ->get(['player_uuid', 'player_name', 'total_distance', 'playtime_seconds']);
            });
            $warmed++;

            // Roadkills leaderboard
            $this->warmCache("leaderboard:roadkills:limit_{$limit}", function () use ($limit) {
                return DB::table('player_stats')
                    ->where('total_roadkills', '>', 0)
                    ->orderByDesc('total_roadkills')
                    ->limit($limit)
                    ->get(['player_uuid', 'player_name', 'total_roadkills', 'kills', 'deaths']);
            });
            $warmed++;
        }

        $this->info("✓ Warmed {$warmed} leaderboard caches (5 minute TTL)");

        return Command::SUCCESS;
    }

    /**
     * Warm a specific cache key with data
     */
    private function warmCache(string $key, callable $callback): void
    {
        Cache::remember($key, 300, $callback);
        $this->line("  - Cached: {$key}");
    }
}
