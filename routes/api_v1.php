<?php

use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\AnticheatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Version 1 of the Game Statistics API. All endpoints use Sanctum auth
| and rate limiting based on token type.
|
| Rate Limits:
| - Standard: 60 req/min
| - High-Volume: 180 req/min
| - Premium: 300 req/min
|
*/

// === PUBLIC ENDPOINTS (no auth) ===
Route::get('/heatmap', [StatsController::class, 'getHeatmapData'])
    ->name('api.v1.heatmap');

Route::middleware(['auth:sanctum', 'api.rate'])->group(function () {

    // === WRITE ENDPOINTS (from game server) ===

    // Server Status
    Route::post('/server-status', [StatsController::class, 'storeServerStatus'])
        ->name('api.v1.server-status');

    // Player Events
    Route::post('/player-kills', [StatsController::class, 'storeKill'])
        ->name('api.v1.player-kills');
    Route::post('/player-stats', [StatsController::class, 'storePlayerStats'])
        ->name('api.v1.player-stats');
    Route::post('/connections', [StatsController::class, 'storeConnection'])
        ->name('api.v1.connections');

    // Game Events
    Route::post('/base-events', [StatsController::class, 'storeBaseEvent'])
        ->name('api.v1.base-events');
    Route::post('/building-events', [StatsController::class, 'storeBuildingEvent'])
        ->name('api.v1.building-events');
    Route::post('/consciousness-events', [StatsController::class, 'storeConsciousnessEvent'])
        ->name('api.v1.consciousness-events');
    Route::post('/group-events', [StatsController::class, 'storeGroupEvent'])
        ->name('api.v1.group-events');
    Route::post('/xp-events', [StatsController::class, 'storeXpEvent'])
        ->name('api.v1.xp-events');
    Route::post('/damage-events', [StatsController::class, 'storeDamageEvents'])
        ->name('api.v1.damage-events');
    Route::post('/chat-events', [StatsController::class, 'storeChatEvent'])
        ->name('api.v1.chat-events');

    // GM/Editor Events
    Route::post('/editor-actions', [StatsController::class, 'storeEditorAction'])
        ->name('api.v1.editor-actions');
    Route::post('/gm-sessions', [StatsController::class, 'storeGmSession'])
        ->name('api.v1.gm-sessions');

    // Player Actions (ReforgerJS)
    Route::post('/player-shooting', [StatsController::class, 'storeShooting'])
        ->name('api.v1.player-shooting');
    Route::post('/player-distance', [StatsController::class, 'storeDistance'])
        ->name('api.v1.player-distance');
    Route::post('/player-healing', [StatsController::class, 'storeHealing'])
        ->name('api.v1.player-healing');
    Route::post('/player-grenades', [StatsController::class, 'storeGrenade'])
        ->name('api.v1.player-grenades');
    Route::post('/player-supplies', [StatsController::class, 'storeSupplies'])
        ->name('api.v1.player-supplies');
    Route::post('/supply-deliveries', [StatsController::class, 'storeSupplies'])
        ->name('api.v1.supply-deliveries');
    Route::post('/player-reports', [StatsController::class, 'storePlayerReport'])
        ->name('api.v1.player-reports');

    // Anti-Cheat
    Route::post('/anticheat-events', [AnticheatController::class, 'storeEvent'])
        ->name('api.v1.anticheat-events');
    Route::post('/anticheat-stats', [AnticheatController::class, 'storeStat'])
        ->name('api.v1.anticheat-stats');

    // === READ ENDPOINTS (for website) ===

    // Servers
    Route::prefix('servers')->name('api.v1.servers.')->group(function () {
        Route::get('/', [StatsController::class, 'getServers'])->name('index');
        Route::get('/{id}', [StatsController::class, 'getServer'])->name('show');
        Route::get('/{id}/status', [StatsController::class, 'getServerStatus'])->name('status');
        Route::get('/{id}/players', [StatsController::class, 'getServerPlayers'])->name('players');
    });

    // Players
    Route::prefix('players')->name('api.v1.players.')->group(function () {
        Route::get('/', [StatsController::class, 'getPlayers'])->name('index');
        Route::get('/{id}', [StatsController::class, 'getPlayer'])->name('show');
        Route::get('/{id}/stats', [StatsController::class, 'getPlayerStats'])->name('stats');
        Route::get('/{id}/kills', [StatsController::class, 'getPlayerKills'])->name('kills');
        Route::get('/{id}/deaths', [StatsController::class, 'getPlayerDeaths'])->name('deaths');
        Route::get('/{id}/connections', [StatsController::class, 'getPlayerConnections'])->name('connections');
        Route::get('/{id}/xp', [StatsController::class, 'getPlayerXp'])->name('xp');
        Route::get('/{id}/distance', [StatsController::class, 'getPlayerDistance'])->name('distance');
        Route::get('/{id}/shooting', [StatsController::class, 'getPlayerShooting'])->name('shooting');
    });

    // Leaderboards
    Route::prefix('leaderboards')->name('api.v1.leaderboards.')->group(function () {
        Route::get('/kills', [StatsController::class, 'getKillsLeaderboard'])->name('kills');
        Route::get('/deaths', [StatsController::class, 'getDeathsLeaderboard'])->name('deaths');
        Route::get('/kd', [StatsController::class, 'getKdLeaderboard'])->name('kd');
        Route::get('/playtime', [StatsController::class, 'getPlaytimeLeaderboard'])->name('playtime');
        Route::get('/xp', [StatsController::class, 'getXpLeaderboard'])->name('xp');
        Route::get('/distance', [StatsController::class, 'getDistanceLeaderboard'])->name('distance');
        Route::get('/roadkills', [StatsController::class, 'getRoadkillLeaderboard'])->name('roadkills');
    });

    // Events/Logs
    Route::prefix('events')->name('api.v1.events.')->group(function () {
        Route::get('/kills', [StatsController::class, 'getKills'])->name('kills');
        Route::get('/connections', [StatsController::class, 'getConnections'])->name('connections');
        Route::get('/bases', [StatsController::class, 'getBaseEvents'])->name('bases');
        Route::get('/chat', [StatsController::class, 'getChatMessages'])->name('chat');
        Route::get('/gm-sessions', [StatsController::class, 'getGmSessions'])->name('gm-sessions');
    });

    // Stats/Aggregates
    Route::prefix('stats')->name('api.v1.stats.')->group(function () {
        Route::get('/overview', [StatsController::class, 'getOverview'])->name('overview');
        Route::get('/weapons', [StatsController::class, 'getWeaponStats'])->name('weapons');
        Route::get('/factions', [StatsController::class, 'getFactionStats'])->name('factions');
        Route::get('/bases', [StatsController::class, 'getBaseStats'])->name('bases');
    });
});
