<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'battlemetrics_id',
        'name',
        'ip',
        'port',
        'query_port',
        'map',
        'scenario',
        'players',
        'max_players',
        'status',
        'country',
        'country_code',
        'game_version',
        'game_build',
        'is_official',
        'is_joinable',
        'is_visible',
        'is_password_protected',
        'battleye_enabled',
        'crossplay_enabled',
        'supported_platforms',
        'direct_join_code',
        'rank',
        'session_started_at',
        'last_updated_at',
        'manager_url',
        'manager_key',
        'is_managed',
    ];

    protected $casts = [
        'players' => 'integer',
        'max_players' => 'integer',
        'port' => 'integer',
        'query_port' => 'integer',
        'rank' => 'integer',
        'is_official' => 'boolean',
        'is_joinable' => 'boolean',
        'is_visible' => 'boolean',
        'is_password_protected' => 'boolean',
        'battleye_enabled' => 'boolean',
        'crossplay_enabled' => 'boolean',
        'supported_platforms' => 'array',
        'session_started_at' => 'datetime',
        'last_updated_at' => 'datetime',
        'is_managed' => 'boolean',
    ];

    /**
     * Get scheduled restarts for this server
     */
    public function scheduledRestarts(): HasMany
    {
        return $this->hasMany(ScheduledRestart::class);
    }

    public function isManaged(): bool
    {
        return $this->is_managed && $this->manager_url;
    }

    public function getManagerUrl(): ?string
    {
        return $this->manager_url;
    }

    public function getManagerKey(): ?string
    {
        return $this->manager_key;
    }

    /**
     * Get all mods for this server
     */
    public function mods(): BelongsToMany
    {
        return $this->belongsToMany(Mod::class, 'server_mod')
            ->withPivot(['load_order', 'is_required'])
            ->withTimestamps()
            ->orderByPivot('load_order');
    }

    /**
     * Get all sessions for this server
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(ServerSession::class)->orderByDesc('started_at');
    }

    /**
     * Get current active session
     */
    public function currentSession(): HasOne
    {
        return $this->hasOne(ServerSession::class)->where('is_current', true);
    }

    /**
     * Get all statistics for this server
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(ServerStatistic::class)->orderByDesc('recorded_at');
    }

    /**
     * Check if server is online
     */
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    /**
     * Get player fill percentage
     */
    public function getPlayerPercentageAttribute(): int
    {
        if ($this->max_players === 0) {
            return 0;
        }

        return (int) round(($this->players / $this->max_players) * 100);
    }

    /**
     * Get full connection address
     */
    public function getConnectionAddressAttribute(): string
    {
        return "{$this->ip}:{$this->port}";
    }

    /**
     * Get statistics for a specific time range
     */
    public function getStatisticsForRange(string $range = '24h'): array
    {
        $hours = match ($range) {
            '6h' => 6,
            '72h' => 72,
            default => 24,
        };

        $startTime = now()->subHours($hours);

        $stats = $this->statistics()
            ->where('recorded_at', '>=', $startTime)
            ->orderBy('recorded_at')
            ->get();

        if ($stats->isEmpty()) {
            return [
                'data' => [],
                'average' => 0,
                'peak' => 0,
                'median' => 0,
                'lowest' => 0,
                'data_points' => 0,
            ];
        }

        $playerCounts = $stats->pluck('players')->toArray();
        sort($playerCounts);

        $count = count($playerCounts);
        $median = $count % 2 === 0
            ? ($playerCounts[$count / 2 - 1] + $playerCounts[$count / 2]) / 2
            : $playerCounts[floor($count / 2)];

        return [
            'data' => $stats->map(fn ($s) => [
                'timestamp' => $s->recorded_at->toIso8601String(),
                'players' => $s->players,
                'max_players' => $s->max_players,
            ])->toArray(),
            'average' => round($stats->avg('players'), 1),
            'peak' => $stats->max('players'),
            'median' => round($median, 1),
            'lowest' => $stats->min('players'),
            'data_points' => $stats->count(),
        ];
    }

    /**
     * Count restarts in the time range
     */
    public function getRestartsCount(string $range = '24h'): int
    {
        $hours = match ($range) {
            '6h' => 6,
            '72h' => 72,
            default => 24,
        };

        return $this->sessions()
            ->where('started_at', '>=', now()->subHours($hours))
            ->count();
    }

    /**
     * Get country flag emoji
     */
    public function getCountryFlagAttribute(): string
    {
        if (! $this->country_code) {
            return '';
        }

        $code = strtoupper($this->country_code);
        $flag = '';
        for ($i = 0; $i < strlen($code); $i++) {
            $flag .= mb_chr(ord($code[$i]) - ord('A') + 0x1F1E6);
        }

        return $flag;
    }

    /**
     * Map BattleMetrics scenario localization keys to human-readable names.
     */
    protected static array $scenarioNameMap = [
        // Conflict
        '#AR-Campaign_ScenarioName_Everon' => 'Conflict - Everon',
        '#AR-Campaign_ScenarioName_NorthCentral' => 'Conflict - Northern Everon',
        '#AR-Campaign_ScenarioName_SWCoast' => 'Conflict - Southern Everon',
        '#AR-Campaign_ScenarioName_Western' => 'Conflict - Western Everon',
        '#AR-Campaign_ScenarioName_Montignac' => 'Conflict - Montignac',
        '#AR-Campaign_ScenarioName_Arland' => 'Conflict - Arland',
        // Conflict: HQ Commander
        '#AR-Campaign_HQC_ScenarioName_Everon' => 'Conflict: HQ Commander - Everon',
        '#AR-Campaign_HQC_ScenarioName_Arland' => 'Conflict: HQ Commander - Arland',
        '#AR-Campaign_HQC_ScenarioName_Cain' => 'Conflict: HQ Commander - Kolguyev',
        // Combat Ops
        '#AR-CombatOps_ScenarioName_Arland' => 'Combat Ops - Arland',
        '#AR-CombatOps_ScenarioName_Everon' => 'Combat Ops - Everon',
        '#AR-CombatOps_ScenarioName_Cain' => 'Combat Ops - Kolguyev',
        '#AR-CombatOps_ScenarioName' => 'Combat Ops',
        // Game Master
        '#AR-GM_ScenarioName_Eden' => 'Game Master - Everon',
        '#AR-GM_ScenarioName_Everon' => 'Game Master - Everon',
        '#AR-GM_ScenarioName_Arland' => 'Game Master - Arland',
        '#AR-GM_ScenarioName_Cain' => 'Game Master - Kolguyev',
        '#AR-GameMaster_ScenarioName_Eden' => 'Game Master - Everon',
        '#AR-GameMaster_ScenarioName_Everon' => 'Game Master - Everon',
        '#AR-GameMaster_ScenarioName_Arland' => 'Game Master - Arland',
        '#AR-GameMaster_ScenarioName_Cain' => 'Game Master - Kolguyev',
        // Capture & Hold
        '#AR-CAH_ScenarioName_Briars' => 'Capture & Hold - Briars',
        '#AR-CAH_ScenarioName_Castle' => 'Capture & Hold - Montfort Castle',
        '#AR-CAH_ScenarioName_ConcretePlant' => 'Capture & Hold - Concrete Plant',
        '#AR-CAH_ScenarioName_Factory' => 'Capture & Hold - Almara Factory',
        '#AR-CAH_ScenarioName_Forest' => "Capture & Hold - Simon's Wood",
        '#AR-CAH_ScenarioName_LeMoule' => 'Capture & Hold - Le Moule',
        '#AR-CAH_ScenarioName_MilitaryBase' => 'Capture & Hold - Camp Blake',
        '#AR-CAH_ScenarioName_Morton' => 'Capture & Hold - Morton',
        // Training / Single-player
        '#AR-Tutorial_ScenarioName' => 'Training',
        '#AR-Training_ScenarioName' => 'Training',
        '#AR-Elimination_ScenarioName' => 'Elimination',
        '#AR-AirSupport_ScenarioName' => 'Air Support',
    ];

    /**
     * Resolve a BattleMetrics scenario localization key to a display name.
     */
    public static function resolveScenarioName(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        // Exact match
        if (isset(static::$scenarioNameMap[$raw])) {
            return static::$scenarioNameMap[$raw];
        }

        // If it doesn't start with #AR-, it's already a readable name
        if (! str_starts_with($raw, '#AR-')) {
            return $raw;
        }

        // Fallback: parse the key into something readable
        // Strip #AR- prefix
        $clean = substr($raw, 4);
        // Remove "ScenarioName" or "_ScenarioName_" filler before converting underscores
        $clean = preg_replace('/[_]?ScenarioName[_]?/', '_', $clean);
        // Replace underscores with spaces
        $clean = str_replace('_', ' ', $clean);
        // Map known game mode prefixes
        $clean = preg_replace('/^Campaign\s*/', 'Conflict - ', $clean);
        $clean = preg_replace('/^CombatOps\s*/', 'Combat Ops - ', $clean);
        $clean = preg_replace('/^GM\s*/', 'Game Master - ', $clean);
        $clean = preg_replace('/^GameMaster\s*/', 'Game Master - ', $clean);
        $clean = preg_replace('/^CAH\s*/', 'Capture & Hold - ', $clean);
        // Clean up extra spaces/dashes
        $clean = preg_replace('/\s*-\s*$/', '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);

        return trim($clean) ?: $raw;
    }

    /**
     * Get human-readable scenario display name.
     */
    public function getScenarioDisplayNameAttribute(): ?string
    {
        return static::resolveScenarioName($this->scenario);
    }

    /**
     * Sync data from BattleMetrics
     */
    public static function syncFromBattleMetrics(array $data): self
    {
        $attributes = $data['attributes'] ?? [];
        $details = $attributes['details'] ?? [];
        $reforger = $details['reforger'] ?? [];

        // Parse supported platforms from reforger data
        $rawPlatforms = $reforger['supportedGameClientTypes'] ?? $details['platforms'] ?? [];
        $platforms = [];
        foreach ($rawPlatforms as $platform) {
            if (str_contains($platform, 'PC')) {
                $platforms[] = 'pc';
            }
            if (str_contains($platform, 'XBL') || str_contains($platform, 'XBOX')) {
                $platforms[] = 'xbox';
            }
            if (str_contains($platform, 'PSN') || str_contains($platform, 'PLAYSTATION')) {
                $platforms[] = 'playstation';
            }
        }
        if (empty($platforms)) {
            $platforms = ['pc'];
        }

        $server = self::updateOrCreate(
            ['battlemetrics_id' => $data['id']],
            [
                'name' => $attributes['name'] ?? 'Unknown',
                'ip' => $attributes['ip'] ?? null,
                'port' => $attributes['port'] ?? null,
                'query_port' => $attributes['portQuery'] ?? null,
                'map' => $details['map'] ?? null,
                'scenario' => $reforger['scenarioName'] ?? $details['scenario'] ?? $details['map'] ?? null,
                'players' => $attributes['players'] ?? 0,
                'max_players' => $attributes['maxPlayers'] ?? 128,
                'status' => $attributes['status'] ?? 'offline',
                'country' => $attributes['country'] ?? null,
                'country_code' => $attributes['country'] ?? null,
                'game_version' => $details['version'] ?? null,
                'game_build' => $details['build'] ?? $details['gameBuild'] ?? null,
                'is_official' => $details['official'] ?? false,
                'battleye_enabled' => $reforger['battlEye'] ?? $details['battleye'] ?? $details['battlEye'] ?? true,
                'crossplay_enabled' => count($platforms) > 1,
                'supported_platforms' => $platforms,
                'direct_join_code' => $details['joinCode'] ?? null,
                'rank' => $attributes['rank'] ?? null,
                'last_updated_at' => now(),
            ]
        );

        return $server;
    }
}
