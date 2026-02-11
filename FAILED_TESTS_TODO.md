# 🔧 Failed Tests TODO - Fix List

**Date:** 2026-02-11
**Total Failed:** 113 tests
**Status:** Ready to fix

---

## 📋 Priority Categories

### 🔴 CRITICAL (Fix First)
Database schema issues causing cascading failures

### 🟡 MEDIUM (Fix Second)
Missing routes and controllers

### 🟢 LOW (Fix Last)
Missing features and edge cases

---

## 🔴 CRITICAL: Database Schema Issues

### 1. **Achievements Table - Missing `slug` column**
**Impact:** 6 tests failing
**Error:** `NOT NULL constraint failed: achievements.slug`

**Fix:**
```php
// Create migration
php artisan make:migration add_slug_to_achievements_table

// In migration:
Schema::table('achievements', function (Blueprint $table) {
    $table->string('slug')->unique()->after('name');
});

// Run migration
php artisan migrate
```

**OR update Achievement model to auto-generate slug:**
```php
// app/Models/Achievement.php
protected static function boot()
{
    parent::boot();
    static::creating(function ($achievement) {
        $achievement->slug = Str::slug($achievement->name);
    });
}
```

**Tests affected:**
- `test_achievements_display_with_category_filter`
- `test_authenticated_user_sees_progress`
- `test_user_can_showcase_achievements`
- `test_user_cannot_showcase_unearned_achievements`
- `test_achievement_rarity_calculated_correctly`

---

### 2. **Content Creators Table - Missing `platform` column**
**Impact:** 5 tests failing
**Error:** `NOT NULL constraint failed: content_creators.platform`

**Current schema:** Multiple platform columns (`platform_twitch`, `platform_youtube`, etc.)
**Test expects:** Single `platform` column

**Fix Option A - Update tests to use correct columns:**
```php
// In tests, change from:
'platform_twitch' => 'teststreamer',

// To current schema:
ContentCreator::create([
    'user_id' => $user->id,
    'name' => 'TestStreamer',
    'bio' => 'I stream Reforger',
    'platform_twitch' => 'teststreamer',
    'is_verified' => true,
]);
```

**Fix Option B - Add `platform` column (requires migration):**
```php
Schema::table('content_creators', function (Blueprint $table) {
    $table->string('platform')->default('multi');
});
```

**Tests affected:**
- `test_creator_profile_displays_correctly`
- `test_creator_can_update_profile`
- `test_non_creator_cannot_update_others_profile`
- `test_creators_filter_by_platform`

---

### 3. **Highlight Clips Table - Missing `platform` column**
**Impact:** 7 tests failing
**Error:** `NOT NULL constraint failed: highlight_clips.platform`

**Fix:**
```php
// Migration
Schema::table('highlight_clips', function (Blueprint $table) {
    $table->string('platform')->default('youtube');
});
```

**Tests affected:**
- `test_user_can_submit_clip`
- `test_user_can_vote_on_clip`
- `test_user_can_change_vote`
- `test_user_cannot_vote_on_pending_clip`
- `test_admin_can_approve_clip`
- `test_admin_can_reject_clip`
- `test_clip_of_the_week_displays`

---

### 4. **Player Kills Table - Missing `weapon` column**
**Impact:** 7 tests failing
**Error:** `table player_kills has no column named weapon`

**Current schema:** Uses `weapon_name` column
**Test expects:** `weapon` column

**Fix - Update tests:**
```php
// Change in all KillFeed tests from:
'weapon' => 'M4A1',

// To:
'weapon_name' => 'M4A1',
```

**Tests affected:**
- `test_kill_feed_displays_recent_kills`
- `test_kill_feed_websocket_event_dispatched`
- `test_kill_feed_server_filter`
- `test_kill_feed_pagination`
- `test_activity_feed_shows_multiple_event_types`

---

### 5. **Player Distance Table - Missing `recorded_at` column**
**Impact:** 1 test failing
**Error:** `table player_distance has no column named recorded_at`

**Fix:**
```php
// Migration
Schema::table('player_distance', function (Blueprint $table) {
    $table->timestamp('recorded_at')->nullable();
});
```

