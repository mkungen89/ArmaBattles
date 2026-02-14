<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;

class TournamentBracketService
{
    public function generateBracket(Tournament $tournament): void
    {
        match ($tournament->format) {
            'single_elimination' => $this->generateSingleElimination($tournament),
            'double_elimination' => $this->generateDoubleElimination($tournament),
            'round_robin' => $this->generateRoundRobin($tournament),
            'swiss' => $this->generateSwiss($tournament),
        };
    }

    protected function generateSingleElimination(Tournament $tournament): void
    {
        $teams = $tournament->approvedTeams()->get();
        $teamCount = $teams->count();

        if ($teamCount < 2) {
            return;
        }

        // Find nearest power of 2
        $bracketSize = (int) pow(2, ceil(log($teamCount, 2)));
        $rounds = (int) log($bracketSize, 2);

        // Seed teams with byes going to top seeds
        $seededTeams = $this->seedTeamsWithByes($teams, $bracketSize);

        $matchNumber = 1;
        $previousRoundMatches = [];

        for ($round = 1; $round <= $rounds; $round++) {
            $matchesInRound = $bracketSize / pow(2, $round);
            $roundMatches = [];

            for ($i = 0; $i < $matchesInRound; $i++) {
                $match = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => $round,
                    'match_number' => $matchNumber++,
                    'bracket' => 'main',
                    'status' => 'pending',
                    'match_type' => $round === $rounds ? 'best_of_3' : 'best_of_1',
                ]);

                // First round: assign teams
                if ($round === 1) {
                    $team1 = $seededTeams[$i * 2] ?? null;
                    $team2 = $seededTeams[$i * 2 + 1] ?? null;

                    $match->update([
                        'team1_id' => $team1?->id,
                        'team2_id' => $team2?->id,
                    ]);

                    // Handle byes - advance the team that has an opponent
                    if ($team1 && ! $team2) {
                        $match->update([
                            'winner_id' => $team1->id,
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    } elseif (! $team1 && $team2) {
                        $match->update([
                            'winner_id' => $team2->id,
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    }
                }

                $roundMatches[] = $match;
            }

            // Link previous round matches to this round
            if ($round > 1) {
                foreach ($previousRoundMatches as $index => $prevMatch) {
                    $targetMatch = $roundMatches[intdiv($index, 2)];
                    $prevMatch->update(['winner_goes_to' => $targetMatch->id]);
                }

                // Advance bye winners to next round
                foreach ($previousRoundMatches as $index => $prevMatch) {
                    if ($prevMatch->winner_id) {
                        $this->advanceWinner($prevMatch);
                    }
                }
            }

            $previousRoundMatches = $roundMatches;
        }
    }

    protected function generateDoubleElimination(Tournament $tournament): void
    {
        $teams = $tournament->approvedTeams()->get();
        $teamCount = $teams->count();

        if ($teamCount < 2) {
            return;
        }

        $bracketSize = (int) pow(2, ceil(log($teamCount, 2)));
        $winnersRounds = (int) log($bracketSize, 2);
        $losersRounds = ($winnersRounds - 1) * 2;

        // Generate winners bracket
        $seededTeams = $this->seedTeamsWithByes($teams, $bracketSize);
        $matchNumber = 1;
        $winnersMatches = [];

        // Winners bracket
        for ($round = 1; $round <= $winnersRounds; $round++) {
            $matchesInRound = $bracketSize / pow(2, $round);
            $roundMatches = [];

            for ($i = 0; $i < $matchesInRound; $i++) {
                $match = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => $round,
                    'match_number' => $matchNumber++,
                    'bracket' => 'main',
                    'status' => 'pending',
                    'match_type' => 'best_of_1',
                ]);

                if ($round === 1) {
                    $team1 = $seededTeams[$i * 2] ?? null;
                    $team2 = $seededTeams[$i * 2 + 1] ?? null;

                    $match->update([
                        'team1_id' => $team1?->id,
                        'team2_id' => $team2?->id,
                    ]);

                    if ($team1 && ! $team2) {
                        $match->update([
                            'winner_id' => $team1->id,
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    } elseif (! $team1 && $team2) {
                        $match->update([
                            'winner_id' => $team2->id,
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    }
                }

                $roundMatches[] = $match;
            }

            if ($round > 1 && isset($winnersMatches[$round - 1])) {
                foreach ($winnersMatches[$round - 1] as $index => $prevMatch) {
                    $targetMatch = $roundMatches[intdiv($index, 2)];
                    $prevMatch->update(['winner_goes_to' => $targetMatch->id]);
                }
            }

            $winnersMatches[$round] = $roundMatches;
        }

        // Losers bracket
        $losersMatches = [];
        for ($round = 1; $round <= $losersRounds; $round++) {
            $matchesInRound = $this->getLosersMatchCount($bracketSize, $round);
            $roundMatches = [];

            for ($i = 0; $i < $matchesInRound; $i++) {
                $match = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => -$round,
                    'match_number' => $matchNumber++,
                    'bracket' => 'losers',
                    'status' => 'pending',
                    'match_type' => 'best_of_1',
                ]);
                $roundMatches[] = $match;
            }

            // Link losers bracket progression
            if ($round > 1 && isset($losersMatches[$round - 1])) {
                foreach ($losersMatches[$round - 1] as $index => $prevMatch) {
                    if (count($roundMatches) > 0) {
                        $targetIndex = min(intdiv($index, 2), count($roundMatches) - 1);
                        $prevMatch->update(['winner_goes_to' => $roundMatches[$targetIndex]->id]);
                    }
                }
            }

            $losersMatches[$round] = $roundMatches;
        }

        // Link winners losers to losers bracket
        foreach ($winnersMatches as $round => $matches) {
            $losersRound = ($round - 1) * 2 + 1;
            if (isset($losersMatches[$losersRound])) {
                foreach ($matches as $index => $match) {
                    $targetIndex = min($index, count($losersMatches[$losersRound]) - 1);
                    if (isset($losersMatches[$losersRound][$targetIndex])) {
                        $match->update(['loser_goes_to' => $losersMatches[$losersRound][$targetIndex]->id]);
                    }
                }
            }
        }

        // Grand final
        $grandFinal = TournamentMatch::create([
            'tournament_id' => $tournament->id,
            'round' => 99,
            'match_number' => $matchNumber++,
            'bracket' => 'grand_final',
            'status' => 'pending',
            'match_type' => 'best_of_3',
        ]);

        // Link winners final and losers final to grand final
        if (isset($winnersMatches[$winnersRounds])) {
            $winnersMatches[$winnersRounds][0]->update(['winner_goes_to' => $grandFinal->id]);
        }
        if (isset($losersMatches[$losersRounds])) {
            $losersMatches[$losersRounds][0]->update(['winner_goes_to' => $grandFinal->id]);
        }

        // Advance byes
        foreach ($winnersMatches[1] ?? [] as $match) {
            if ($match->winner_id) {
                $this->advanceWinner($match);
            }
        }
    }

    protected function generateRoundRobin(Tournament $tournament): void
    {
        $teams = $tournament->approvedTeams()->get()->shuffle();
        $teamCount = $teams->count();

        if ($teamCount < 2) {
            return;
        }

        $matchNumber = 1;

        // Generate all possible matchups
        for ($i = 0; $i < $teamCount; $i++) {
            for ($j = $i + 1; $j < $teamCount; $j++) {
                $round = $this->calculateRoundRobinRound($i, $j, $teamCount);

                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => $round,
                    'match_number' => $matchNumber++,
                    'bracket' => 'main',
                    'team1_id' => $teams[$i]->id,
                    'team2_id' => $teams[$j]->id,
                    'status' => 'pending',
                    'match_type' => 'best_of_1',
                ]);
            }
        }
    }

