<?php

namespace App\Console\Commands;

use App\Models\Mod;
use App\Models\Server;
use App\Services\ReforgerWorkshopService;
use Illuminate\Console\Command;

class SyncModsFromWorkshop extends Command
{
    protected $signature = 'mods:sync {--server= : BattleMetrics server ID to sync mods for}';
    protected $description = 'Sync mod information from Reforger Workshop';

    public function __construct(
        protected ReforgerWorkshopService $workshop
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $serverId = $this->option('server');

        if ($serverId) {
            $server = Server::where('battlemetrics_id', $serverId)->first();

            if (!$server) {
                $this->error("Server not found: {$serverId}");
                return 1;
            }

            $this->info("Syncing mods for server: {$server->name}");
            $mods = $server->mods;
        } else {
            $this->info("Syncing all mods from database...");
            $mods = Mod::all();
        }

        if ($mods->isEmpty()) {
            $this->warn("No mods found to sync");
            return 0;
        }

        $bar = $this->output->createProgressBar($mods->count());
        $bar->start();

        $synced = 0;
        $failed = 0;

        foreach ($mods as $mod) {
            if (!$mod->workshop_id) {
                $bar->advance();
                continue;
            }

            $updated = $this->workshop->syncMod($mod->workshop_id, $mod->name);

            if ($updated && $updated->version) {
                $synced++;
            } else {
                $failed++;
                $this->line(" <comment>Could not fetch workshop data for: {$mod->name}</comment>");
            }

            $bar->advance();

            // Small delay to avoid rate limiting
            usleep(200000); // 200ms
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Sync complete!");
        $this->info("  Synced: {$synced}");
        $this->info("  Failed: {$failed}");

        return 0;
    }
}
