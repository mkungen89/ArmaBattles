<?php

namespace App\Console\Commands;

use App\Services\DiscordPresenceService;
use Illuminate\Console\Command;

class RefreshDiscordPresences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:refresh-presences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh stale Discord Rich Presence records';

    /**
     * Execute the console command.
     */
    public function handle(DiscordPresenceService $presenceService): int
    {
        $this->info('Refreshing stale Discord presences...');

        $updated = $presenceService->refreshStalePresences();

        $this->info("Refreshed {$updated} stale presence(s).");

        return Command::SUCCESS;
    }
}