    protected function generateSwiss(Tournament $tournament): void
    {
        $teams = $tournament->approvedTeams()->get()->shuffle();

        if ($teams->count() < 2) {
            return;
        }

        $matchNumber = 1;

        // Pair teams randomly for round 1
        for ($i = 0; $i < $teams->count(); $i += 2) {
            if (isset($teams[$i + 1])) {
                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => 1,
                    'match_number' => $matchNumber++,
                    'bracket' => 'main',
                    'team1_id' => $teams[$i]->id,
                    'team2_id' => $teams[$i + 1]->id,
                    'status' => 'pending',
                    'match_type' => 'best_of_1',
                ]);
            }
        }
    }

    public function generateNextSwissRound(Tournament $tournament): bool
    {
        $currentRound = $tournament->matches()->where('bracket', 'main')->max('round') ?? 0;
        $nextRound = $currentRound + 1;
        $maxRounds = $tournament->swiss_rounds ?? site_setting('tournament_default_swiss_rounds', 5);

        if ($nextRound > $maxRounds) {
            return false;
        }

        // Check if all matches in current round are complete
        $pendingMatches = $tournament->matches()
            ->where('round', $currentRound)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        if ($pendingMatches > 0) {
            return false;
        }

        // Get standings and pair teams with similar records
        $standings = $this->getSwissStandings($tournament);
        $paired = [];
        $matchNumber = $tournament->matches()->max('match_number') + 1;

        foreach ($standings as $team) {
            if (in_array($team->id, $paired)) {
                continue;
            }

            $opponent = $this->findSwissOpponent($tournament, $team, $standings, $paired);

            if ($opponent) {
                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => $nextRound,
                    'match_number' => $matchNumber++,
                    'bracket' => 'main',
                    'team1_id' => $team->id,
                    'team2_id' => $opponent->id,
                    'status' => 'pending',
                    'match_type' => 'best_of_1',
                ]);

                $paired[] = $team->id;
                $paired[] = $opponent->id;
            }
        }

        return true;
    }

    public function advanceWinner(TournamentMatch $match): void
    {
        if (! $match->winner_id || ! $match->winner_goes_to) {
            return;
        }

        $nextMatch = TournamentMatch::find($match->winner_goes_to);

        if (! $nextMatch) {
            return;
        }

        // Place winner in appropriate slot
        if (! $nextMatch->team1_id) {
            $nextMatch->update(['team1_id' => $match->winner_id]);
        } elseif (! $nextMatch->team2_id) {
            $nextMatch->update(['team2_id' => $match->winner_id]);
        }
    }

    public function advanceLoser(TournamentMatch $match): void
    {
        if (! $match->winner_id || ! $match->loser_goes_to) {
            return;
        }

        $loserId = $match->team1_id === $match->winner_id ? $match->team2_id : $match->team1_id;

        if (! $loserId) {
            return;
        }

        $nextMatch = TournamentMatch::find($match->loser_goes_to);

        if (! $nextMatch) {
            return;
        }

        if (! $nextMatch->team1_id) {
            $nextMatch->update(['team1_id' => $loserId]);
        } elseif (! $nextMatch->team2_id) {
            $nextMatch->update(['team2_id' => $loserId]);
        }
    }

    protected function seedTeamsWithByes(Collection $teams, int $bracketSize): array
    {
        $seeded = array_fill(0, $bracketSize, null);
        $positions = $this->getSeededPositions($bracketSize);

        foreach ($teams as $index => $team) {
            if (isset($positions[$index])) {
                $seeded[$positions[$index]] = $team;
            }
        }

        return $seeded;
    }

    protected function getSeededPositions(int $size): array
    {
        if ($size === 2) {
            return [0, 1];
        }

        $half = $size / 2;
        $positions = $this->getSeededPositions($half);
        $result = [];

        foreach ($positions as $pos) {
            $result[] = $pos;
            $result[] = $size - 1 - $pos;
        }

        return $result;
    }

    protected function getLosersMatchCount(int $bracketSize, int $round): int
    {
        $winnersRounds = (int) log($bracketSize, 2);

        // Losers bracket has roughly half the matches of winners in alternating pattern
        if ($round % 2 === 1) {
            return max(1, $bracketSize / pow(2, (int) ceil($round / 2) + 1));
        }

        return max(1, $bracketSize / pow(2, (int) ceil($round / 2) + 1));
    }

    protected function calculateRoundRobinRound(int $i, int $j, int $teamCount): int
    {
        // Circle method for round robin scheduling
        $rounds = $teamCount % 2 === 0 ? $teamCount - 1 : $teamCount;

        return (($i + $j) % $rounds) + 1;
    }

    public function getSwissStandings(Tournament $tournament): Collection
    {
        $teams = $tournament->approvedTeams()->get();

        return $teams->map(function ($team) use ($tournament) {
            $matches = $tournament->matches()
                ->where('status', 'completed')
                ->where(function ($q) use ($team) {
                    $q->where('team1_id', $team->id)
                        ->orWhere('team2_id', $team->id);
                })->get();

            $team->wins = $matches->where('winner_id', $team->id)->count();
            $team->losses = $matches->count() - $team->wins;
            $team->score = $team->wins;

            // Calculate opponent win percentage (tiebreaker)
            $team->opponent_wins = 0;
            $opponentCount = 0;

            foreach ($matches as $match) {
                $opponentId = $match->team1_id === $team->id ? $match->team2_id : $match->team1_id;
                $opponentMatches = $tournament->matches()
                    ->where('status', 'completed')
                    ->where(function ($q) use ($opponentId) {
                        $q->where('team1_id', $opponentId)->orWhere('team2_id', $opponentId);
                    })->get();

                $team->opponent_wins += $opponentMatches->where('winner_id', $opponentId)->count();
                $opponentCount += $opponentMatches->count();
            }

            $team->buchholz = $opponentCount > 0 ? $team->opponent_wins / $opponentCount : 0;

            return $team;
        })->sortByDesc(function ($team) {
            return [$team->score, $team->buchholz];
        })->values();
    }

    protected function findSwissOpponent(Tournament $tournament, Team $team, Collection $standings, array $paired): ?Team
    {
        $previousOpponents = $tournament->matches()
            ->where(function ($q) use ($team) {
                $q->where('team1_id', $team->id)->orWhere('team2_id', $team->id);
            })
            ->get()
            ->flatMap(fn ($m) => [$m->team1_id, $m->team2_id])
            ->filter(fn ($id) => $id !== $team->id)
            ->toArray();

        // Find opponent with similar score who hasn't been paired
        foreach ($standings as $opponent) {
            if ($opponent->id === $team->id) {
                continue;
            }
            if (in_array($opponent->id, $paired)) {
                continue;
            }
            if (in_array($opponent->id, $previousOpponents)) {
                continue;
            }

            return $opponent;
        }

        return null;
    }

    public function checkTournamentComplete(Tournament $tournament): bool
    {
        $pendingMatches = $tournament->matches()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        if ($pendingMatches > 0) {
            return false;
        }

        // Find winner from final match
        $finalMatch = $tournament->matches()
            ->where('bracket', 'grand_final')
            ->first();

        if (! $finalMatch) {
            $finalMatch = $tournament->matches()
                ->where('bracket', 'main')
                ->orderByDesc('round')
                ->orderBy('match_number')
                ->first();
        }

        if ($finalMatch?->winner_id) {
            $tournament->update([
                'status' => 'completed',
                'winner_team_id' => $finalMatch->winner_id,
                'ends_at' => now(),
            ]);

            // Send Discord notification
            try {
                $discord = app(DiscordWebhookService::class);
                $tournament->load('winnerTeam');

                $discord->sendTournamentResult([
                    'name' => $tournament->name,
                    'format' => $tournament->format,
                    'team_count' => $tournament->approvedTeams()->count(),
                    'url' => route('tournaments.show', $tournament),
                ], [
                    'name' => $tournament->winnerTeam->name,
                ]);
            } catch (\Exception $e) {
                // Don't fail if Discord notification fails
                \Log::warning('Discord tournament result notification failed', ['error' => $e->getMessage()]);
            }

            return true;
        }

        return false;
    }
}
