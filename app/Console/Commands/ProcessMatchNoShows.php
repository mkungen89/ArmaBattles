<?php

namespace App\Console\Commands;

use App\Models\TournamentMatch;
use Illuminate\Console\Command;

class ProcessMatchNoShows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matches:process-no-shows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-forfeit matches where teams did not check in';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing match no-shows...');

        // Find matches that started more than 15 minutes ago but are still pending
        $noShowMatches = TournamentMatch::where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', now()->subMinutes(15))
            ->with(['team1', 'team2', 'tournament'])
            ->get();

        $processed = 0;

        foreach ($noShowMatches as $match) {
            $team1CheckedIn = $match->team1_checked_in;
            $team2CheckedIn = $match->team2_checked_in;

            // Case 1: Both teams no-show - mark as cancelled
            if (!$team1CheckedIn && !$team2CheckedIn) {
                $match->update([
                    'status' => 'cancelled',
                    'notes' => 'Auto-cancelled: Both teams failed to check in',
                ]);

                $this->warn("Match #{$match->id}: Both teams no-show - CANCELLED");
                $processed++;
                continue;
            }

            // Case 2: Team 1 no-show - forfeit to team 2
            if (!$team1CheckedIn && $team2CheckedIn) {
                $match->update([
                    'status' => 'completed',
                    'winner_team_id' => $match->team2_id,
                    'team2_score' => 1,
                    'team1_score' => 0,
                    'notes' => 'Auto-forfeit: Team 1 failed to check in',
                    'completed_at' => now(),
                ]);

                $this->line("Match #{$match->id}: Team 1 no-show - Team 2 wins by forfeit");
                $processed++;
                continue;
            }

            // Case 3: Team 2 no-show - forfeit to team 1
            if ($team1CheckedIn && !$team2CheckedIn) {
                $match->update([
                    'status' => 'completed',
                    'winner_team_id' => $match->team1_id,
                    'team1_score' => 1,
                    'team2_score' => 0,
                    'notes' => 'Auto-forfeit: Team 2 failed to check in',
                    'completed_at' => now(),
                ]);

                $this->line("Match #{$match->id}: Team 2 no-show - Team 1 wins by forfeit");
                $processed++;
                continue;
            }

            // Case 4: Both checked in but match not started - start it
            if ($team1CheckedIn && $team2CheckedIn) {
                $match->update([
                    'status' => 'in_progress',
                    'notes' => 'Auto-started: Both teams checked in',
                ]);

                $this->info("Match #{$match->id}: Both teams ready - STARTED");
                $processed++;
            }
        }

        $this->info("Processed {$processed} match(es).");

        return Command::SUCCESS;
    }
}
