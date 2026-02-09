<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SteamController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\PlayerProfileController;
use App\Http\Controllers\PlayerSearchController;
use App\Http\Controllers\PlayerComparisonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerDetailController;
use App\Http\Controllers\ServerStatsController;
use App\Http\Controllers\ServerWidgetController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamComparisonController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\WeaponStatsController;
use App\Http\Controllers\KillFeedController;
use App\Http\Controllers\ActivityFeedController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AnticheatAdminController;
use App\Http\Controllers\Admin\GameStatsAdminController;
use App\Http\Controllers\Admin\TournamentAdminController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\TeamAdminController;
use App\Http\Controllers\Admin\ServerManagerController;
use App\Http\Controllers\Admin\NewsAdminController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('profile');
    }

    try {
        $latestNews = \App\Models\NewsArticle::published()
            ->orderByDesc('published_at')
            ->with('author')
            ->withCount('hoorahs')
            ->limit(3)
            ->get();
    } catch (\Exception $e) {
        $latestNews = collect();
    }
    return view('welcome', compact('latestNews'));
})->name('home');

Route::get('/rules', function () {
    return view('rules');
})->name('rules');

Route::get('/api/docs', fn () => view('api.docs'))->name('api.docs');

Route::get('/faq', function () {
    return view('pages.faq');
})->name('faq');

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

// Player search & comparison (must be before /players/{user})
Route::get('/players/compare/head-to-head', [PlayerComparisonController::class, 'headToHead'])->name('players.compare.h2h');
Route::get('/players/compare', [PlayerComparisonController::class, 'index'])->name('players.compare');
Route::get('/players', [PlayerSearchController::class, 'search'])->name('players.search');

// Public player profiles
Route::get('/players/{user}', [PlayerProfileController::class, 'show'])->name('players.show');

// Leaderboard
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');

// Achievements
Route::get('/achievements', [\App\Http\Controllers\AchievementController::class, 'index'])->name('achievements.index');
Route::post('/achievements/showcase', [\App\Http\Controllers\AchievementController::class, 'updateShowcase'])->middleware('auth')->name('achievements.showcase.update');
Route::get('/achievements/showcase/{playerUuid}', [\App\Http\Controllers\AchievementController::class, 'getShowcase'])->name('achievements.showcase.get');

// Reputation
Route::get('/reputation', [\App\Http\Controllers\ReputationController::class, 'index'])->name('reputation.index');
Route::get('/reputation/{user}', [\App\Http\Controllers\ReputationController::class, 'show'])->name('reputation.show');
Route::post('/reputation/{user}/vote', [\App\Http\Controllers\ReputationController::class, 'vote'])->middleware('auth')->name('reputation.vote');
Route::delete('/reputation/{user}/vote', [\App\Http\Controllers\ReputationController::class, 'removeVote'])->middleware('auth')->name('reputation.remove-vote');

// Scrims (Practice Matches)
Route::middleware('auth')->prefix('scrims')->name('scrims.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ScrimController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\ScrimController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\ScrimController::class, 'store'])->name('store');
    Route::get('/{scrim}', [\App\Http\Controllers\ScrimController::class, 'show'])->name('show');
    Route::post('/invitations/{invitation}/accept', [\App\Http\Controllers\ScrimController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/decline', [\App\Http\Controllers\ScrimController::class, 'decline'])->name('invitations.decline');
    Route::post('/{scrim}/cancel', [\App\Http\Controllers\ScrimController::class, 'cancel'])->name('cancel');
    Route::post('/{scrim}/report', [\App\Http\Controllers\ScrimController::class, 'reportResult'])->name('report');
});

// Content Creators
Route::prefix('creators')->name('content-creators.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ContentCreatorController::class, 'index'])->name('index');
    Route::get('/{contentCreator}', [\App\Http\Controllers\ContentCreatorController::class, 'show'])->name('show');

    Route::middleware('auth')->group(function () {
        Route::get('/register/new', [\App\Http\Controllers\ContentCreatorController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\ContentCreatorController::class, 'store'])->name('store');
        Route::get('/{contentCreator}/edit', [\App\Http\Controllers\ContentCreatorController::class, 'edit'])->name('edit');
        Route::put('/{contentCreator}', [\App\Http\Controllers\ContentCreatorController::class, 'update'])->name('update');
        Route::delete('/{contentCreator}', [\App\Http\Controllers\ContentCreatorController::class, 'destroy'])->name('destroy');
        Route::post('/{contentCreator}/verify', [\App\Http\Controllers\ContentCreatorController::class, 'verify'])->name('verify');
        Route::post('/{contentCreator}/unverify', [\App\Http\Controllers\ContentCreatorController::class, 'unverify'])->name('unverify');
    });
});

