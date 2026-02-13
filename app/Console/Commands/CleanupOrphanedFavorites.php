<?php

namespace App\Console\Commands;

use App\Models\Favorite;
use App\Models\Server;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;

class CleanupOrphanedFavorites extends Command
{
    protected $signature = 'favorites:cleanup';

    protected $description = 'Remove orphaned favorites (pointing to deleted models)';

    public function handle(): int
    {
        $this->info('Cleaning up orphaned favorites...');

        $deleted = 0;

        // Clean up player favorites pointing to deleted users
        $orphanedPlayers = Favorite::where('favoritable_type', User::class)
            ->whereNotIn('favoritable_id', User::pluck('id'))
            ->get();

        if ($orphanedPlayers->isNotEmpty()) {
            $count = $orphanedPlayers->count();
            Favorite::where('favoritable_type', User::class)
                ->whereNotIn('favoritable_id', User::pluck('id'))
                ->delete();
            $this->line("  - Removed {$count} orphaned player favorites");
            $deleted += $count;
        }

        // Clean up team favorites pointing to deleted teams
        $orphanedTeams = Favorite::where('favoritable_type', Team::class)
            ->whereNotIn('favoritable_id', Team::pluck('id'))
            ->get();

        if ($orphanedTeams->isNotEmpty()) {
            $count = $orphanedTeams->count();
            Favorite::where('favoritable_type', Team::class)
                ->whereNotIn('favoritable_id', Team::pluck('id'))
                ->delete();
            $this->line("  - Removed {$count} orphaned team favorites");
            $deleted += $count;
        }

        // Clean up server favorites pointing to deleted servers
        $orphanedServers = Favorite::where('favoritable_type', Server::class)
            ->whereNotIn('favoritable_id', Server::pluck('id'))
            ->get();

        if ($orphanedServers->isNotEmpty()) {
            $count = $orphanedServers->count();
            Favorite::where('favoritable_type', Server::class)
                ->whereNotIn('favoritable_id', Server::pluck('id'))
                ->delete();
            $this->line("  - Removed {$count} orphaned server favorites");
            $deleted += $count;
        }

        if ($deleted === 0) {
            $this->info('No orphaned favorites found.');
        } else {
            $this->info("✓ Cleaned up {$deleted} orphaned favorites.");
        }

        return Command::SUCCESS;
    }
}
