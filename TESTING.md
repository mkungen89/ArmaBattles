# Testing Guide for ArmaBattles

## Automated Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test --filter=TeamManagementTest

# Run with coverage
php artisan test --coverage

# Run in parallel (faster)
php artisan test --parallel
```

### Static Analysis (PHPStan/Larastan)

```bash
# Run static analysis
./vendor/bin/phpstan analyse

# Run with higher strictness (level 0-9)
./vendor/bin/phpstan analyse --level=6

# Fix autoload issues first
composer dump-autoload
```

**What it catches:**
- Type mismatches
- Undefined methods/properties
- Incorrect return types
- Unused variables
- N+1 query problems (sometimes)

### Writing New Tests

**Feature Test Example:**
```php
public function test_team_show_page_loads(): void
{
    $team = Team::factory()->create();

    $response = $this->get("/teams/{$team->id}");

    $response->assertOk();
    $response->assertSee($team->name);
}
```

**When to Write Tests:**
- ✅ New controllers/routes
- ✅ Complex business logic
- ✅ Bug fixes (regression tests)
- ✅ Authentication/authorization
- ✅ API endpoints

## Manual Testing Workflows

### 1. **Teams (Platoons) - Critical Path**

**Create Team:**
1. Login as new user
2. Go to `/teams/create`
3. Fill form (name, tag, description)
4. Upload avatar (optional)
5. ✅ Check: Team appears at `/teams`
6. ✅ Check: You're listed as captain at `/teams/my`

**Invite Member:**
1. Login as captain
2. Go to `/teams/my`
3. Search for player by Steam ID
4. Send invitation
5. ✅ Check: Invitation appears in pending list
6. Logout, login as invited user
7. ✅ Check: Notification received
8. Accept invitation
9. ✅ Check: User added to team roster

**View Team Page:**
1. Go to `/teams/{id}`
2. ✅ Check: All members load (no "Call to undefined relationship [user]" error)
3. ✅ Check: Stats display correctly
4. ✅ Check: Combat stats aggregate all members
5. ✅ Check: Recent matches load

**Edge Cases:**
- Try inviting user already on a team → error
- Try applying to team you're already on → error
- Captain leaves team with active members → error
- Disband team with active tournament → error

### 2. **Tournaments - Critical Path**

**Create Tournament:**
1. Login as admin
2. Go to `/admin/tournaments/create`
3. Set name, dates, format
4. ✅ Check: Tournament appears at `/tournaments`

**Team Registration:**
1. Login as team captain
2. Go to `/teams/my`
3. Register for tournament
4. ✅ Check: Registration shows in pending list

**Bracket Generation:**
1. Admin approves teams
2. Close registration
3. Start tournament
4. ✅ Check: Bracket renders correctly
5. ✅ Check: All teams appear

**Match Flow:**
1. Team checks in
2. Match goes live
3. ✅ Check: Match chat works (if implemented)
4. Referee reports score
5. ✅ Check: Winner advances in bracket

### 3. **Game Stats API - Critical Path**

**Send Event:**
```bash
curl -X POST https://armabattles.com/api/v1/player-kills \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "killer_uuid": "test-uuid",
    "killer_name": "TestPlayer",
    "victim_uuid": "victim-uuid",
    "victim_name": "Victim",
    "weapon": "M4A1",
    "distance": 150,
    "is_headshot": true,
    "server_id": 1
  }'
```

✅ Check:
1. Event stored in `player_kills` table
2. `player_stats.kills` incremented
3. `player_stats.headshots` incremented (if headshot)
4. Kill feed updates via WebSocket

### 4. **WebSocket/Reverb - Real-time Features**

**Kill Feed:**
1. Open `/kill-feed` in browser
2. Send kill event via API (see above)
3. ✅ Check: Kill appears immediately (no refresh needed)

**Notifications:**
1. Login as User A
2. In another browser, invite User A to a team
3. ✅ Check: Notification bell updates in real-time
4. ✅ Check: Desktop notification appears (if enabled)

**Server Status:**
1. Open `/servers/{id}`
2. Game server sends status update
3. ✅ Check: Player count updates live

### 5. **Admin Panel - Critical Endpoints**

**Server Manager:**
1. Login as admin
2. Go to `/admin/server`
3. ✅ Check: Dashboard loads
4. ✅ Check: Live metrics polling works
5. Click "Restart Server"
6. ✅ Check: Confirmation modal appears
7. ✅ Check: Restart executes (check logs)

**Game Stats Dashboard:**
1. Go to `/admin/game-stats`
2. ✅ Check: Player profiles load
3. Search for player
4. ✅ Check: Stats appear
5. View event tables
6. ✅ Check: Pagination works

**Metrics:**
1. Go to `/admin/metrics`
2. ✅ Check: Charts render
3. Change time range
4. ✅ Check: Data updates

### 6. **Ranked System - Competitive Rating**

**Opt-in:**
1. Login with player_uuid set
2. Go to `/ranked`
3. Click "Opt In"
4. ✅ Check: User now visible on leaderboard

**Placement Games:**
1. Play 10 competitive-eligible events
2. ✅ Check: Progress shows "X/10 placement games"
3. Complete 10 games
4. ✅ Check: Tier assigned
5. ✅ Check: Rank visible on profile

**Rating Updates:**
1. Game server sends kill event
2. ✅ Check: Kill queued in `rated_kills_queue`
3. Run `php artisan ratings:calculate`
4. ✅ Check: Rating updated in `player_ratings`
5. ✅ Check: History entry created in `rating_history`

## Error Scenarios to Test

### Common Bugs to Catch

**1. N+1 Queries:**
```bash
# Enable query log
php artisan telescope:install

