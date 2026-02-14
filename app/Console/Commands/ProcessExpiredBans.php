<?php

namespace App\Console\Commands;

use App\Services\BanService;
use Illuminate\Console\Command;

class ProcessExpiredBans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bans:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired temporary bans and automatically unban users';

    /**
     * Execute the console command.
     */
    public function handle(BanService $banService)
    {
        $this->info('Processing expired temporary bans...');

        $count = $banService->processExpiredBans();

        $this->info("Processed {$count} expired ban(s).");

        return Command::SUCCESS;
    }
}
