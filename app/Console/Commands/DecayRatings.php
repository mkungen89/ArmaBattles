<?php

namespace App\Console\Commands;

use App\Services\RatingCalculationService;
use Illuminate\Console\Command;

class DecayRatings extends Command
{
    protected $signature = 'ratings:decay {--days=14 : Days of inactivity before RD increases}';

    protected $description = 'Apply rating deviation increase to inactive competitive players';

    public function handle(RatingCalculationService $service): int
    {
        $days = (int) $this->option('days');

        $this->info("Applying decay for players inactive > {$days} days...");

        $decayed = $service->applyInactivityDecay($days);

        $this->info("Increased RD for {$decayed} inactive players.");

        return Command::SUCCESS;
    }
}
