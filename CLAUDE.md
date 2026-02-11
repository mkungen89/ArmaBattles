# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Reforger Community is a Laravel 12 web application for tracking Arma Reforger game servers. It provides real-time server monitoring, player statistics, mod management, tournament system with bracket generation, platoon (team) management, and admin tools with Steam authentication.

## Development Commands

```bash
# Full project setup (install deps, migrate, build)
composer setup

# Start all dev services concurrently (server, queue, logs, vite, reverb)
composer dev

# Run tests
composer test

# Run a single test file
php artisan test --filter=GameStatsApiTest

# Individual commands
php artisan serve              # Dev server only
php artisan queue:listen       # Process jobs
npm run dev                    # Vite with HMR
npm run build                  # Production build

# Server tracking (run via cron or manually)
php artisan server:track --server-id=<battlemetrics_id>

# Sync mod metadata from Reforger Workshop
php artisan mods:sync --server=<battlemetrics_id>

# Recalculate player levels (after XP changes or new system)
php artisan levels:recalculate

# Run database seeders
php artisan db:seed
php artisan db:seed --class=SiteSettingsSeeder  # Specific seeder
```

Note: When running as root, use `COMPOSER_ALLOW_SUPERUSER=1` for composer commands.

## Architecture

### API Versioning

**Current Version:** `/api/v1/` (introduced 2026-02-08)

The API uses URI-based versioning. All new integrations should use `/api/v1/` endpoints.

**Legacy API (`/api/*`):**
- Status: ⚠️ Deprecated as of 2026-02-08
- Sunset date: 2026-06-01
- All responses include deprecation headers: `X-API-Deprecated`, `X-API-Sunset-Date`, `Link` (to v1 alternative)
- Functionally identical to v1 but without improved organization

**Migration:** Replace `/api/` with `/api/v1/` in all API calls. Event read endpoints moved from `/api/{event}` to `/api/v1/events/{event}`.

See [docs/API_VERSIONING.md](docs/API_VERSIONING.md) for full migration guide.

### API Documentation

Interactive API docs (Swagger UI) available at `/api/docs`. The OpenAPI spec is at `public/docs/openapi.yaml`. This is a static file and must be manually updated when API endpoints change (add/remove/modify routes, change validation rules, or alter response shapes).

### Two-Tier API System

The codebase has two separate API layers:

1. **`StatsController`** (`app/Http/Controllers/Api/StatsController.php`) — Primary API using Laravel Sanctum (`auth:sanctum`). All new endpoints go here. Handles both write endpoints (game server POSTs events) and read endpoints (website fetches data). Available at `/api/v1/*` (current) and `/api/*` (deprecated). Tokens managed via admin panel at `/admin/game-stats/api-tokens`.

2. **`AnticheatController`** (`app/Http/Controllers/Api/AnticheatController.php`) — Raven Anti-Cheat API using Sanctum. Receives AC events and periodic stats from the Node.js stats collector. Available at `/api/v1/anticheat-*` and `/api/anticheat-*` (deprecated).

3. **`GameEventController`** (`app/Http/Controllers/Api/GameEventController.php`) — Legacy API using custom token middleware (`api.token`). Mounted at `/api/legacy/game-events`. Kept for backwards compatibility only.

### API Data Flow

1. Game servers POST events to StatsController write endpoints with Sanctum Bearer token
2. StatsController stores raw events in event-specific tables using raw `DB::table()->insertGetId()` (NOT Eloquent)
3. Each store method MUST call a corresponding private helper to update aggregated `player_stats` (e.g. `storeKill` → `updatePlayerKillStats`, `storeDistance` → `updatePlayerDistanceTotals`)
4. Admin views (`GameStatsAdminController`) and player profile (`ProfileController`) query both raw events and aggregated stats

**Critical pattern:** When adding a new store endpoint, always ensure it calls an update helper to increment the relevant `player_stats` columns. Forgetting this means the stat will be stored as raw data but never appear on profiles or leaderboards.

### API Write Endpoints (StatsController)

**Current (v1):** All endpoints under `/api/v1/`

| Category | Endpoints |
|----------|-----------|
| Combat | `/api/v1/player-kills`, `/api/v1/damage-events` (batch) |
| Players | `/api/v1/connections`, `/api/v1/player-stats` |
| Objectives | `/api/v1/base-events` |
| World | `/api/v1/building-events`, `/api/v1/consciousness-events`, `/api/v1/group-events` |
| XP | `/api/v1/xp-events` |
| Social | `/api/v1/chat-events`, `/api/v1/editor-actions`, `/api/v1/gm-sessions` |
| Game State | `/api/v1/server-status` |
| ReforgerJS | `/api/v1/player-distance`, `/api/v1/player-grenades`, `/api/v1/player-shooting`, `/api/v1/player-healing`, `/api/v1/player-supplies`, `/api/v1/supply-deliveries` |
| Anti-Cheat | `/api/v1/anticheat-events`, `/api/v1/anticheat-stats` |

### API Read Endpoints (StatsController)

**Current (v1):** Organized under `/api/v1/` with logical prefixes

