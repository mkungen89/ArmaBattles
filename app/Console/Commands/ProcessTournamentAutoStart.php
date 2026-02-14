<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use Illuminate\Console\Command;

class ProcessTournamentAutoStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournaments:process-auto-start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-start tournaments when registration threshold is met';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing tournament auto-start...');

        // Find tournaments that are eligible for auto-start
        $tournaments = Tournament::where('status', 'registration_open')
            ->where('auto_start_enabled', true)
            ->whereNotNull('auto_start_threshold')
            ->with('registrations')
            ->get();

        $started = 0;

        foreach ($tournaments as $tournament) {
            $currentRegistrations = $tournament->registrations()->count();

            // Check if threshold is met
            if ($currentRegistrations >= $tournament->auto_start_threshold) {
                // Close registration and start tournament
                $tournament->update([
                    'status' => 'in_progress',
                    'auto_started_at' => now(),
                ]);

                $this->line("Tournament #{$tournament->id} ({$tournament->name}): Auto-started with {$currentRegistrations} teams");
                $started++;

                // Generate bracket if not already generated
                if ($tournament->matches()->count() === 0) {
                    \Artisan::call('tournaments:generate-bracket', [
                        'tournament_id' => $tournament->id,
                    ]);
                    $this->info("  → Generated bracket");
                }
            }
        }

        $this->info("Auto-started {$started} tournament(s).");

        return Command::SUCCESS;
    }
}