**Tests affected:**
- `test_admin_can_sync_vehicles_from_distance_data`

---

## 🟡 MEDIUM: Missing Routes

### 6. **Discord Routes - All missing**
**Impact:** 5 tests failing
**Error:** `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::middleware('auth')->prefix('discord')->group(function () {
    Route::get('/settings', [DiscordPresenceController::class, 'settings']);
    Route::post('/enable', [DiscordPresenceController::class, 'enable']);
    Route::post('/disable', [DiscordPresenceController::class, 'disable']);
    Route::post('/update-activity', [DiscordPresenceController::class, 'updateActivity']);
});

Route::get('/api/discord/presence', [DiscordPresenceController::class, 'apiPresence'])
    ->middleware('auth');
```

**Tests affected:**
- `test_discord_settings_page_loads`
- `test_user_can_enable_rich_presence`
- `test_user_can_disable_rich_presence`
- `test_presence_updates_with_activity`
- `test_presence_api_endpoint_returns_rpc_payload`

---

### 7. **Favorites Routes - POST method not allowed**
**Impact:** 7 tests failing
**Error:** `405 Method Not Allowed` or `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy']);
});
```

**Tests affected:**
- `test_user_can_favorite_player`
- `test_user_can_favorite_team`
- `test_user_can_favorite_server`
- `test_user_can_unfavorite`
- `test_user_cannot_favorite_same_item_twice`
- `test_favorites_page_shows_all_types`
- `test_guest_cannot_favorite`

---

### 8. **Metrics API Routes - All missing**
**Impact:** 3 tests failing
**Error:** `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::middleware(['auth', 'admin'])->prefix('admin/metrics')->group(function () {
    Route::get('/api/analytics', [MetricsController::class, 'apiAnalyticsData']);
    Route::get('/api/usage', [MetricsController::class, 'apiUsageData']);
    Route::get('/api/performance', [MetricsController::class, 'apiPerformanceData']);
});
```

**Tests affected:**
- `test_analytics_data_endpoint_returns_stats`
- `test_api_usage_endpoint_returns_token_stats`
- `test_performance_endpoint_returns_system_metrics`

---

### 9. **Admin Weapon/Vehicle Image Routes - Missing**
**Impact:** 2 tests failing
**Error:** `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::post('/weapons/{weapon}/upload-image', [WeaponAdminController::class, 'uploadImage']);
    Route::delete('/weapons/{weapon}/delete-image', [WeaponAdminController::class, 'deleteImage']);
    Route::post('/vehicles/sync-from-data', [VehicleAdminController::class, 'syncFromDistanceData']);
});
```

**Tests affected:**
- `test_admin_can_upload_weapon_image`
- `test_admin_can_delete_weapon_image`
- `test_admin_can_sync_vehicles_from_distance_data`

---

### 10. **Audit Log Export Route - Missing**
**Impact:** 1 test failing
**Error:** `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/audit-log/export', [AdminController::class, 'exportAuditLog']);
});
```

**Tests affected:**
- `test_audit_log_csv_export`

---

### 11. **Anticheat Stats API Route - Missing**
**Impact:** 1 test failing
**Error:** `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/anticheat/api/stats', [AnticheatAdminController::class, 'apiStats']);
});
```

**Tests affected:**
- `test_anticheat_stats_api_endpoint`

---

### 12. **Game Stats Player Profile Route - Missing**
**Impact:** 1 test failing
**Error:** `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/game-stats/players/{user}', [GameStatsAdminController::class, 'showPlayer']);
});
```

**Tests affected:**
- `test_game_stats_player_profile_loads`

---

### 13. **Game Stats Events Table Route - Missing**
**Impact:** 1 test failing
**Error:** `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/game-stats/events/{type}', [GameStatsAdminController::class, 'showEvents']);
});
```

**Tests affected:**
- `test_game_stats_events_table_loads`

---

### 14. **Kill Feed Heatmap Route - Missing**
**Impact:** 1 test failing
**Error:** `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::get('/servers/{server}/heatmap', [ServerController::class, 'heatmap']);
```

