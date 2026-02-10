<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Log;

class ModUpdateCheckService
{
    public function __construct(
        protected GameServerManager $manager,
        protected ReforgerWorkshopService $workshop,
    ) {}

    public function checkForServer(Server $server): array
    {
        $mgr = $server->isManaged()
            ? $this->manager->forServer($server)
            : $this->manager;

        $installedMods = $mgr->getMods();
        $modList = $installedMods['mods'] ?? $installedMods['data'] ?? $installedMods;

        if (! is_array($modList)) {
            return [];
        }

        $results = [];

        foreach ($modList as $mod) {
            $modId = $mod['modId'] ?? $mod['id'] ?? null;
            $modName = $mod['name'] ?? 'Unknown';
            $installedVersion = $mod['version'] ?? $mod['currentVersion'] ?? null;

            if (! $modId) {
                continue;
            }

            try {
                $workshopData = $this->workshop->getMod($modId, $modName);
                $latestVersion = $workshopData['version'] ?? null;

                $hasUpdate = false;
                if ($installedVersion && $latestVersion && $installedVersion !== $latestVersion) {
                    $hasUpdate = true;
                }

                $results[] = [
                    'mod_id' => $modId,
                    'name' => $workshopData['name'] ?? $modName,
                    'installed_version' => $installedVersion,
                    'latest_version' => $latestVersion,
                    'has_update' => $hasUpdate,
                    'workshop_url' => $this->workshop->buildWorkshopUrl($modId, $workshopData['name'] ?? $modName),
                ];
            } catch (\Exception $e) {
                Log::warning("Failed to check mod update for {$modId}: {$e->getMessage()}");
                $results[] = [
                    'mod_id' => $modId,
                    'name' => $modName,
                    'installed_version' => $installedVersion,
                    'latest_version' => null,
                    'has_update' => false,
                    'workshop_url' => $this->workshop->buildWorkshopUrl($modId, $modName),
                    'error' => 'Could not check for updates',
                ];
            }
        }

        return $results;
    }
}
