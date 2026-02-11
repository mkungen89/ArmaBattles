<?php

namespace App\Console\Commands;

use App\Services\PlayerLevelService;
use Illuminate\Console\Command;

class RecalculatePlayerLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levels:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate all player levels based on XP and achievement points';

    /**
     * Execute the console command.
     */
    public function handle(PlayerLevelService $levelService)
    {
        $this->info('Recalculating player levels...');

        $updated = $levelService->recalculateAllLevels();

        $this->info("Successfully updated {$updated} player levels!");

        return Command::SUCCESS;
    }
}