// Highlight Clips
Route::prefix('clips')->name('clips.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HighlightClipController::class, 'index'])->name('index');
    Route::get('/{clip}', [\App\Http\Controllers\HighlightClipController::class, 'show'])->name('show');

    Route::middleware('auth')->group(function () {
        Route::get('/submit/new', [\App\Http\Controllers\HighlightClipController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HighlightClipController::class, 'store'])->name('store');
        Route::post('/{clip}/vote', [\App\Http\Controllers\HighlightClipController::class, 'vote'])->name('vote');
        Route::delete('/{clip}/vote', [\App\Http\Controllers\HighlightClipController::class, 'unvote'])->name('unvote');
        Route::post('/{clip}/feature', [\App\Http\Controllers\HighlightClipController::class, 'feature'])->name('feature');
        Route::post('/{clip}/unfeature', [\App\Http\Controllers\HighlightClipController::class, 'unfeature'])->name('unfeature');
        Route::delete('/{clip}', [\App\Http\Controllers\HighlightClipController::class, 'destroy'])->name('destroy');
    });
});

// Discord Rich Presence
Route::prefix('discord')->name('discord.')->middleware('auth')->group(function () {
    Route::get('/presence/settings', [\App\Http\Controllers\DiscordPresenceController::class, 'settings'])->name('presence.settings');
    Route::post('/presence/enable', [\App\Http\Controllers\DiscordPresenceController::class, 'enable'])->name('presence.enable');
    Route::delete('/presence/disable', [\App\Http\Controllers\DiscordPresenceController::class, 'disable'])->name('presence.disable');
    Route::get('/presence/current', [\App\Http\Controllers\DiscordPresenceController::class, 'current'])->name('presence.current');
    Route::post('/presence/activity', [\App\Http\Controllers\DiscordPresenceController::class, 'updateActivity'])->name('presence.activity');
});

// Public Discord API (for Discord bot integration)
Route::get('/api/discord/presences/active', [\App\Http\Controllers\DiscordPresenceController::class, 'active'])->name('api.discord.presences');

// Weapon stats
Route::get('/weapons', [WeaponStatsController::class, 'index'])->name('weapons.index');

// Kill feed
Route::get('/kill-feed', [KillFeedController::class, 'index'])->name('kill-feed');

// Public API endpoints (no auth)
Route::get('/api/player-search', [PlayerSearchController::class, 'apiSearch'])->name('api.player-search');
Route::get('/api/players/search', [PlayerComparisonController::class, 'searchPlayer'])->name('api.players.search');
Route::get('/api/activity-feed', [ActivityFeedController::class, 'recent'])->name('api.activity-feed');
Route::get('/api/kill-feed', [KillFeedController::class, 'api'])->name('api.kill-feed');