- **Servers:** `/api/v1/servers`, `/api/v1/servers/{id}`, `/api/v1/servers/{id}/status`, `/api/v1/servers/{id}/players`
- **Players:** `/api/v1/players`, `/api/v1/players/{id}`, `/api/v1/players/{id}/stats`, `/api/v1/players/{id}/kills`, `/api/v1/players/{id}/deaths`, `/api/v1/players/{id}/connections`, `/api/v1/players/{id}/xp`, `/api/v1/players/{id}/distance`, `/api/v1/players/{id}/shooting`
- **Leaderboards:** `/api/v1/leaderboards/kills`, `/api/v1/leaderboards/deaths`, `/api/v1/leaderboards/kd`, `/api/v1/leaderboards/playtime`, `/api/v1/leaderboards/xp`, `/api/v1/leaderboards/distance`, `/api/v1/leaderboards/roadkills`
- **Events/Logs:** `/api/v1/events/kills`, `/api/v1/events/connections`, `/api/v1/events/bases`, `/api/v1/events/chat`, `/api/v1/events/gm-sessions`
- **Stats/Aggregates:** `/api/v1/stats/overview`, `/api/v1/stats/weapons`, `/api/v1/stats/factions`, `/api/v1/stats/bases`

All endpoints require Sanctum auth and include rate limiting based on token type.

### BattleMetrics Scenario Name Resolution

BattleMetrics returns raw game localization keys like `#AR-Campaign_ScenarioName_Everon` instead of human-readable names. The `Server` model handles this:

- **`Server::$scenarioNameMap`** — Static lookup table mapping all known localization keys to display names (Conflict, Combat Ops, Game Master, Capture & Hold, Training, etc.)
- **`Server::resolveScenarioName($raw)`** — Static method: exact lookup → fallback pattern parser → returns raw if not a `#AR-` key
- **`$server->scenario_display_name`** — Accessor for use in Blade templates

When displaying scenario names, always use `$server->scenario_display_name` (not `$server->scenario`). For API responses, use `Server::resolveScenarioName($raw)`. When adding new official scenarios, add the localization key to `$scenarioNameMap` in `Server.php`.

### External Service Integration

- **BattleMetricsService** — Primary source for server info, player counts. Aggressive caching (30-60s live, 5min history).
- **A2SQueryService** — Direct UDP queries to game servers (Source Engine protocol). 3-second timeout, automatic port fallback.
- **ReforgerWorkshopService** — Web scraping for mod metadata. Extracts from Next.js `__NEXT_DATA__` JSON, falls back to meta tags. Workshop URLs: `{modId}-{slugifiedName}`. 1-hour cache.
- **GameServerManager** — HTTP client to Node.js management layer. Supports multi-server via `forServer(Server $server)` — falls back to global `config('services.gameserver.url/key')` when no server model provided. All admin server control (restart, stop, ban, broadcast, mods, config) goes through this service.
- **PlayerHistoryService** — Queries `connections` table for player search and connection history. Uses PostgreSQL `string_agg(DISTINCT ...)` for alternative name aggregation (falls back to `GROUP_CONCAT` on MySQL/SQLite).
- **ModUpdateCheckService** — Compares installed mod versions (from GameServerManager) against latest versions from ReforgerWorkshopService. Catches exceptions per-mod so one failure doesn't break the list.
- **TournamentBracketService** — Bracket generation for single_elimination, double_elimination, round_robin, swiss.
- **MetricsTracker** — Tracks page views, API requests, and feature usage. Methods: `trackPageView()`, `trackApiRequest()`, `trackFeatureUse()`. All wrapped in try/catch — never breaks requests on failure. Uses raw `DB::table('analytics_events')->insert()`.

### Game Statistics Tables

**Aggregated:**
- `player_stats` — Accumulated player stats. Key columns: kills, deaths, headshots, team_kills, total_roadkills, playtime_seconds, total_distance, shots_fired, grenades_thrown, heals_given, heals_received, bases_captured, supplies_delivered, xp_total, hits_head/torso/arms/legs, total_hits, total_damage_dealt, **level** (1-100), **level_xp** (xp_total + achievement_points), **achievement_points**. Accessed via `$user->gameStats()` which returns a `PlayerStat` model.

**Stats aggregation mapping (store method → player_stats columns):**

| Store Method | Raw Table | player_stats Columns Updated |
|---|---|---|
| `storeKill` | `player_kills` | kills, deaths, headshots, team_kills, total_roadkills |
| `storeDistance` | `player_distance` | playtime_seconds, total_distance |
| `storeShooting` | `player_shooting` | shots_fired |
| `storeGrenade` | `player_grenades` | grenades_thrown |
| `storeHealing` | `player_healing_rjs` | heals_given, heals_received |
| `storeBaseEvent` | `base_events` | bases_captured (event types: CAPTURED, CAPTURE, BASE_SEIZED, BASE_CAPTURE) |
| `storeSupplies` | `supply_deliveries` | supplies_delivered |
| `storeXpEvent` | `xp_events` | xp_total |
| `storeDamageEvents` | `damage_events` | hits_head/torso/arms/legs, total_hits, total_damage_dealt |

