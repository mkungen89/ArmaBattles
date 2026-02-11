<?php

use App\Http\Controllers\Api\AnticheatController;
use App\Http\Controllers\Api\GameEventController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\PlayerComparisonController;
use Illuminate\Support\Facades\Route;

// Public API endpoints (no auth required)
Route::get('/players/search', [PlayerComparisonController::class, 'searchPlayer'])->name('api.players.search');

// DEPRECATED: Legacy Stats API (use /api/v1/ instead)
// These endpoints will be removed on 2026-06-01
// See /api/v1/ for the current API version
Route::middleware(['auth:sanctum', 'api.rate', 'api.deprecation'])->group(function () {

    // === WRITE ENDPOINTS (från servern) ===
    Route::post('/server-status', [StatsController::class, 'storeServerStatus']);
    Route::post('/player-kills', [StatsController::class, 'storeKill']);
    Route::post('/player-stats', [StatsController::class, 'storePlayerStats']);
    Route::post('/connections', [StatsController::class, 'storeConnection']);
    Route::post('/base-events', [StatsController::class, 'storeBaseEvent']);
    Route::post('/building-events', [StatsController::class, 'storeBuildingEvent']);
    Route::post('/consciousness-events', [StatsController::class, 'storeConsciousnessEvent']);
    Route::post('/group-events', [StatsController::class, 'storeGroupEvent']);
    Route::post('/xp-events', [StatsController::class, 'storeXpEvent']);
    Route::post('/damage-events', [StatsController::class, 'storeDamageEvents']);
    Route::post('/chat-events', [StatsController::class, 'storeChatEvent']);
    Route::post('/editor-actions', [StatsController::class, 'storeEditorAction']);
    Route::post('/gm-sessions', [StatsController::class, 'storeGmSession']);
    Route::post('/player-shooting', [StatsController::class, 'storeShooting']);
    Route::post('/player-distance', [StatsController::class, 'storeDistance']);
    Route::post('/player-healing', [StatsController::class, 'storeHealing']);
    Route::post('/player-grenades', [StatsController::class, 'storeGrenade']);
    Route::post('/player-supplies', [StatsController::class, 'storeSupplies']);
    Route::post('/supply-deliveries', [StatsController::class, 'storeSupplies']);
    Route::post('/player-reports', [StatsController::class, 'storePlayerReport']);

    // Anticheat
    Route::post('/anticheat-events', [AnticheatController::class, 'storeEvent']);
    Route::post('/anticheat-stats', [AnticheatController::class, 'storeStat']);

    // === READ ENDPOINTS (för hemsidan) ===

    // Servrar
    Route::get('/servers', [StatsController::class, 'getServers']);
    Route::get('/servers/{id}', [StatsController::class, 'getServer']);
    Route::get('/servers/{id}/status', [StatsController::class, 'getServerStatus']);
    Route::get('/servers/{id}/players', [StatsController::class, 'getServerPlayers']);

    // Spelare
    Route::get('/players', [StatsController::class, 'getPlayers']);
    Route::get('/players/{id}', [StatsController::class, 'getPlayer']);
    Route::get('/players/{id}/stats', [StatsController::class, 'getPlayerStats']);
    Route::get('/players/{id}/kills', [StatsController::class, 'getPlayerKills']);
    Route::get('/players/{id}/deaths', [StatsController::class, 'getPlayerDeaths']);
    Route::get('/players/{id}/connections', [StatsController::class, 'getPlayerConnections']);
    Route::get('/players/{id}/xp', [StatsController::class, 'getPlayerXp']);
    Route::get('/players/{id}/distance', [StatsController::class, 'getPlayerDistance']);
    Route::get('/players/{id}/shooting', [StatsController::class, 'getPlayerShooting']);

    // Leaderboards
    Route::get('/leaderboards/kills', [StatsController::class, 'getKillsLeaderboard']);
    Route::get('/leaderboards/deaths', [StatsController::class, 'getDeathsLeaderboard']);
    Route::get('/leaderboards/kd', [StatsController::class, 'getKdLeaderboard']);
    Route::get('/leaderboards/playtime', [StatsController::class, 'getPlaytimeLeaderboard']);
    Route::get('/leaderboards/xp', [StatsController::class, 'getXpLeaderboard']);
    Route::get('/leaderboards/distance', [StatsController::class, 'getDistanceLeaderboard']);
    Route::get('/leaderboards/roadkills', [StatsController::class, 'getRoadkillLeaderboard']);

    // Events/Logs
    Route::get('/kills', [StatsController::class, 'getKills']);
    Route::get('/connections', [StatsController::class, 'getConnections']);
    Route::get('/base-events', [StatsController::class, 'getBaseEvents']);
    Route::get('/chat', [StatsController::class, 'getChatMessages']);
    Route::get('/gm-sessions', [StatsController::class, 'getGmSessions']);

    // Statistik/Aggregat
    Route::get('/stats/overview', [StatsController::class, 'getOverview']);
    Route::get('/stats/weapons', [StatsController::class, 'getWeaponStats']);
    Route::get('/stats/factions', [StatsController::class, 'getFactionStats']);
    Route::get('/stats/bases', [StatsController::class, 'getBaseStats']);
});

// Legacy Game Events API with custom token auth (for backwards compatibility)
Route::middleware(['api.token'])->prefix('legacy')->group(function () {
    Route::post('/game-events', [GameEventController::class, 'legacyWebhook']);
});