# Visit page
# Check /telescope/queries
# Look for 100+ queries on single page load
```

**Fix:** Add `->with(['relation'])` to eager load.

**2. Undefined Relationship:**
```
Call to undefined relationship [user] on model [App\Models\User]
```

**How to test:**
- Visit every team/tournament/match page
- Check console for errors
- Run feature tests

**3. Memory Leaks:**
```bash
# Monitor memory during long-running commands
php artisan server:track --server-id=1

# Watch for memory increase
watch -n 1 'ps aux | grep artisan'
```

**4. WebSocket Disconnects:**
- Open DevTools → Network → WS tab
- Watch for disconnects
- Check Reverb logs: `journalctl -u reverb -f`

**5. Race Conditions:**
- Two users accept same team invitation simultaneously
- Two teams register for last tournament slot
- Match result submitted twice

**Test:** Use browser automation (Dusk) to simulate concurrent requests.

## Performance Testing

### Load Testing with Apache Bench

```bash
# 100 requests, 10 concurrent
ab -n 100 -c 10 https://armabattles.com/

# POST API endpoint
ab -n 100 -c 10 -p payload.json -T application/json \
   -H "Authorization: Bearer TOKEN" \
   https://armabattles.com/api/v1/player-kills
```

### Database Query Performance

```sql
-- Find slow queries
SELECT * FROM pg_stat_statements
ORDER BY mean_exec_time DESC
LIMIT 10;

-- Check missing indexes
SELECT schemaname, tablename, attname, n_distinct, correlation
FROM pg_stats
WHERE schemaname = 'public'
  AND n_distinct > 100
ORDER BY abs(correlation) ASC;
```

### Cache Hit Rate

```bash
# Check Redis stats
redis-cli INFO stats | grep hits

# Check Laravel cache
php artisan tinker
>>> Cache::get('leaderboard:kills:all-time');
```

## Continuous Integration (GitHub Actions)

The `.github/workflows/tests.yml` file runs tests automatically on:
- Every push to `main` or `develop`
- Every pull request

**View results:**
https://github.com/mkungen89/ArmaBattles/actions

## Debugging Tools

### 1. **Laravel Telescope**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Visit `/telescope` to see:
- All queries (find N+1 problems)
- All exceptions
- All requests/responses
- Cache hits/misses
- Jobs/queues

### 2. **Laravel Debugbar**
```bash
composer require barryvdh/laravel-debugbar --dev
```

Shows at bottom of page:
- Queries with execution time
- View rendering time
- Route info
- Session data

### 3. **Ray** (Paid, but excellent)
```bash
composer require spatie/laravel-ray
```

```php
// In controller
ray($team->activeMembers);
ray()->showQueries();
```

Desktop app shows all debugging output.

## Pre-Deployment Checklist

Before pushing to production:

- [ ] Run `php artisan test` → all green
- [ ] Run `./vendor/bin/phpstan analyse` → no errors
- [ ] Check `/telescope/exceptions` → no recent errors
- [ ] Test critical paths manually (teams, tournaments, API)
- [ ] Check database migrations: `php artisan migrate --pretend`
- [ ] Review `git diff` for debugging code (dd(), ray(), console.log)
- [ ] Check `.env` for correct values
- [ ] Verify SSL certificate valid
- [ ] Test WebSocket connection (open DevTools → WS tab)
- [ ] Run `npm run build` → no errors
- [ ] Check disk space: `df -h`
- [ ] Check memory: `free -m`
- [ ] Backup database: `bash /root/backup-db.sh`

## Post-Deployment Monitoring

After deployment:

```bash
# Watch logs
tail -f storage/logs/laravel.log

# Watch Reverb
journalctl -u reverb -f

# Watch HTTP errors
tail -f /var/log/nginx/error.log

# Watch queue
php artisan queue:monitor

# Check for failed jobs
php artisan queue:failed
```

## Rollback Procedure

If deployment breaks production:

```bash
# 1. Revert code
git revert HEAD
git push

# 2. Rollback migrations (if any)
php artisan migrate:rollback

# 3. Restore database backup (last resort)
# See CLAUDE.md Database & Backups section

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 5. Restart services
sudo systemctl restart reverb
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

## Common Test Failures & Fixes

### "Class 'Database\Factories\TeamFactory' not found"
```bash
composer dump-autoload
```

### "SQLSTATE[HY000]: General error: 1 no such table"
```bash
php artisan migrate:fresh --env=testing
```

### "WebSocket connection failed"
```bash
# Check Reverb is running
sudo systemctl status reverb

# Restart Reverb
sudo systemctl restart reverb

# Check ports
ss -tlnp | grep 8085
```

### "Route [teams.show] not defined"
```bash
php artisan route:clear
php artisan route:cache
```

## Test Coverage Goals

**Current coverage:** ~40% (estimate based on existing tests)

**Target coverage by area:**
- Controllers: 80%+ (critical paths covered)
- Models: 60%+ (relationships, scopes, accessors)
- Commands: 50%+ (critical commands)
- Services: 70%+ (business logic)
- API: 90%+ (all endpoints)

**How to measure:**
```bash
php artisan test --coverage --min=70
```

---

**Last Updated:** 2026-02-11
**Maintained By:** ArmaBattles Development Team