**Raw Event Tables:**
- `player_kills` — Kill events (killer/victim uuid/name, weapon, distance, headshot, is_roadkill, victim_type AI/Player)
- `connections` — Player connect/disconnect events (NOT `player_sessions`)
- `damage_events` — Hit zone damage data (killer/victim, hit_zone_name, damage_amount, is_friendly_fire)
- `xp_events` — XP rewards (player_uuid, xp_amount, reward_type). Game server sends `xp_type` field, mapped to `reward_type` column.
- `player_distance` — Movement tracking (walking_distance, vehicle distances, vehicles JSON array)
- `player_shooting` — Shots fired tracking
- `player_grenades` — Grenade usage
- `player_healing_rjs` — Medical item usage
- `supply_deliveries` — Supply deliveries
- `base_events` — Objective capture events
- `building_events` — Building/composition placement
- `consciousness_events` — Knocked/unconscious state changes
- `group_events` — Squad join/leave
- `chat_events` — In-game chat messages
- `editor_actions` — GM editor action logging
- `gm_sessions` — GM enter/exit events
- `game_sessions` — Game mode, map, duration, winner
- `server_status` — Periodic server snapshots (fps, memory_mb, players_online, uptime_seconds, recorded_at)
- `scheduled_restarts` — Restart schedules per server (daily/weekly/custom cron, warning config, next/last execution)
- `weapons` — Weapon metadata with optional image_path for display
- `vehicles` — Vehicle metadata with optional image_path for display (mirrors weapons pattern)
- `anticheat_events` — Raven AC enforcement actions and flagged players (event_type: ENFORCEMENT_ACTION, ENFORCEMENT_SKIPPED, LIFESTATE, SPAWN_GRACE, OTHER, UNKNOWN)
- `anticheat_stats` — Periodic AC snapshots every ~10s (online/active/registered players, potential_cheaters, banned/confirmed/potentials lists as JSON)
- `player_ratings` — Competitive Glicko-2 ratings per user (rating, rating_deviation, volatility, rank_tier, ranked_kills/deaths, placement_games, is_placed, peak_rating, opted_in_at)
- `rating_history` — Rating change history per period (before/after for rating, RD, volatility, tier; period kills/deaths/encounters)
- `rated_kills_queue` — Staging table for competitive events awaiting batch processing. Columns: kill_id, event_type (kill/team_kill/base_capture/heal/supply), player_uuid, killer/victim_uuid, processed flag. Supports PvP kills, team kill penalties, and objective events.
- `analytics_events` — Write-once event log for page views, API requests, and feature usage. Columns: event_type (page_view/api_request/feature_use), event_name, user_id, token_id, ip_address, user_agent, response_time_ms, response_status, metadata (JSON), created_at. No `updated_at`. Indexed on (event_type, created_at), created_at, user_id, token_id.
- `system_metrics` — Time-series system performance snapshots (every 5min via `metrics:collect`). Columns: cache_hits, cache_misses, jobs_processed, jobs_failed, queue_size, memory_usage_mb, cpu_load_1m, disk_usage_percent, api_requests_count, api_p50_ms, api_p95_ms, api_p99_ms, recorded_at.

### Core Domain Models

**Server Tracking:** `Server`, `Mod`, `ServerSession`, `ServerStatistic`, `ScheduledRestart`

**Tournament System:** `Tournament` → `TournamentMatch` → `MatchGame`, `TournamentRegistration`, `MatchCheckIn`, `MatchReport` (referee reports)

**Platoon (Team) System:** `Team`, `TeamInvitation`, `TeamApplication`

**Scrim System:** `ScrimMatch`, `ScrimInvitation` — Casual team-vs-team matches separate from tournaments. Status flow: `pending → scheduled → in_progress → completed/cancelled`. 7-day invitation expiry. Optional server password protection.

**Reputation System:** `PlayerReputation`, `ReputationVote` — +Rep/-Rep voting with categories (teamwork, leadership, sportsmanship, general). Tiers: Trusted (100+), Good (50+), Neutral (0+), Poor (-50+), Flagged (-50 or lower). 24-hour vote change window.

**Ranked Rating System (Glicko-2):** `PlayerRating`, `RatingHistory` — Opt-in competitive tactical rating. Rewards objective play, not just kills. Event types and their Glicko-2 mapping: `kill` = win vs victim's real rating; `team_kill` = LOSS vs phantom at own rating, RD 150 (significant penalty); `friendly_fire` = LOSS vs phantom at own rating, RD 250 (moderate penalty per hit); `vehicle_destroy` = win vs phantom at 1600 (high-value target); `base_capture` = win vs phantom at 1500; `heal` = win vs phantom at 1300 (non-self only); `supply` = win vs phantom at 1300; `building` = win vs phantom at 1200 (engineer role). AI kills excluded. `rated_kills_queue` table stages events for batch processing every 4 hours. Tiers: Bronze (0+), Silver (1200+), Gold (1400+), Platinum (1600+), Diamond (1800+), Master (2000+), Elite (2200+). 10 placement games required. Services: `Glicko2Service` (pure math), `RatingCalculationService` (orchestrator with phantom opponent constants). StatsController hooks: `queueRatedKillIfEligible()` in `storeKill()`, `queueRatedObjectiveIfEligible()` in `storeBaseEvent()`, `storeHealing()`, `storeSupplies()`, `storeXpEvent()` (ENEMY_KILL_VEH), `storeBuildingEvent()` (BUILDING_PLACED), `storeDamageEvents()` (is_friendly_fire). Commands: `ratings:calculate` (every 4h), `ratings:decay` (daily, 14-day inactivity). User: `$user->playerRating()` HasOne, `$user->isCompetitive()`, `$user->getRatingDisplay()`. Team: `$team->getTeamRating()` returns avg rating of competitive members.

**Content Creators:** `ContentCreator`, `HighlightClip`, `ClipVote` — Multi-platform streamer directory (Twitch, YouTube, TikTok, Kick). Clip submission with community voting. "Clip of the Week" feature.

**Achievements:** `Achievement`, `AchievementProgress`, `AchievementShowcase` — Category-based achievements with progress tracking, rarity calculation, and profile showcase (pin up to 3).

**Favorites:** `Favorite` — Polymorphic favorites for players, teams, and servers. User methods: `favorites()`, `hasFavorited($model)`, `toggleFavorite($model)`.

**Discord:** `DiscordRichPresence` — Tracks user activity (playing, watching_tournament, browsing) with Discord RPC payload generation.

