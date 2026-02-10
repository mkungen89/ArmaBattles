<?php

namespace App\Services;

use App\Models\PlayerRating;
use App\Models\RatingHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RatingCalculationService
{
    // Synthetic opponent ratings for objective-based events.
    // These create "phantom" opponents in Glicko-2 so objective play
    // impacts rating without needing a real opponent.
    //
    // Vehicle destroy  = win vs 1600 (high value — destroying armor is hard and impactful)
    // Base capture     = win vs 1500 (equal to beating an average player)
    // Healing teammate = win vs 1300 (smaller boost, rewards medics)
    // Supply delivery  = win vs 1300 (smaller boost, rewards logistics)
    // Building placed  = win vs 1200 (small boost, lower value due to farm risk)
    // Team kill        = LOSS vs own rating, RD 150 (significant penalty)
    // Friendly fire    = LOSS vs own rating, RD 250 (moderate penalty, less than TK)
    private const PHANTOM_RD = 150;

    private const PHANTOM_RD_FRIENDLY_FIRE = 250;

    private const PHANTOM_RATING_VEHICLE_DESTROY = 1600;

    private const PHANTOM_RATING_BASE_CAPTURE = 1500;

    private const PHANTOM_RATING_HEAL = 1300;

    private const PHANTOM_RATING_SUPPLY = 1300;

    private const PHANTOM_RATING_BUILDING = 1200;

    public function __construct(
        private Glicko2Service $glicko2
    ) {}

    /**
     * Get the cached set of competitive player UUIDs.
     */
    private function getCompetitiveUuids(): array
    {
        return Cache::remember('competitive_player_uuids', 300, function () {
            return PlayerRating::competitive()
                ->pluck('player_uuid')
                ->flip()
                ->toArray();
        });
    }

    /**
     * Check if a player UUID is competitive.
     */
    public function isCompetitivePlayer(string $uuid): bool
    {
        return isset($this->getCompetitiveUuids()[$uuid]);
    }

    /**
     * Check if a kill qualifies as a rated encounter.
     * For PvP kills: both players must be competitive.
     * For team kills: only the killer needs to be competitive (penalty applies to them).
     */
    public function isRatedKill(string $killerUuid, ?string $victimUuid, ?string $victimType, bool $isTeamKill): bool
    {
        // Always exclude AI kills
        if ($victimType && strtoupper($victimType) === 'AI') {
            return false;
        }

        $competitiveUuids = $this->getCompetitiveUuids();

        // Team kill: only killer must be competitive (they get penalized)
        if ($isTeamKill) {
            return isset($competitiveUuids[$killerUuid]);
        }

        // Regular PvP kill: both must be competitive
        return $victimUuid
            && isset($competitiveUuids[$killerUuid])
            && isset($competitiveUuids[$victimUuid]);
    }

    /**
     * Queue a kill for rating processing.
     */
    public function queueRatedKill(int $killId, array $killData): void
    {
        $eventType = ! empty($killData['is_team_kill']) ? 'team_kill' : 'kill';

        DB::table('rated_kills_queue')->insert([
            'kill_id' => $killId,
            'event_type' => $eventType,
            'player_uuid' => $killData['killer_uuid'],
            'killer_uuid' => $killData['killer_uuid'],
            'victim_uuid' => $killData['victim_uuid'] ?? $killData['killer_uuid'],
            'is_headshot' => $killData['is_headshot'] ?? false,
            'is_team_kill' => $killData['is_team_kill'] ?? false,
            'kill_distance' => $killData['kill_distance'] ?? 0,
            'weapon_name' => $killData['weapon_name'] ?? null,
            'server_id' => $killData['server_id'] ?? null,
            'killed_at' => $killData['killed_at'] ?? now(),
            'processed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Queue an objective event (base capture, heal, supply) for rating processing.
     */
    public function queueRatedObjective(string $eventType, string $playerUuid, array $data = []): void
    {
        DB::table('rated_kills_queue')->insert([
            'kill_id' => $data['event_id'] ?? 0,
            'event_type' => $eventType,
            'player_uuid' => $playerUuid,
            'killer_uuid' => $playerUuid,
            'victim_uuid' => $playerUuid, // no real victim for objectives
            'is_headshot' => false,
            'is_team_kill' => false,
            'kill_distance' => 0,
            'weapon_name' => null,
            'server_id' => $data['server_id'] ?? null,
            'killed_at' => $data['timestamp'] ?? now(),
            'processed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Process all unprocessed events in the rating queue and update ratings.
     *
     * Event types and their Glicko-2 mapping:
     *   kill            → Killer: win vs victim's real rating. Victim: loss vs killer's real rating.
     *   team_kill       → Killer: LOSS vs phantom at own rating (significant penalty).
     *   friendly_fire   → Attacker: LOSS vs phantom at own rating, higher RD (moderate penalty).
     *   vehicle_destroy → Player: win vs phantom at 1600 (high-value target reward).
     *   base_capture    → Player: win vs phantom at 1500 (objective reward).
     *   heal            → Player: win vs phantom at 1300 (teamplay reward).
     *   supply          → Player: win vs phantom at 1300 (logistics reward).
     *   building        → Player: win vs phantom at 1200 (engineer reward).
     */
    public function processRatingPeriod(): array
    {
        $periodStart = DB::table('rated_kills_queue')
            ->where('processed', false)
            ->min('killed_at');

        if (! $periodStart) {
            return ['processed' => 0, 'players_updated' => 0];
        }

        $periodEnd = now();

        // Fetch all unprocessed events
        $events = DB::table('rated_kills_queue')
            ->where('processed', false)
            ->orderBy('killed_at')
            ->get();

        if ($events->isEmpty()) {
            return ['processed' => 0, 'players_updated' => 0];
        }

        // Gather all involved UUIDs to load ratings
        $allUuids = collect();
        foreach ($events as $event) {
            $allUuids->push($event->killer_uuid);
            if ($event->victim_uuid && $event->victim_uuid !== $event->killer_uuid) {
                $allUuids->push($event->victim_uuid);
            }
            if ($event->player_uuid) {
                $allUuids->push($event->player_uuid);
            }
        }

        $ratings = PlayerRating::competitive()
            ->whereIn('player_uuid', $allUuids->unique()->toArray())
            ->get()
            ->keyBy('player_uuid');

        // Build encounters per player
        $playerEncounters = [];

        foreach ($events as $event) {
            $type = $event->event_type ?? 'kill';

            switch ($type) {
                case 'kill':
                    // Standard PvP: killer wins, victim loses (using real ratings)
                    $killerRating = $ratings->get($event->killer_uuid);
                    $victimRating = $ratings->get($event->victim_uuid);

                    if ($killerRating && $victimRating) {
                        $playerEncounters[$event->killer_uuid]['opponents'][] = [
                            'rating' => (float) $victimRating->rating,
                            'rd' => (float) $victimRating->rating_deviation,
                        ];
                        $playerEncounters[$event->killer_uuid]['outcomes'][] = 1.0;
                        $playerEncounters[$event->killer_uuid]['kills'] = ($playerEncounters[$event->killer_uuid]['kills'] ?? 0) + 1;

                        $playerEncounters[$event->victim_uuid]['opponents'][] = [
                            'rating' => (float) $killerRating->rating,
                            'rd' => (float) $killerRating->rating_deviation,
                        ];
                        $playerEncounters[$event->victim_uuid]['outcomes'][] = 0.0;
                        $playerEncounters[$event->victim_uuid]['deaths'] = ($playerEncounters[$event->victim_uuid]['deaths'] ?? 0) + 1;
                    }
                    break;

                case 'team_kill':
                    // Team kill penalty: killer "loses" against phantom at own rating
                    $killerRating = $ratings->get($event->killer_uuid);
                    if ($killerRating) {
                        $playerEncounters[$event->killer_uuid]['opponents'][] = [
                            'rating' => (float) $killerRating->rating,
                            'rd' => self::PHANTOM_RD,
                        ];
                        $playerEncounters[$event->killer_uuid]['outcomes'][] = 0.0;
                        // Count as a death for stats (friendly fire = self-inflicted)
                        $playerEncounters[$event->killer_uuid]['deaths'] = ($playerEncounters[$event->killer_uuid]['deaths'] ?? 0) + 1;
                    }
                    break;

                case 'friendly_fire':
                    // Friendly fire penalty: attacker "loses" against phantom at own rating
                    // Uses higher phantom RD (250) than team_kill (150) = less severe
                    $attackerRating = $ratings->get($event->killer_uuid);
                    if ($attackerRating) {
                        $playerEncounters[$event->killer_uuid]['opponents'][] = [
                            'rating' => (float) $attackerRating->rating,
                            'rd' => self::PHANTOM_RD_FRIENDLY_FIRE,
                        ];
                        $playerEncounters[$event->killer_uuid]['outcomes'][] = 0.0;
                    }
                    break;

                case 'vehicle_destroy':
                    // High-value target reward: "win" against tough phantom
                    $uuid = $event->player_uuid ?? $event->killer_uuid;
                    if ($ratings->has($uuid)) {
                        $playerEncounters[$uuid]['opponents'][] = [
                            'rating' => self::PHANTOM_RATING_VEHICLE_DESTROY,
                            'rd' => self::PHANTOM_RD,
                        ];
                        $playerEncounters[$uuid]['outcomes'][] = 1.0;
                    }
                    break;

                case 'base_capture':
                    // Objective reward: "win" against moderate phantom
                    $uuid = $event->player_uuid ?? $event->killer_uuid;
                    if ($ratings->has($uuid)) {
                        $playerEncounters[$uuid]['opponents'][] = [
                            'rating' => self::PHANTOM_RATING_BASE_CAPTURE,
                            'rd' => self::PHANTOM_RD,
                        ];
                        $playerEncounters[$uuid]['outcomes'][] = 1.0;
                    }
                    break;

                case 'heal':
                    // Teamplay reward: "win" against lower phantom
                    $uuid = $event->player_uuid ?? $event->killer_uuid;
                    if ($ratings->has($uuid)) {
                        $playerEncounters[$uuid]['opponents'][] = [
                            'rating' => self::PHANTOM_RATING_HEAL,
                            'rd' => self::PHANTOM_RD,
                        ];
                        $playerEncounters[$uuid]['outcomes'][] = 1.0;
                    }
                    break;

                case 'supply':
                    // Logistics reward: "win" against lower phantom
                    $uuid = $event->player_uuid ?? $event->killer_uuid;
                    if ($ratings->has($uuid)) {
                        $playerEncounters[$uuid]['opponents'][] = [
                            'rating' => self::PHANTOM_RATING_SUPPLY,
                            'rd' => self::PHANTOM_RD,
                        ];
                        $playerEncounters[$uuid]['outcomes'][] = 1.0;
                    }
                    break;

                case 'building':
                    // Engineer reward: "win" against easy phantom (low value due to farm risk)
                    $uuid = $event->player_uuid ?? $event->killer_uuid;
                    if ($ratings->has($uuid)) {
                        $playerEncounters[$uuid]['opponents'][] = [
                            'rating' => self::PHANTOM_RATING_BUILDING,
                            'rd' => self::PHANTOM_RD,
                        ];
                        $playerEncounters[$uuid]['outcomes'][] = 1.0;
                    }
                    break;
            }
        }

        $playersUpdated = 0;

        foreach ($playerEncounters as $playerUuid => $encounters) {
            $playerRating = $ratings->get($playerUuid);
            if (! $playerRating || empty($encounters['opponents'])) {
                continue;
            }

            // Calculate new rating
            $newRating = $this->glicko2->calculateNewRating(
                (float) $playerRating->rating,
                (float) $playerRating->rating_deviation,
                (float) $playerRating->volatility,
                $encounters['opponents'],
                $encounters['outcomes']
            );

            $oldTier = $playerRating->rank_tier;
            $periodKills = $encounters['kills'] ?? 0;
            $periodDeaths = $encounters['deaths'] ?? 0;
            $totalEncounters = count($encounters['opponents']);
            $newGamesPlayed = $playerRating->games_played + $totalEncounters;
            $newPlacementGames = $playerRating->placement_games + $totalEncounters;
            $isPlaced = $playerRating->is_placed || $newPlacementGames >= 10;
            $newTier = PlayerRating::calculateTier($newRating['rating'], $isPlaced);
            $newPeak = max((float) $playerRating->peak_rating, $newRating['rating']);

            // Record history
            RatingHistory::create([
                'player_rating_id' => $playerRating->id,
                'player_uuid' => $playerUuid,
                'rating_before' => $playerRating->rating,
                'rating_after' => $newRating['rating'],
                'rd_before' => $playerRating->rating_deviation,
                'rd_after' => $newRating['rd'],
                'volatility_before' => $playerRating->volatility,
                'volatility_after' => $newRating['volatility'],
                'rank_tier_before' => $oldTier,
                'rank_tier_after' => $newTier,
                'period_kills' => $periodKills,
                'period_deaths' => $periodDeaths,
                'period_encounters' => $totalEncounters,
                'season' => $playerRating->current_season,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ]);

            // Update player rating
            $playerRating->update([
                'rating' => $newRating['rating'],
                'rating_deviation' => $newRating['rd'],
                'volatility' => $newRating['volatility'],
                'rank_tier' => $newTier,
                'ranked_kills' => $playerRating->ranked_kills + $periodKills,
                'ranked_deaths' => $playerRating->ranked_deaths + $periodDeaths,
                'games_played' => $newGamesPlayed,
                'placement_games' => min($newPlacementGames, 10),
                'is_placed' => $isPlaced,
                'peak_rating' => $newPeak,
                'last_rated_at' => $periodEnd,
            ]);

            $playersUpdated++;
        }

        // Mark all processed
        DB::table('rated_kills_queue')
            ->where('processed', false)
            ->update(['processed' => true, 'updated_at' => now()]);

        // Clear caches
        Cache::forget('competitive_player_uuids');
        Cache::forget('ranked_leaderboard:50');
        Cache::forget('ranked_leaderboard:100');

        return [
            'processed' => $events->count(),
            'players_updated' => $playersUpdated,
        ];
    }

    /**
     * Apply inactivity decay — increase RD for players who haven't played.
     */
    public function applyInactivityDecay(int $daysThreshold = 14): int
    {
        $cutoff = now()->subDays($daysThreshold);

        $inactivePlayers = PlayerRating::competitive()
            ->where(function ($q) use ($cutoff) {
                $q->where('last_rated_at', '<', $cutoff)
                    ->orWhereNull('last_rated_at');
            })
            ->where('rating_deviation', '<', 350)
            ->get();

        $decayed = 0;

        foreach ($inactivePlayers as $rating) {
            $newRd = $this->glicko2->applyRdIncrease(
                (float) $rating->rating_deviation,
                (float) $rating->volatility,
                1
            );

            if ($newRd > (float) $rating->rating_deviation) {
                $rating->update(['rating_deviation' => $newRd]);
                $decayed++;
            }
        }

        return $decayed;
    }
}