// Auth routes
Route::middleware(['guest', 'throttle:auth'])->group(function () {
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::prefix('auth/steam')->middleware('throttle:auth')->group(function () {
    Route::get('/', [SteamController::class, 'redirect'])->name('auth.steam');
    Route::get('/callback', [SteamController::class, 'callback'])->name('auth.steam.callback');
});

Route::post('/logout', [SteamController::class, 'logout'])->name('logout')->middleware('auth');

// Two-Factor Authentication Challenge (guest with session)
Route::middleware('throttle:auth')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verifyChallenge'])->name('two-factor.verify');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::post('/profile/link-arma-id', [ProfileController::class, 'linkArmaId'])->name('profile.link-arma-id');
    Route::delete('/profile/unlink-arma-id', [ProfileController::class, 'unlinkArmaId'])->name('profile.unlink-arma-id');
    Route::post('/profile/link-discord', [ProfileController::class, 'linkDiscord'])->name('profile.link-discord');
    Route::delete('/profile/unlink-discord', [ProfileController::class, 'unlinkDiscord'])->name('profile.unlink-discord');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.upload-avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');
    Route::post('/profile/social-links', [ProfileController::class, 'updateSocialLinks'])->name('profile.update-social-links');
    Route::post('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.update-settings');

    // Favorites
    Route::get('/favorites', [\App\Http\Controllers\FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // Stats Export
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/player/{uuid}/stats', [\App\Http\Controllers\StatsExportController::class, 'exportPlayerStats'])->name('player.stats');
        Route::get('/player/{uuid}/history', [\App\Http\Controllers\StatsExportController::class, 'exportMatchHistory'])->name('player.history');
        Route::get('/leaderboard/{type}/csv', [\App\Http\Controllers\StatsExportController::class, 'exportLeaderboard'])->name('leaderboard.csv');
        Route::get('/leaderboard/{type}/json', [\App\Http\Controllers\StatsExportController::class, 'exportLeaderboardJson'])->name('leaderboard.json');
    });

    // Two-Factor Authentication Management
    Route::post('/profile/two-factor', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::get('/profile/two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/profile/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/profile/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::get('/profile/two-factor/recovery-codes', [TwoFactorController::class, 'showRecoveryCodes'])->name('two-factor.recovery-codes');
    Route::post('/profile/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.regenerate-recovery-codes');

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    });
});

// Server info page
Route::get('/server', [ServerController::class, 'info'])->name('server.info');

// API routes for server data
Route::prefix('api/server')->group(function () {
    Route::get('/status', [ServerController::class, 'status'])->name('api.server.status');
    Route::get('/players', [ServerController::class, 'players'])->name('api.server.players');
    Route::get('/history', [ServerController::class, 'history'])->name('api.server.history');
});

// Server detail page (ArmaHQ style)
Route::prefix('servers')->group(function () {
    Route::get('/{serverId}/stats', [ServerStatsController::class, 'show'])->name('servers.stats');
    Route::get('/{serverId}/stats/data', [ServerStatsController::class, 'apiData'])->name('servers.stats.data');
    Route::get('/{serverId}', [ServerDetailController::class, 'show'])->name('servers.show');
    Route::get('/{serverId}/history', [ServerDetailController::class, 'history'])->name('servers.history');
    Route::get('/{serverId}/sessions', [ServerDetailController::class, 'sessions'])->name('servers.sessions');
    Route::get('/{serverId}/status', [ServerDetailController::class, 'status'])->name('servers.status');
    Route::get('/{serverId}/mods/json', [ServerDetailController::class, 'modsJson'])->name('servers.mods.json');
    Route::get('/{serverId}/mods/download', [ServerDetailController::class, 'downloadMods'])->name('servers.mods.download');
    Route::get('/{serverId}/heatmap', [ServerDetailController::class, 'heatmap'])->name('servers.heatmap');
    Route::get('/{serverId}/heatmap/players', [ServerDetailController::class, 'heatmapPlayers'])->name('servers.heatmap.players');
    Route::get('/{serverId}/debug', [ServerDetailController::class, 'debug'])->name('servers.debug');
    Route::get('/{server}/widget', [ServerWidgetController::class, 'widget'])->name('servers.widget');
    Route::get('/{server}/widget/api', [ServerWidgetController::class, 'api'])->name('servers.widget.api');
    Route::get('/{server}/embed', [ServerWidgetController::class, 'embed'])->name('servers.embed');
});