**News:** `NewsArticle`, `NewsComment`, `NewsHoorah` — Community news with comments and "hoorah" reactions.

**Users:** `User` — Steam-authenticated with roles (user/moderator/admin/gm/referee/observer/caster). Has `player_uuid` field to link to game stats. `$user->gameStats()` returns the linked `PlayerStat` record. Optional 2FA via TOTP. User settings: `profile_visibility` (public/private), `notification_preferences` (JSON), `social_links` (JSON — twitch, youtube, tiktok, kick, twitter, facebook, instagram). `last_seen_at` tracked via `TrackLastSeen` middleware (5-minute cache throttle).

**Role helpers:** `isReferee()` (admin/moderator/referee), `isObserver()` (admin/moderator/referee/observer), `isCaster()` (admin/moderator/caster), `canManageTournaments()` (admin/moderator/referee).

**Metrics:** `AnalyticsEvent`, `SystemMetric` — Page view/API/feature tracking and system performance snapshots. Both use `$timestamps = false`.

**Audit:** `AdminAuditLog` — Tracks admin and security actions with the `LogsAdminActions` trait.

**Player Leveling System:** Level 1-100 progression with 6 tiers (Recruit, Soldier, Veteran, Elite, Master, Legend). Exponential XP curve: `BASE_XP (1000) * pow(level, 1.15)`. Service: `PlayerLevelService` handles all level calculations, tier assignment, and progress tracking. Database: `player_stats` has `level`, `level_xp`, `achievement_points` columns. XP sources: `xp_total` (game XP) + `achievement_points` = `level_xp`. Auto level-up: `StatsController::updatePlayerXp()` checks for level ups and sends `LevelUpNotification`. Command: `levels:recalculate` for bulk recalculation. Leaderboard: `/levels` route with tier distribution, stats overview, paginated rankings. Model accessors: `$stats->tier`, `$stats->level_progress`, `$stats->level_display`. UI: Level badges and XP progress bars on profile headers.

### Route Groups

- `/` — Public pages (home, rules, news)
- `/profile` — Logged-in user's profile with game stats (kills, weapons, hit zones, XP, etc.)
- `/players` — Player search with autocomplete (recent searches via localStorage)
- `/players/compare` — Player comparison tool (up to 4 players, radar chart, stat bars, weapon charts)
- `/players/compare/head-to-head` — AJAX endpoint for H2H matchup data between 2 players
- `/kill-feed` — Public kill feed with weapon images
- `/weapons` — Public weapon stats leaderboard (headshot %, distances)
- `/servers/{serverId}/*` — Server detail pages, AJAX endpoints, embeddable widget
- `/servers/{serverId}/stats` — Server-specific performance stats with Chart.js
- `/servers/{serverId}/heatmap` — Kill/death heatmap on Leaflet.js map (admin/gm/mod see live player positions)
- `/servers/{serverId}/widget` — Standalone embeddable status widget (public, no auth)
- `/servers/{serverId}/widget/api` — Public JSON API for server status
- `/servers/{serverId}/embed` — Embed code generator with live preview
- `/achievements` — Public achievement catalog with progress tracking
- `/two-factor-challenge` — 2FA login challenge (guest with session)
- `/profile/two-factor/*` — 2FA setup, confirm, disable, recovery codes (auth)
- `/auth/steam/*` — Steam OAuth flow
- `/tournaments/*` — Public tournament views, brackets, match details
- `/teams/*` — Platoon profiles, management, applications
- `/matches/*` — Match check-in and scheduling
- `/scrims/*` — Scrim (practice match) management
- `/ranked/*` — Competitive skill rating leaderboard, player detail, about page, opt-in/out
- `/reputation/*` — Player reputation leaderboard and voting
- `/creators/*` — Content creator directory and registration
- `/clips/*` — Highlight clip gallery and submission
- `/referee/*` — Referee dashboard and match reporting (requires referee middleware)
- `/favorites` — User's favorited players, teams, servers
- `/leaderboard` — Public leaderboards with sort/period/export
- `/levels` — Level leaderboard with tier distribution, stats overview (total players, highest level, avg level, legends count), paginated rankings with progress bars
- `/news/*` — Community news articles with comments
- `/discord/*` — Discord Rich Presence settings
- `/admin/*` — Admin panel (requires AdminMiddleware)
  - `/admin/users` — User management with all 7 roles (user/moderator/admin/gm/referee/observer/caster), player UUID column, Reset 2FA, ban/unban
  - `/admin/users/{user}/edit` — Full user editor: name, role, email, player_uuid, discord connection, profile visibility, custom avatar. User Activity section: links to game stats, teams, tournaments (via teams), audit log. Admin Actions: impersonate user, delete user. Cannot impersonate/delete self.
  - `/admin/users/{user}/impersonate` — Log in as another user (stores original user in session). Purple banner shown when impersonating with "Stop Impersonating" button.
  - `/admin/server/*` — Server Manager: dashboard, player history, performance graphs, scheduled restarts, quick messages, mod update checker, server comparison, plus service controls/config/logs/mods
  - `/admin/game-stats/*` — Game stats dashboard, player profiles, event tables, API tokens
  - `/admin/anticheat/*` — Raven Anti-Cheat dashboard, events log, stats history
  - `/admin/weapons/*` — Weapon image management
  - `/admin/vehicles/*` — Vehicle image management (mirrors weapons pattern)
  - `/admin/rcon/*` — RCON server commands (kick, ban, say). Separate from GameServerManager; uses `config('services.rcon.api_url/api_key')`
  - `/admin/reports/*` — Player conduct reports with status workflow (open/investigating/action_taken/dismissed)
  - `/admin/metrics` — Metrics & Tracking dashboard (analytics, API usage, performance) with 3 tabs and Chart.js charts
  - `/admin/ranked` — Ranked ratings admin dashboard (competitive count, tier distribution, suspicious players, queue size, rating reset)
  - `/admin/audit-log` — Enhanced audit log with user/date/search filters and CSV export
  - `/admin/news/*` — News article management
