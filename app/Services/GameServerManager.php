<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GameServerManager
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct(?Server $server = null)
    {
        if ($server && $server->isManaged()) {
            $this->baseUrl = $server->getManagerUrl();
            $this->apiKey = $server->getManagerKey() ?? '';
        } else {
            $this->baseUrl = config('services.gameserver.url') ?? '';
            $this->apiKey = config('services.gameserver.key') ?? '';
        }
    }

    public function forServer(Server $server): static
    {
        $instance = new static;
        $instance->baseUrl = $server->getManagerUrl() ?? config('services.gameserver.url') ?? '';
        $instance->apiKey = $server->getManagerKey() ?? config('services.gameserver.key') ?? '';

        return $instance;
    }

    protected function client(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->baseUrl($this->baseUrl)
            ->timeout(30)
            ->throw();
    }

    public function health(): array
    {
        return $this->client()->get('/manage/health')->json();
    }

    public function status(): array
    {
        return $this->client()->get('/manage/status')->json();
    }

    public function getArmaConfig(): array
    {
        return $this->client()->get('/manage/config/arma')->json();
    }

    public function updateArmaConfig(array $config): array
    {
        return $this->client()->put('/manage/config/arma', ['config' => $config])->json();
    }

    public function getStatsConfig(): array
    {
        return $this->client()->get('/manage/config/stats')->json();
    }

    public function updateStatsConfig(array $config): array
    {
        return $this->client()->put('/manage/config/stats', ['config' => $config])->json();
    }

    public function getMods(): array
    {
        return $this->client()->get('/manage/mods')->json();
    }

    public function addMod(string $modId, string $name, ?string $version = null): array
    {
        $data = ['modId' => $modId, 'name' => $name];
        if ($version) {
            $data['version'] = $version;
        }

        return $this->client()->post('/manage/mods', $data)->json();
    }

    public function removeMod(string $modId): array
    {
        return $this->client()->delete("/manage/mods/{$modId}")->json();
    }

    public function restartArma(): array
    {
        return $this->client()->post('/manage/services/arma/restart')->json();
    }

    public function stopArma(): array
    {
        return $this->client()->post('/manage/services/arma/stop')->json();
    }

    public function startArma(): array
    {
        return $this->client()->post('/manage/services/arma/start')->json();
    }

    public function restartStats(): array
    {
        return $this->client()->post('/manage/services/stats/restart')->json();
    }

    public function startUpdate(): array
    {
        return $this->client()->post('/manage/server/update')->json();
    }

    public function updateStatus(): array
    {
        return $this->client()->get('/manage/server/update/status')->json();
    }

    public function logs(string $type, int $lines = 100): array
    {
        return $this->client()->get("/manage/logs/{$type}", ['lines' => $lines])->json();
    }

    public function anticheat(): array
    {
        return $this->client()->get('/manage/anticheat')->json();
    }

    public function players(): array
    {
        return $this->client()->get('/manage/players')->json();
    }

    public function kickPlayer(int $playerId, string $reason = 'Kicked by admin'): array
    {
        return $this->client()->post('/manage/players/kick', [
            'playerId' => $playerId,
            'reason' => $reason,
        ])->json();
    }

    public function banPlayer(int $playerId, int $minutes = 0, string $reason = 'Banned by admin'): array
    {
        return $this->client()->post('/manage/players/ban', [
            'playerId' => $playerId,
            'minutes' => $minutes,
            'reason' => $reason,
        ])->json();
    }

    public function banPlayerByGuid(string $guid, int $minutes = 0, string $reason = 'Banned by admin'): array
    {
        return $this->client()->post('/manage/players/ban-guid', [
            'guid' => $guid,
            'minutes' => $minutes,
            'reason' => $reason,
        ])->json();
    }

    public function unbanPlayer(int|string $banIndex): array
    {
        return $this->client()->post('/manage/players/unban', [
            'banIndex' => $banIndex,
        ])->json();
    }

    public function bans(): array
    {
        return $this->client()->get('/manage/players/bans')->json();
    }

    public function broadcast(string $message): array
    {
        return $this->client()->post('/manage/players/broadcast', [
            'message' => $message,
        ])->json();
    }
}