// Public Tournament Routes
Route::prefix('tournaments')->group(function () {
    Route::get('/', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
    Route::get('/{tournament}/bracket', [TournamentController::class, 'bracket'])->name('tournaments.bracket');
    Route::get('/{tournament}/standings', [TournamentController::class, 'standings'])->name('tournaments.standings');
    Route::get('/{tournament}/matches/{match}', [TournamentController::class, 'matchDetails'])->name('tournaments.match');
});

// Public News Routes
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{article}', [NewsController::class, 'show'])->name('news.show');

// Authenticated News Routes
Route::middleware('auth')->group(function () {
    Route::post('/news/{article}/comment', [NewsController::class, 'storeComment'])->name('news.comment');
    Route::delete('/news/comments/{comment}', [NewsController::class, 'destroyComment'])->name('news.comment.destroy');
    Route::post('/news/{article}/hoorah', [NewsController::class, 'toggleHoorah'])->name('news.hoorah');
});

// Public Team Routes
Route::prefix('teams')->group(function () {
    Route::get('/', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/compare', [TeamComparisonController::class, 'index'])->name('teams.compare');
    Route::get('/create', [TeamController::class, 'create'])->name('teams.create')->middleware('auth');
    Route::get('/my', [TeamController::class, 'myTeam'])->name('teams.my')->middleware('auth');
    Route::get('/{team}', [TeamController::class, 'show'])->name('teams.show');
});

// Authenticated Team Routes
Route::middleware('auth')->prefix('teams')->group(function () {
    Route::post('/', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
    Route::put('/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::post('/{team}/invite', [TeamController::class, 'invite'])->name('teams.invite');
    Route::post('/{team}/leave', [TeamController::class, 'leaveTeam'])->name('teams.leave');
    Route::post('/{team}/disband', [TeamController::class, 'disband'])->name('teams.disband');
    Route::delete('/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
    Route::post('/{team}/register', [TeamController::class, 'registerForTournament'])->name('teams.register');
    Route::post('/{team}/withdraw/{tournament}', [TeamController::class, 'withdrawFromTournament'])->name('teams.withdraw');
    Route::post('/{team}/transfer-captain', [TeamController::class, 'transferCaptain'])->name('teams.transfer-captain');
    Route::post('/{team}/members/{member}/kick', [TeamController::class, 'kickMember'])->name('teams.members.kick');
    Route::post('/{team}/members/{member}/promote', [TeamController::class, 'promoteMember'])->name('teams.members.promote');
    Route::post('/{team}/members/{member}/demote', [TeamController::class, 'demoteMember'])->name('teams.members.demote');
    Route::post('/{team}/invitations/{invitation}/cancel', [TeamController::class, 'cancelInvitation'])->name('teams.invitations.cancel');

    // Application routes
    Route::post('/{team}/apply', [TeamController::class, 'apply'])->name('teams.apply');
    Route::post('/{team}/applications/{application}/accept', [TeamController::class, 'acceptApplication'])->name('teams.applications.accept');
    Route::post('/{team}/applications/{application}/reject', [TeamController::class, 'rejectApplication'])->name('teams.applications.reject');

    // Social links
    Route::post('/{team}/social-links', [TeamController::class, 'updateSocialLinks'])->name('teams.social-links');

    // Recruitment settings
    Route::post('/{team}/recruitment', [TeamController::class, 'updateRecruitment'])->name('teams.recruitment');

    // Player search for invitations
    Route::get('/{team}/search-players', [TeamController::class, 'searchPlayers'])->name('teams.search-players');
});

// Team Invitation Routes (no team prefix needed)
Route::middleware('auth')->group(function () {
    Route::post('/invitations/{invitation}/accept', [TeamController::class, 'acceptInvitation'])->name('teams.invitations.accept');
    Route::post('/invitations/{invitation}/decline', [TeamController::class, 'declineInvitation'])->name('teams.invitations.decline');
    Route::post('/applications/{application}/cancel', [TeamController::class, 'cancelApplication'])->name('teams.applications.cancel');
});

// Match Routes
Route::middleware('auth')->prefix('matches')->group(function () {
    Route::post('/{match}/check-in', [MatchController::class, 'checkIn'])->name('matches.check-in');
    Route::post('/{match}/schedule', [MatchController::class, 'proposeSchedule'])->name('matches.schedule');
});

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{user}/ban', [AdminController::class, 'banUser'])->name('admin.users.ban');
    Route::post('/users/{user}/unban', [AdminController::class, 'unbanUser'])->name('admin.users.unban');
    Route::post('/users/{user}/reset-2fa', [AdminController::class, 'resetTwoFactor'])->name('admin.users.reset-2fa');
    Route::get('/servers', [AdminController::class, 'servers'])->name('admin.servers');
    Route::post('/servers', [AdminController::class, 'storeServer'])->name('admin.servers.store');
    Route::delete('/servers/{server}', [AdminController::class, 'destroyServer'])->name('admin.servers.destroy');
    Route::post('/servers/{server}/sync-mods', [AdminController::class, 'syncMods'])->name('admin.servers.sync-mods');
    Route::post('/cache/clear', [AdminController::class, 'clearCache'])->name('admin.cache.clear');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::get('/audit-log', [AdminController::class, 'auditLog'])->name('admin.audit-log');

    // Tournament Admin Routes
    Route::prefix('tournaments')->group(function () {
        Route::get('/', [TournamentAdminController::class, 'index'])->name('admin.tournaments.index');
        Route::get('/create', [TournamentAdminController::class, 'create'])->name('admin.tournaments.create');
        Route::post('/', [TournamentAdminController::class, 'store'])->name('admin.tournaments.store');
        Route::get('/{tournament}', [TournamentAdminController::class, 'show'])->name('admin.tournaments.show');
        Route::get('/{tournament}/edit', [TournamentAdminController::class, 'edit'])->name('admin.tournaments.edit');
        Route::put('/{tournament}', [TournamentAdminController::class, 'update'])->name('admin.tournaments.update');
        Route::post('/{tournament}/status', [TournamentAdminController::class, 'updateStatus'])->name('admin.tournaments.status');
        Route::delete('/{tournament}', [TournamentAdminController::class, 'destroy'])->name('admin.tournaments.destroy');

        // Registration Management
        Route::get('/{tournament}/registrations', [TournamentAdminController::class, 'registrations'])->name('admin.tournaments.registrations');
        Route::post('/{tournament}/seeding', [TournamentAdminController::class, 'updateSeeding'])->name('admin.tournaments.seeding');

        // Bracket Management
        Route::post('/{tournament}/generate-bracket', [TournamentAdminController::class, 'generateBracket'])->name('admin.tournaments.generate-bracket');
        Route::post('/{tournament}/reset-bracket', [TournamentAdminController::class, 'resetBracket'])->name('admin.tournaments.reset-bracket');
        Route::post('/{tournament}/next-swiss-round', [TournamentAdminController::class, 'generateNextSwissRound'])->name('admin.tournaments.next-swiss-round');

        // Match Management
        Route::get('/{tournament}/matches', [TournamentAdminController::class, 'matches'])->name('admin.tournaments.matches');
    });

    // Registration Actions (outside tournament prefix for cleaner URLs)
    Route::post('/registrations/{registration}/approve', [TournamentAdminController::class, 'approveRegistration'])->name('admin.registrations.approve');
    Route::post('/registrations/{registration}/reject', [TournamentAdminController::class, 'rejectRegistration'])->name('admin.registrations.reject');

    // Match Actions
    Route::get('/matches/{match}/edit', [TournamentAdminController::class, 'editMatch'])->name('admin.matches.edit');
    Route::put('/matches/{match}', [TournamentAdminController::class, 'updateMatch'])->name('admin.matches.update');

    // Team Admin Routes
    Route::prefix('teams')->group(function () {
        Route::get('/', [TeamAdminController::class, 'index'])->name('admin.teams.index');
        Route::get('/{team}', [TeamAdminController::class, 'show'])->name('admin.teams.show');
        Route::post('/{team}/verify', [TeamAdminController::class, 'verify'])->name('admin.teams.verify');
        Route::post('/{team}/unverify', [TeamAdminController::class, 'unverify'])->name('admin.teams.unverify');
        Route::post('/{team}/disband', [TeamAdminController::class, 'disband'])->name('admin.teams.disband');
        Route::post('/{team}/restore', [TeamAdminController::class, 'restore'])->name('admin.teams.restore');
        Route::delete('/{team}', [TeamAdminController::class, 'destroy'])->name('admin.teams.destroy');
    });

    // Game Statistics Admin Routes
    Route::prefix('game-stats')->group(function () {
        Route::get('/', [GameStatsAdminController::class, 'index'])->name('admin.game-stats.index');
        Route::get('/players', [GameStatsAdminController::class, 'players'])->name('admin.game-stats.players');
        Route::get('/players/{uuid}', [GameStatsAdminController::class, 'playerShow'])->name('admin.game-stats.player');
        Route::get('/kills', [GameStatsAdminController::class, 'kills'])->name('admin.game-stats.kills');
        Route::get('/sessions', [GameStatsAdminController::class, 'sessions'])->name('admin.game-stats.sessions');
        Route::get('/server-status', [GameStatsAdminController::class, 'serverStatus'])->name('admin.game-stats.server-status');
        Route::get('/healing', [GameStatsAdminController::class, 'healingEvents'])->name('admin.game-stats.healing');
        Route::get('/base-captures', [GameStatsAdminController::class, 'baseCaptures'])->name('admin.game-stats.base-captures');
        Route::get('/chat', [GameStatsAdminController::class, 'chatMessages'])->name('admin.game-stats.chat');
        Route::get('/game-sessions', [GameStatsAdminController::class, 'gameSessions'])->name('admin.game-stats.game-sessions');
        Route::get('/supply-deliveries', [GameStatsAdminController::class, 'supplyDeliveries'])->name('admin.game-stats.supply-deliveries');
        Route::get('/api-tokens', [GameStatsAdminController::class, 'apiTokens'])->name('admin.game-stats.api-tokens');
        Route::post('/api-tokens', [GameStatsAdminController::class, 'generateToken'])->name('admin.game-stats.generate-token');
        Route::delete('/api-tokens/{tokenId}', [GameStatsAdminController::class, 'revokeToken'])->name('admin.game-stats.revoke-token');
    });

    // Raven Anti-Cheat Admin Routes
    Route::prefix('anticheat')->group(function () {
        Route::get('/', [AnticheatAdminController::class, 'index'])->name('admin.anticheat.index');
        Route::get('/events', [AnticheatAdminController::class, 'events'])->name('admin.anticheat.events');
        Route::get('/stats-history', [AnticheatAdminController::class, 'statsHistory'])->name('admin.anticheat.stats-history');
    });

    // Weapons Admin Routes
    Route::prefix('weapons')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WeaponAdminController::class, 'index'])->name('admin.weapons.index');
        Route::get('/create', [\App\Http\Controllers\Admin\WeaponAdminController::class, 'create'])->name('admin.weapons.create');
        Route::post('/', [\App\Http\Controllers\Admin\WeaponAdminController::class, 'store'])->name('admin.weapons.store');
        Route::post('/sync-from-kills', [\App\Http\Controllers\Admin\WeaponAdminController::class, 'syncFromKills'])->name('admin.weapons.sync');
        Route::get('/{weapon}/edit', [\App\Http\Controllers\Admin\WeaponAdminController::class, 'edit'])->name('admin.weapons.edit');
        Route::put('/{weapon}', [\App\Http\Controllers\Admin\WeaponAdminController::class, 'update'])->name('admin.weapons.update');
        Route::delete('/{weapon}', [\App\Http\Controllers\Admin\WeaponAdminController::class, 'destroy'])->name('admin.weapons.destroy');
        Route::delete('/{weapon}/image', [\App\Http\Controllers\Admin\WeaponAdminController::class, 'deleteImage'])->name('admin.weapons.delete-image');
    });

    // Vehicles Admin Routes
    Route::prefix('vehicles')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\VehicleAdminController::class, 'index'])->name('admin.vehicles.index');
        Route::get('/create', [\App\Http\Controllers\Admin\VehicleAdminController::class, 'create'])->name('admin.vehicles.create');
        Route::post('/', [\App\Http\Controllers\Admin\VehicleAdminController::class, 'store'])->name('admin.vehicles.store');
        Route::post('/sync-from-distance', [\App\Http\Controllers\Admin\VehicleAdminController::class, 'syncFromDistanceData'])->name('admin.vehicles.sync');
        Route::get('/{vehicle}/edit', [\App\Http\Controllers\Admin\VehicleAdminController::class, 'edit'])->name('admin.vehicles.edit');
        Route::put('/{vehicle}', [\App\Http\Controllers\Admin\VehicleAdminController::class, 'update'])->name('admin.vehicles.update');
        Route::delete('/{vehicle}', [\App\Http\Controllers\Admin\VehicleAdminController::class, 'destroy'])->name('admin.vehicles.destroy');
        Route::delete('/{vehicle}/image', [\App\Http\Controllers\Admin\VehicleAdminController::class, 'deleteImage'])->name('admin.vehicles.delete-image');
    });

    // Player Reports
    Route::prefix('reports')->group(function () {
        Route::get('/', [AdminReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/{report}', [AdminReportController::class, 'show'])->name('admin.reports.show');
        Route::put('/{report}', [AdminReportController::class, 'update'])->name('admin.reports.update');
    });
});