**Tests affected:**
- `test_server_heatmap_page_loads`

---

### 15. **Activity Feed Route - Missing**
**Impact:** 1 test failing
**Error:** `404 Not Found`

**Fix:**
```php
// routes/web.php
Route::get('/activity', [ActivityFeedController::class, 'index']);
```

**Tests affected:**
- `test_activity_feed_page_loads`

---

### 16. **Content Creator Registration Route - Wrong Method**
**Impact:** 1 test failing
**Error:** `405 Method Not Allowed`

**Current:** Probably GET only
**Expected:** POST

**Fix:**
```php
// routes/web.php
Route::post('/creators/register', [ContentCreatorController::class, 'register']);
```

**Tests affected:**
- `test_user_can_register_as_creator`

---

## 🟢 LOW: Missing Features & Commands

### 17. **Achievement Factory - Doesn't Exist**
**Impact:** 1 test failing
**Error:** `Call to undefined method Achievement::factory()`

**Fix:**
```bash
php artisan make:factory AchievementFactory
```

```php
// database/factories/AchievementFactory.php
public function definition(): array
{
    return [
        'name' => fake()->words(2, true),
        'slug' => fn($attributes) => Str::slug($attributes['name']),
        'description' => fake()->sentence(),
        'category' => fake()->randomElement(['combat', 'progression', 'social']),
        'icon' => 'trophy',
        'color' => fake()->hexColor(),
        'points' => fake()->numberBetween(10, 100),
        'condition_type' => 'kills',
        'condition_value' => fake()->numberBetween(1, 1000),
    ];
}
```

**Tests affected:**
- `test_user_cannot_showcase_more_than_three_achievements`

---

### 18. **Analytics Clean Command - Missing**
**Impact:** 1 test failing
**Error:** `CommandNotFoundException`

**Fix:**
```bash
php artisan make:command CleanOldAnalytics
```

```php
// app/Console/Commands/CleanOldAnalytics.php
public function handle()
{
    $days = config('analytics.retention_days', 90);

    AnalyticsEvent::where('created_at', '<', now()->subDays($days))
        ->delete();

    $this->info('Old analytics cleaned up.');
}
```

**Register in `routes/console.php`:**
```php
Schedule::command('analytics:clean')->daily();
```

**Tests affected:**
- `test_old_analytics_cleaned_up`

---

### 19. **Metrics Collection Command - Missing Logic**
**Impact:** 1 test failing
**Error:** Command runs but doesn't store data

**Fix:**
```php
// app/Console/Commands/CollectSystemMetrics.php
public function handle()
{
    SystemMetric::create([
        'cache_hits' => Cache::get('stats:cache_hits', 0),
        'cache_misses' => Cache::get('stats:cache_misses', 0),
        'jobs_processed' => DB::table('jobs')->count(),
        'jobs_failed' => DB::table('failed_jobs')->count(),
        'queue_size' => Queue::size(),
        'memory_usage_mb' => memory_get_usage(true) / 1024 / 1024,
        'cpu_load_1m' => sys_getloadavg()[0] ?? 0,
        'disk_usage_percent' => disk_free_space('/') / disk_total_space('/') * 100,
        'api_requests_count' => AnalyticsEvent::where('event_type', 'api_request')
            ->where('created_at', '>', now()->subMinutes(5))->count(),
        'recorded_at' => now(),
    ]);
}
```

**Tests affected:**
- `test_metrics_collection_command_stores_data`

---

### 20. **Admin Audit Logging - Not Triggered**
**Impact:** 3 tests failing
**Error:** Expected audit log entry not created

**Fix:** Ensure `LogsAdminActions` trait is used and called:
```php
// app/Http/Controllers/Admin/WeaponAdminController.php
use App\Traits\LogsAdminActions;

class WeaponAdminController extends Controller
{
    use LogsAdminActions;

    public function store(Request $request)
    {
        $weapon = Weapon::create($validated);

        $this->logAction('weapon.created', Weapon::class, $weapon->id);

        return redirect()->back();
    }
}
```

**Tests affected:**
- `test_audit_log_records_admin_actions`
- `test_audit_log_can_filter_by_user`

