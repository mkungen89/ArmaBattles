<?php

namespace App\Console\Commands;

use App\Services\RatingCalculationService;
use Illuminate\Console\Command;

class CalculateRatings extends Command
{
    protected $signature = 'ratings:calculate';

    protected $description = 'Process queued rated kills and update Glicko-2 ratings';

    public function handle(RatingCalculationService $service): int
    {
        $this->info('Processing rated kills queue...');

        $result = $service->processRatingPeriod();

        $this->info("Processed {$result['processed']} kills, updated {$result['players_updated']} player ratings.");

        return Command::SUCCESS;
    }
}