// Admin News Routes (accessible to GM, Moderator, and Admin roles)
Route::prefix('admin/news')->middleware(['auth', 'gm'])->group(function () {
    Route::get('/', [NewsAdminController::class, 'index'])->name('admin.news.index');
    Route::get('/create', [NewsAdminController::class, 'create'])->name('admin.news.create');
    Route::post('/', [NewsAdminController::class, 'store'])->name('admin.news.store');
    Route::get('/{article}/edit', [NewsAdminController::class, 'edit'])->name('admin.news.edit');
    Route::put('/{article}', [NewsAdminController::class, 'update'])->name('admin.news.update');
    Route::post('/{article}/toggle-pin', [NewsAdminController::class, 'togglePin'])->name('admin.news.toggle-pin');
    Route::delete('/{article}', [NewsAdminController::class, 'destroy'])->name('admin.news.destroy');
    Route::delete('/{article}/image', [NewsAdminController::class, 'deleteImage'])->name('admin.news.delete-image');
});

// GM routes (accessible to GM, Moderator, and Admin roles)
Route::prefix('gm')->middleware(['auth', 'gm'])->group(function () {
    Route::get('/sessions', [GameStatsAdminController::class, 'gmSessions'])->name('gm.sessions');
    Route::get('/editor-actions', [GameStatsAdminController::class, 'editorActions'])->name('gm.editor-actions');
});