- `/export/*` — Stats export: player CSV, match history CSV, leaderboard CSV/JSON (via `StatsExportController`)
- `/gm/*` — GM/Moderator routes (gm_sessions, editor_actions)
- `/api/*` — Sanctum-authenticated Stats API
- `/api/legacy/*` — Legacy token-authenticated GameEvent API

### Tournament Status Flow

```
draft → registration_open → registration_closed → in_progress → completed
                                                      ↓
                                                  cancelled
```

### Two-Factor Authentication

TOTP-based 2FA (Google Authenticator, Authy). Opt-in per user from profile settings.

- **`TwoFactorController`** (`app/Http/Controllers/Auth/TwoFactorController.php`) — Setup, confirm, disable, challenge, recovery codes
- `two_factor_secret` (encrypted), `two_factor_recovery_codes` (encrypted), `two_factor_confirmed_at` on User model
- `User::hasTwoFactorEnabled()` checks both secret and confirmed_at are present
- Login flow: Auth succeeds → 2FA enabled? → logout, store `two_factor_user_id` in session → redirect `/two-factor-challenge` → verify → `Auth::loginUsingId()`
- 8 single-use recovery codes, 10-char alphanumeric
- Admin can reset a user's 2FA via `/admin/users/{user}/reset-2fa`
- Packages: `pragmarx/google2fa-laravel`, `bacon/bacon-qr-code`

### Audit Logging

- **`AdminAuditLog`** model — Stores user_id, action, target_type, target_id, metadata (JSON), ip_address
- **`LogsAdminActions`** trait — `$this->logAction('action.name', 'TargetType', $id, $metadata)` — used in AdminController, ServerManagerController, and TwoFactorController
- Admin view at `/admin/audit-log` with filtering by action, user, date range, search, and CSV export
- Auto-cleanup: logs older than 1 year deleted monthly (via `routes/console.php`)
- 2FA events logged: `2fa.initiated`, `2fa.enabled`, `2fa.disabled`, `2fa.admin-reset`, `2fa.challenge-passed`, `2fa.recovery-codes-regenerated`
- Server manager events logged: `server.scheduled-restart.*`, `server.player.ban-guid`, `server.broadcast`, `server.quick-messages.update`, etc.

### Middleware

- `admin` — `AdminMiddleware` — requires admin role
- `gm` — `GMMiddleware` — requires gm, moderator, or admin role
- `referee` — `RefereeMiddleware` — requires referee, moderator, or admin role
- `api.token` — `ApiTokenAuth` — legacy custom bearer token check
- `api.rate` — `ApiRateLimiter` — per-token rate limiting (standard 60/min, high-volume 180/min, premium 300/min). Returns `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset` headers.
- `api.deprecation` — `ApiDeprecationWarning` — adds deprecation headers to legacy `/api/*` endpoints
- `TrackLastSeen` — Web middleware (appended). Updates `last_seen_at` on user, cache-throttled to once per 5 minutes.
- `TrackAnalytics` — Terminable middleware (appended to both web and api groups). Records `microtime(true)` start in `handle()`, inserts analytics event in `terminate()` (after response sent, zero user latency). Web: tracks `page_view` for GET requests (skips AJAX, admin polling). API: tracks `api_request` with response time and status code.
- `MaintenanceModeMiddleware` — Web middleware (appended). Checks `maintenance_mode` site setting. Allows admins, login/auth routes, and API through. Returns 503 with configurable message.
- CSRF is disabled for `api/*` routes in `bootstrap/app.php`
- Default `throttle:api` is removed from the api middleware group (custom `api.rate` handles rate limiting instead)

### Admin User Management (`/admin/users/*`)

`AdminController` provides comprehensive user management for all 7 roles with impersonation and advanced editing.

**User List (`/admin/users`):**
- Filterable by role (user/moderator/admin/gm/referee/observer/caster), banned status, search by name/Steam ID
- Displays: avatar, name, Steam ID, player UUID (truncated), role with color coding, status (active/banned + 2FA indicator), joined date
- Role colors: admin=red, moderator=yellow, gm=purple, referee=blue, observer=cyan, caster=pink, user=gray
- Actions per user: Edit, Reset 2FA (if 2FA enabled), Ban/Unban
- Pagination: 20 users per page

**User Editor (`/admin/users/{user}/edit`):**
- **Basic Info:** name, role (all 7 roles), email, profile visibility (public/private)
- **Game Integration:** player_uuid (links to game stats), custom_avatar (override Steam avatar)
- **Discord:** discord_id, discord_username
- **User Activity Links:** Game Stats (if player_uuid exists), Teams (with count), Tournaments (count via teams), Audit Log (filtered for user)
- **2FA Management:** Shows if 2FA enabled with confirmation date, Reset 2FA button
- **Account Status:** Ban/Unban with optional reason field
- **Admin Actions:** Impersonate User (login as them), Delete User (permanent with confirmation)
- **Protection:** Cannot impersonate or delete yourself

**Impersonation System:**
- Stores original admin user ID in `session('impersonating')`
- Purple banner displayed at top of all pages when impersonating with "Stop Impersonating" button
- Route: `POST /stop-impersonating` (available to any authenticated user, not just admins)
- Audit logging: `user.impersonate` action logged with target user info
- Use case: debugging user-specific issues, testing permissions