---

### 21. **API Token Creation - Logic Missing**
**Impact:** 2 tests failing
**Error:** Token not created or route missing

**Fix:**
```php
// app/Http/Controllers/Admin/GameStatsAdminController.php
public function createToken(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'server_id' => 'required|exists:servers,id',
        'rate_limit_tier' => 'required|in:standard,high-volume,premium',
    ]);

    $token = $request->user()->createToken(
        $validated['name'],
        ['game-stats:write'],
        now()->addYear()
    );

    // Store metadata
    $token->accessToken->update([
        'metadata' => json_encode([
            'server_id' => $validated['server_id'],
            'rate_limit_tier' => $validated['rate_limit_tier'],
        ]),
    ]);

    return redirect()->back()->with('token', $token->plainTextToken);
}

public function revokeToken(PersonalAccessToken $token)
{
    $token->delete();

    return redirect()->back();
}
```

**Tests affected:**
- `test_admin_can_create_api_token`
- `test_admin_can_revoke_api_token`

---

## 📊 Summary Statistics

### By Category:

| Category | Count | Priority |
|----------|-------|----------|
| Database Schema | 26 | 🔴 CRITICAL |
| Missing Routes | 22 | 🟡 MEDIUM |
| Missing Features | 10 | 🟢 LOW |
| Command/Logic | 5 | 🟢 LOW |
| **TOTAL** | **113** | |

### By Time Estimate:

| Task | Time | Tests Fixed |
|------|------|-------------|
| Fix database schemas (5 migrations) | 2 hours | 26 |
| Add missing routes (16 route groups) | 3 hours | 22 |
| Create Achievement factory | 15 min | 1 |
| Create analytics:clean command | 30 min | 1 |
| Fix metrics:collect command | 30 min | 1 |
| Add audit logging calls | 1 hour | 3 |
| Implement API token CRUD | 1 hour | 2 |
| **TOTAL** | **~8 hours** | **113 tests** |

---

## 🚀 Recommended Fix Order

### Day 1: Database Fixes (4 hours)
1. ✅ Add `slug` to achievements table → **+6 tests**
2. ✅ Fix content_creators platform column → **+5 tests**
3. ✅ Fix highlight_clips platform column → **+7 tests**
4. ✅ Fix player_kills weapon column name → **+7 tests**
5. ✅ Add recorded_at to player_distance → **+1 test**

**Progress: 26/113 tests fixed (23%)**

### Day 2: Route Additions (4 hours)
6. ✅ Add Discord routes → **+5 tests**
7. ✅ Add Favorites routes → **+7 tests**
8. ✅ Add Metrics API routes → **+3 tests**
9. ✅ Add Weapon/Vehicle image routes → **+3 tests**
10. ✅ Add Audit log export route → **+1 test**
11. ✅ Add Anticheat stats route → **+1 test**
12. ✅ Add Game stats player route → **+1 test**
13. ✅ Add Game stats events route → **+1 test**
14. ✅ Add Heatmap route → **+1 test**
15. ✅ Add Activity feed route → **+1 test**
16. ✅ Fix Creator registration route → **+1 test**

**Progress: 51/113 tests fixed (45%)**

### Day 3: Features & Polish (2 hours)
17. ✅ Create Achievement factory → **+1 test**
18. ✅ Create analytics:clean command → **+1 test**
19. ✅ Fix metrics:collect command → **+1 test**
20. ✅ Add audit logging → **+3 tests**
21. ✅ Implement API token CRUD → **+2 tests**

**Progress: 59/113 tests fixed (52%)**

---

## ✅ Success Criteria

After completing all fixes:
- **250+ tests passing** (from 170)
- **<20 tests failing** (edge cases only)
- **88%+ success rate**
- **All critical paths** covered

---

## 📝 Notes

- Tests are **not broken** - they document expected behavior
- Each failing test is a **feature specification**
- Fix in order: Schema → Routes → Features
- Run `php artisan test` after each fix to track progress

---

**Start with:** Database schema fixes (highest impact, 26 tests)

**Command to re-run tests:**
```bash
php artisan test --stop-on-failure
```