// Referee Routes
Route::prefix('referee')->middleware(['auth', 'referee'])->name('referee.')->group(function () {
    Route::get('/', [\App\Http\Controllers\RefereeController::class, 'index'])->name('dashboard');
    Route::get('/match/{match}/report', [\App\Http\Controllers\RefereeController::class, 'showReportForm'])->name('match.report');
    Route::post('/match/{match}/report', [\App\Http\Controllers\RefereeController::class, 'submitReport'])->name('match.submit-report');
    Route::get('/report/{report}', [\App\Http\Controllers\RefereeController::class, 'viewReport'])->name('report.view');
    Route::post('/report/{report}/approve', [\App\Http\Controllers\RefereeController::class, 'approveReport'])->name('report.approve');
    Route::post('/report/{report}/dispute', [\App\Http\Controllers\RefereeController::class, 'disputeReport'])->name('report.dispute');
});

// RCON Admin Routes (within admin middleware)
Route::prefix('admin/rcon')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\RconController::class, 'index'])->name('admin.rcon.index');
    Route::get('/status', [\App\Http\Controllers\Admin\RconController::class, 'status'])->name('admin.rcon.status');
    Route::get('/players', [\App\Http\Controllers\Admin\RconController::class, 'players'])->name('admin.rcon.players');
    Route::get('/bans', [\App\Http\Controllers\Admin\RconController::class, 'bans'])->name('admin.rcon.bans');
    Route::post('/command', [\App\Http\Controllers\Admin\RconController::class, 'command'])->name('admin.rcon.command');
    Route::post('/kick', [\App\Http\Controllers\Admin\RconController::class, 'kick'])->name('admin.rcon.kick');
    Route::post('/ban', [\App\Http\Controllers\Admin\RconController::class, 'ban'])->name('admin.rcon.ban');
    Route::post('/unban', [\App\Http\Controllers\Admin\RconController::class, 'unban'])->name('admin.rcon.unban');
    Route::post('/say', [\App\Http\Controllers\Admin\RconController::class, 'say'])->name('admin.rcon.say');
});