**Validation:** All 7 roles accepted in `updateUser()`. Fields: name, role, email (nullable), player_uuid (nullable), discord_id/username (nullable), profile_visibility (required), custom_avatar (nullable URL).

**Tournament Count:** Tournaments counted via user's teams (`foreach ($user->teams as $team) { $count += $team->registrations()->count() }`) since tournament registrations are team-based, not user-based.

### Server Manager (`/admin/server/*`)

`ServerManagerController` manages game servers via `GameServerManager` (HTTP API to Node.js management layer).

**Multi-server architecture:** The `servers` table has `manager_url`, `manager_key`, `is_managed` columns. `GameServerManager` accepts an optional `Server` model — `forServer($server)` uses per-server credentials, otherwise falls back to global config. This allows managing multiple game servers from one admin panel.

**Sub-pages:**
- Dashboard — real-time status with Alpine.js polling (health/status/AC/logs)
- Player History — search `connections` table by name/UUID, view connection logs, ban by GUID
- Performance — Chart.js graphs (FPS, memory, players, uptime) from `server_status` table via AJAX
- Scheduled Restarts — CRUD for `scheduled_restarts` table, executed via `ExecuteScheduledRestart` job (sends broadcast warnings then restarts)
- Quick Messages — broadcast templates stored in `site_settings` key `broadcast_templates` (JSON)
- Mod Updates — compares installed versions (from GameServerManager) vs Workshop versions (from ReforgerWorkshopService)
- Server Comparison — side-by-side live metrics for multiple managed servers via AJAX polling

**`server_status` table (production schema):** `server_id`, `players_online`, `max_players`, `ai_count`, `fps` (decimal), `memory_mb`, `uptime_seconds`, `recorded_at`

### Environment Variables

Required for full functionality:
```
BATTLEMETRICS_API_TOKEN    # BattleMetrics API key
BATTLEMETRICS_SERVER_ID    # Default server to track
STEAM_CLIENT_SECRET        # Steam OAuth secret
GAMESERVER_URL             # Node.js server manager API URL (services.gameserver.url)
GAMESERVER_KEY             # Server manager API bearer token (services.gameserver.key)
REVERB_APP_ID              # Reverb application ID (e.g. armabattles)
REVERB_APP_KEY             # Reverb app key (hex string)
REVERB_APP_SECRET          # Reverb app secret (hex string)
REVERB_SERVER_HOST         # Reverb bind address (127.0.0.1)
REVERB_SERVER_PORT         # Reverb internal port (8085)
REVERB_HOST                # Public hostname for WS connections (armabattles.com)
REVERB_PORT                # Public port (443 via nginx proxy)
REVERB_SCHEME              # https
```

Production uses PostgreSQL. Tests use in-memory SQLite. Uses database-backed cache, sessions, queues, broadcasting (Reverb), and notifications.

### Scheduled Tasks (`routes/console.php`)

- `server:track` — every 5 minutes (server status via A2S)
- Expired team invitations cleanup — daily
- Old read notifications cleanup — weekly (>90 days, configurable via `notification_retention_days` setting)
- Old audit logs cleanup — monthly (>1 year, configurable via `audit_log_retention_days` setting)
- `achievements:check` — hourly
- Scheduled restart processor — every minute (dispatches `ExecuteScheduledRestart` jobs for due restarts)
- `leaderboards:warm-cache` — every 4 minutes (pre-warms 30 cache variants before 5min TTL expires)
- `metrics:collect` — every 5 minutes (collects queue size, CPU, memory, disk, API percentiles into `system_metrics`)
- Old analytics events cleanup — daily (configurable via `analytics_retention_days` setting, default 90 days)
- Old system metrics cleanup — daily (configurable via `metrics_retention_days` setting, default 90 days)
- `ratings:calculate` — every 4 hours (processes rated_kills_queue, updates Glicko-2 ratings)
- `ratings:decay` — daily (increases RD for competitive players inactive >14 days)

### Profile System

Two controllers serve player profiles with identical data but different views:

- **`ProfileController`** (`show()`) → `profile.show` — Logged-in user's own profile
- **`PlayerProfileController`** (`show()`) → `profile.public` — Any user's public profile (respects `profile_visibility`)

Both controllers must pass the same variables to their views. When adding new stats sections, update BOTH controllers and BOTH views. Shared partials in `resources/views/profile/`:
- `_social-links.blade.php` — Social media icons
- `_vehicle-stats.blade.php` — Vehicle distance breakdown + roadkills + top vehicles (expects both `$vehicleStats` and `$gameStats`)

Profile views display data from two sources:
1. `$gameStats` (PlayerStat model) — aggregated counters from `player_stats` table
2. Raw queries to event tables — top weapons, recent kills, hit zones, XP breakdown, etc.

### Player Comparison Tool

`PlayerComparisonController` supports comparing up to 4 players via query params (`?p1=uuid&p2=uuid&p3=uuid&p4=uuid`). The view uses Chart.js for:
- **Radar chart** — 8 axes (Kills, Deaths, K/D, Headshots, Playtime, Distance, Heals, XP), normalized to 0-100% relative to max
- **Stat bars** — 14 stats with color-coded bars per player, winner highlighted
- **Weapon preference chart** — Horizontal grouped bar chart of top weapons across all players
- **Head-to-head section** — Only shown with exactly 2 players, AJAX-loaded from `/players/compare/head-to-head`