// Server Manager Admin Routes
Route::prefix('admin/server')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [ServerManagerController::class, 'dashboard'])->name('admin.server.dashboard');
    Route::get('/config', [ServerManagerController::class, 'config'])->name('admin.server.config');
    Route::put('/config/arma', [ServerManagerController::class, 'updateArmaConfig'])->name('admin.server.config.arma.update');
    Route::put('/config/stats', [ServerManagerController::class, 'updateStatsConfig'])->name('admin.server.config.stats.update');
    Route::get('/mods', [ServerManagerController::class, 'mods'])->name('admin.server.mods');
    Route::post('/mods', [ServerManagerController::class, 'addMod'])->name('admin.server.mods.add');
    Route::delete('/mods/{modId}', [ServerManagerController::class, 'removeMod'])->name('admin.server.mods.remove');
    Route::post('/services/{service}/{action}', [ServerManagerController::class, 'serviceAction'])->name('admin.server.service');
    Route::post('/update', [ServerManagerController::class, 'startUpdate'])->name('admin.server.update');
    Route::get('/logs/{type?}', [ServerManagerController::class, 'logs'])->name('admin.server.logs');
    Route::get('/players', [ServerManagerController::class, 'players'])->name('admin.server.players');
    Route::post('/players/kick', [ServerManagerController::class, 'kickPlayer'])->name('admin.server.players.kick');
    Route::post('/players/ban', [ServerManagerController::class, 'banPlayer'])->name('admin.server.players.ban');
    Route::post('/players/unban', [ServerManagerController::class, 'unbanPlayer'])->name('admin.server.players.unban');
    Route::post('/players/broadcast', [ServerManagerController::class, 'broadcast'])->name('admin.server.players.broadcast');
    Route::post('/players/ban-guid', [ServerManagerController::class, 'banPlayerByGuid'])->name('admin.server.players.ban-guid');

    // Player History
    Route::get('/player-history', [ServerManagerController::class, 'playerHistory'])->name('admin.server.player-history');
    Route::get('/player-history/{uuid}', [ServerManagerController::class, 'playerDetail'])->name('admin.server.player-detail');

    // Performance
    Route::get('/performance', [ServerManagerController::class, 'performance'])->name('admin.server.performance');

    // Scheduled Restarts
    Route::get('/scheduled-restarts', [ServerManagerController::class, 'scheduledRestarts'])->name('admin.server.scheduled-restarts');
    Route::post('/scheduled-restarts', [ServerManagerController::class, 'storeScheduledRestart'])->name('admin.server.scheduled-restarts.store');
    Route::put('/scheduled-restarts/{restart}', [ServerManagerController::class, 'updateScheduledRestart'])->name('admin.server.scheduled-restarts.update');
    Route::delete('/scheduled-restarts/{restart}', [ServerManagerController::class, 'destroyScheduledRestart'])->name('admin.server.scheduled-restarts.destroy');

    // Quick Messages
    Route::get('/quick-messages', [ServerManagerController::class, 'quickMessages'])->name('admin.server.quick-messages');
    Route::post('/quick-messages', [ServerManagerController::class, 'saveQuickMessages'])->name('admin.server.quick-messages.save');
    Route::post('/quick-messages/send', [ServerManagerController::class, 'sendQuickMessage'])->name('admin.server.quick-messages.send');

    // Mod Updates
    Route::get('/mod-updates', [ServerManagerController::class, 'modUpdates'])->name('admin.server.mod-updates');

    // Server Comparison
    Route::get('/compare', [ServerManagerController::class, 'compare'])->name('admin.server.compare');

    // AJAX API endpoints
    Route::get('/api/health', [ServerManagerController::class, 'apiHealth'])->name('admin.server.api.health');
    Route::get('/api/status', [ServerManagerController::class, 'apiStatus'])->name('admin.server.api.status');
    Route::get('/api/update-status', [ServerManagerController::class, 'apiUpdateStatus'])->name('admin.server.api.update-status');
    Route::get('/api/anticheat', [ServerManagerController::class, 'apiAnticheat'])->name('admin.server.api.anticheat');
    Route::get('/api/players', [ServerManagerController::class, 'apiPlayers'])->name('admin.server.api.players');
    Route::get('/api/bans', [ServerManagerController::class, 'apiBans'])->name('admin.server.api.bans');
    Route::get('/api/logs/{type}', [ServerManagerController::class, 'apiLogs'])->name('admin.server.api.logs');
    Route::get('/api/performance-data', [ServerManagerController::class, 'apiPerformanceData'])->name('admin.server.api.performance-data');
    Route::get('/api/compare-data', [ServerManagerController::class, 'apiServerCompareData'])->name('admin.server.api.compare-data');
});