Player colors: P1 green (#22c55e), P2 blue (#3b82f6), P3 orange (#f97316), P4 purple (#a855f7).

### Admin Image Management Pattern

Weapons and Vehicles share an identical admin CRUD pattern:
- Model with `name` (unique), `display_name`, `image_path`, optional metadata
- Admin controller with index/create/store/edit/update/destroy + deleteImage
- Images stored via `Storage::disk('public')` at `weapons/` or `vehicles/`
- Displayed on profiles using `Storage::url($imagePath)`
- `VehicleAdminController` has additional `syncFromDistanceData()` that extracts unique vehicle names from `player_distance.vehicles` JSON

### PostgreSQL Considerations

Production uses PostgreSQL. Key differences from MySQL/SQLite to watch for:

- **JSON columns:** Cannot compare JSON directly with `!=` or `=`. Use `::text` cast: `->whereRaw("column::text != '[]'")`
- **String aggregation:** Use `string_agg(DISTINCT col, ',')` instead of MySQL's `GROUP_CONCAT`
- **Type casting:** PostgreSQL is strict about types. Explicit casts may be needed in raw queries.
- **Migrations:** Avoid PostgreSQL-specific syntax (e.g. `ALTER COLUMN ... TYPE`) in migrations that need to run on SQLite for tests. Use `Schema::hasColumn()` guards for idempotent migrations.

### Leaderboard Endpoints

All leaderboards query `player_stats` table via `DB::table('player_stats')` with Sanctum auth. Pattern:
```php
Route::get('/leaderboards/{stat}', [StatsController::class, 'get{Stat}Leaderboard']);
```
Available: kills, deaths, kd, playtime, xp, distance, roadkills.

### Site Settings System

Database-backed settings via `SiteSetting` model with 1-hour cache. Access anywhere with the `site_setting($key, $default)` helper. Settings are managed in admin panel and seeded via `SiteSettingsSeeder`.

Key setting groups: General (site_name, maintenance_mode), SEO (meta_description, og_image_url, analytics_code), Security (allow_registration, force_2fa_staff), Server Tracking (BM cache TTLs, A2S query settings), Leaderboard (per_page, min_playtime), Appearance (custom_logo_url, primary_accent_color, custom_css).

Auto-casts types: string, text, integer, boolean, json, color. Gracefully falls back if DB unavailable.

### Favicon & Branding

Favicon files in `public/`: `favicon.ico` (multi-size 16+32+48), `favicon-16x16.png`, `favicon-32x32.png`, `apple-touch-icon.png` (180x180), `android-chrome-192x192.png`, `android-chrome-512x512.png`. Link tags in `layouts/app.blade.php` `<head>`.

Site branding (name, logo, accent color) is configurable via site settings. Custom CSS injection supported via `custom_css` setting.

### Email Notifications

Four queued mailable classes in `app/Mail/`:
- `TeamInvitationMail` — Team invites with accept/decline links
- `MatchReminderMail` — Automated 24h/1h match reminders (via `SendMatchReminders` command)
- `AchievementUnlockedMail` — Achievement unlock with rarity percentage
- `TournamentRegistrationMail` — Tournament confirmation with status

All emails respect `notification_preferences` on User model. Markdown templates in `resources/views/emails/`.

### Notification System

Laravel database notifications with Alpine.js dropdown in the navbar. Categories: team, match, achievement, general. Features:
- Category-based filtering with icons (team=blue, match=orange, achievement=yellow)
- Desktop notifications via browser Notification API (auto-prompts permission)
- Mark all as read, individual mark-as-read on click
- Real-time via WebSocket (NewNotification event on private channel), fallback polling every 60s
- Controller: `NotificationController` with category filtering support

### Real-Time Broadcasting (Laravel Reverb)

WebSocket-based real-time updates via Laravel Reverb. Replaces HTTP polling with instant event delivery, keeping polling as fallback with slowed intervals when WS is connected.

**Stack:** Laravel Reverb (server) + Laravel Echo + Pusher.js (client). Reverb runs as a systemd service on port 8085, proxied via nginx at `/app` and `/apps` paths over WSS (port 443).

**Configuration:**
- `config/reverb.php` — Reverb server config (reads from env)
- `config/broadcasting.php` — Broadcasting driver config
- `bootstrap/app.php` — `withBroadcasting()` registers channel auth routes
- `resources/js/bootstrap.js` — Echo client initialization
- `routes/channels.php` — Channel authorization rules
- `.env` — `BROADCAST_CONNECTION=reverb`, `REVERB_SERVER_PORT=8085`, `VITE_REVERB_*` vars

**Channels (`routes/channels.php`):**

| Channel | Type | Auth | Used For |
|---------|------|------|----------|
| `server.{serverId}` | Public | None | Kill feed, status, player connections, base events |
| `server.global` | Public | None | Activity feed (kills, connections, captures) |
| `App.Models.User.{id}` | Private | `$user->id === $id` | Notifications |
| `admin.server.{serverId}` | Private | admin/moderator | Admin dashboard events |
| `heatmap.{serverId}` | Private | admin/gm/moderator | Player tracking |

**Events (`app/Events/`):**

| Event | Channel | Broadcast As | Queue |
|-------|---------|-------------|-------|
| `KillFeedUpdated` | `server.{id}` | `.kill.new` | ShouldBroadcastNow |
| `PlayerConnected` | `server.{id}` | `.player.connected` | ShouldBroadcastNow |
| `ServerStatusUpdated` | `server.{id}` | `.status.updated` | ShouldBroadcast (queued) |
| `ActivityFeedUpdated` | `server.global` | `.activity.new` | ShouldBroadcastNow |
| `BaseEventOccurred` | `server.{id}` | `.base.event` | ShouldBroadcastNow |
| `NewNotification` | private `App.Models.User.{id}` | `.notification.new` | ShouldBroadcast (queued) |

**Dispatch points in StatsController:**
- `storeKill()` → `KillFeedUpdated` + `ActivityFeedUpdated`
- `storeConnection()` → `PlayerConnected` + `ActivityFeedUpdated` (on CONNECT)
- `storeServerStatus()` → `ServerStatusUpdated`
- `storeBaseEvent()` → `BaseEventOccurred` + `ActivityFeedUpdated` (on capture)
- `TrackServerStatus` command → `ServerStatusUpdated`

**Notification listener:** `app/Listeners/BroadcastNotificationCreated.php` listens on Laravel's `NotificationSent` event (auto-discovered). Broadcasts `NewNotification` when a database notification is sent.

**Frontend pattern (9 views modified):** Each view adds Echo listeners alongside existing polling. When WS connects, polling intervals are slowed 2-5x. Example:
```javascript
if (window.Echo) {
    window.Echo.channel('server.' + serverId)
        .listen('.kill.new', (e) => { /* prepend kill */ });
    pollInterval = 60000; // slow from 12s to 60s
}
```

**Critical:** `Event::dispatch()` does NOT support PHP named parameters. Always use positional arguments matching the constructor order. Named params like `KillFeedUpdated::dispatch(serverId: $id)` will throw `Unknown named parameter` errors.

**Infrastructure:**
- Systemd: `/etc/systemd/system/reverb.service` — `php artisan reverb:start --host=127.0.0.1 --port=8085`
- Nginx: WSS proxy at `/app` and `/apps` → `http://127.0.0.1:8085`
- Memory: ~37MB RAM for the Reverb process
- Management: `systemctl start/stop/restart reverb`, logs at `/var/log/reverb.log`

### Server Status Widget

Embeddable server status widget for external sites:
- `/servers/{id}/widget` — Standalone HTML widget (no layout, auto-refreshes every 60s)
- `/servers/{id}/widget/api` — Public JSON endpoint (no auth required)
- `/servers/{id}/embed` — Embed code generator with live preview, theme (dark/light), accent color picker, compact mode

### Stats Export

`StatsExportController` provides CSV/JSON export:
- Player stats CSV, match history CSV
- Leaderboard CSV and JSON with metadata
- Streamed responses for large datasets
- Export buttons on profile and leaderboard pages

### Metrics & Tracking (`/admin/metrics`)

`MetricsController` provides an admin dashboard for analytics, API usage, and system performance. Three tabs with AJAX-loaded Chart.js charts following the same pattern as the server performance page.

**Architecture:**
- `TrackAnalytics` terminable middleware inserts events into `analytics_events` after the response is sent (zero latency impact)
- `MetricsTracker` service provides `trackPageView()`, `trackApiRequest()`, `trackFeatureUse()` — all try/catch wrapped
- `CollectSystemMetrics` command (`metrics:collect`) runs every 5 minutes, inserting system snapshots into `system_metrics`
- API percentiles computed via PostgreSQL `percentile_cont()` (same PostgreSQL-only pattern as other admin features)

**Controller methods:**
- `index()` — Server-rendered summary stats (page views 24h, API requests 24h, unique visitors, feature uses, tournament regs 30d, team apps 30d)
- `apiAnalyticsData(Request)` — AJAX: page views over time (hourly via `date_trunc`), top 15 pages, feature adoption
- `apiUsageData(Request)` — AJAX: per-token requests (joined with `personal_access_tokens`), top endpoints with avg/P95 response time, error rate
- `apiPerformanceData(Request)` — AJAX: time-series from `system_metrics` (P50/P95/P99, memory, CPU, cache hit rate, queue size/jobs)

**Time ranges:** 6h, 24h, 72h, 7d (parsed via `parseRange()` helper)

**View (`resources/views/admin/metrics/index.blade.php`):**
- 6 summary stat cards (server-rendered)
- Alpine.js tabs: Analytics, API Usage, Performance
- Charts: page views line, API requests line, API response times multi-line (P50/P95/P99), cache hit rate, system memory, queue jobs
- Follows `admin/server/performance.blade.php` pattern exactly

## Terminology

The codebase uses "platoon" in user-facing text and "team" in code (models, controllers, database). These terms are interchangeable.

## Testing

```bash
composer test
```

Uses in-memory SQLite with array cache/queue for isolation. Tests in `tests/Feature/` and `tests/Unit/`.

**Known issues:**
- Tests using PostgreSQL-specific migrations (e.g. `ALTER COLUMN ... TYPE`) will fail with SQLite. This is a pre-existing incompatibility, not a bug in application code.
- The `server_status` test is skipped due to schema mismatch between test SQLite and production PostgreSQL.

## Database & Backups

Production database is PostgreSQL (`DB_CONNECTION=pgsql`). Automated nightly backups to Backblaze B2 via `/root/backup-db.sh` (cron at 03:00). Rotation: 7 daily + 4 weekly. Backup logs at `/var/log/backup-db.log`. Rclone configured with remote name `backblaze`, bucket `ArmaBattles`.

```bash
# Manual backup
bash /root/backup-db.sh

# Restore from backup
rclone copy backblaze:ArmaBattles/daily/<filename>.sql.gz /root/backups/
gunzip /root/backups/<filename>.sql.gz
psql -U reforger -h 127.0.0.1 reforger_community < /root/backups/<filename>.sql
```
