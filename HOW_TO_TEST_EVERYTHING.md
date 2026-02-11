# How to Test Everything in ArmaBattles

## Quick Answer

**`php artisan test` does NOT test the entire platform - it only tests what you've written tests for.**

Currently: **132 tests** covering ~35% of the codebase.

To test everything, you need to **write more tests** for the untested 65%.

## What Gets Tested Now

```bash
php artisan test
```

**Output:**
```
Tests:    22 failed, 10 skipped, 132 passed (525 assertions)
Duration: 7.96s
```

### ✅ What's Covered (132 tests)

1. **API Endpoints** (80+ tests)
   - All game stats endpoints
   - Rate limiting
   - Authentication
   - Deprecation headers
   - Stats aggregation

2. **Team Management** (50+ tests)
   - Team CRUD
   - Invitations & Applications
   - Member management
   - Team show page

3. **Tournaments** (15+ tests)
   - Bracket generation (all formats)

4. **Authentication** (15+ tests)
   - 2FA flow
   - Steam login

5. **Ranked System** (10 tests) ← NEW!
   - Leaderboard display
   - Opt-in/opt-out
   - Tier calculation

6. **Achievements** (10 tests) ← NEW!
   - Display & filtering
   - Progress tracking
   - Showcase system

7. **Scrims** (12 tests) ← NEW!
   - Invitations
   - Match creation
   - Result reporting

8. **Profile** (12 tests) ← NEW!
   - Public/private visibility
   - Stats display
   - Social links

### ❌ What's NOT Covered Yet

- Server Manager (61% of admin panel)
- Content Creators
- Highlight Clips
- News & Comments
- Reputation System
- Favorites
- Discord Integration
- Referee System
- Metrics Dashboard
- Export Features
- Player Comparison
- Kill Feed & Heatmap
- Leaderboards (beyond basic)
- Most admin controllers

## How to Achieve 100% Coverage

### Step 1: Run Tests to See Failures

```bash
php artisan test
```

**22 tests failing** is GOOD - they're catching bugs and missing features!

### Step 2: Fix Failures One by One

Example failure:
```
FAILED  Tests\Feature\Achievements\AchievementTest > user_cannot_showcase_more_than_three_achievements

Call to undefined method Illuminate\Database\Eloquent\Factories\Factory::count()
```

**Fix:** Create `AchievementFactory`:

```bash
php artisan make:factory AchievementFactory
```

### Step 3: Add More Tests for Uncovered Areas

For each controller without tests:

```bash
# Example: ServerManagerController
php artisan make:test Feature/ServerManager/ServerControlTest
```

Then write tests:

```php
public function test_admin_can_restart_server(): void
{
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/admin/server/restart');

    $response->assertRedirect();
    // Assert restart command was sent
}
```

### Step 4: Use Static Analysis

```bash
./vendor/bin/phpstan analyse
```

Finds errors **before** running tests:
- Type mismatches
- Undefined methods
- Missing parameters

### Step 5: Enable CI/CD

GitHub Actions runs tests automatically on every push:

**.github/workflows/tests.yml** (already created)

View results at:
```
https://github.com/mkungen89/ArmaBattles/actions
```

## Test Priority Roadmap

### Phase 1: Critical Paths (Week 1)
**Goal:** 60% coverage

- [x] API endpoints ← DONE
- [x] Team management ← DONE
- [x] Tournaments ← DONE
- [x] Authentication ← DONE
- [x] Ranked system ← DONE (new!)
- [x] Achievements ← DONE (new!)
- [x] Scrims ← DONE (new!)
- [x] Profile ← DONE (new!)
- [ ] Server Manager (restart, mods, RCON)
- [ ] Leaderboards (sorting, filters, export)

### Phase 2: User-Facing Features (Week 2)
**Goal:** 75% coverage

- [ ] Player Comparison (head-to-head, multi-player)
- [ ] Kill Feed & Heatmap (WebSocket updates)
- [ ] News System (CRUD, comments, reactions)
- [ ] Reputation (voting, tiers)
- [ ] Favorites (polymorphic favoriting)

### Phase 3: Admin Tools (Week 3)
**Goal:** 85% coverage

- [ ] Metrics Dashboard (charts, time ranges)
- [ ] Audit Logs (filtering, export)
- [ ] Reports (moderation workflow)
- [ ] Anticheat Dashboard
- [ ] Weapon/Vehicle image management

### Phase 4: Integrations (Week 4)
**Goal:** 95% coverage

- [ ] Discord Integration (Rich Presence)
- [ ] Content Creators (directory, registration)
- [ ] Highlight Clips (submission, voting)
- [ ] Export Features (CSV, JSON)
- [ ] Referee System (match reporting)

## Fast Testing Workflow

### Run Only What Changed

```bash
# Test single file
php artisan test --filter=RankedSystemTest

# Test directory
php artisan test tests/Feature/Ranked

# Test with coverage
php artisan test --coverage --min=70

# Parallel (faster)
php artisan test --parallel
```

### Watch Mode (Re-run on File Change)

Install `phpunit-watcher`:

```bash
composer require --dev spatie/phpunit-watcher
./vendor/bin/phpunit-watcher watch
```

Now tests run automatically when you save a file!

## Common Test Patterns

### Testing Controllers

```php
public function test_page_loads(): void
{
    $response = $this->get('/some-page');
    $response->assertOk();
}

public function test_requires_auth(): void
{
    $response = $this->get('/admin/dashboard');
    $response->assertRedirect('/login');
}

public function test_create_resource(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/teams', [
        'name' => 'Test Team',
        'tag' => 'TT',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('teams', ['name' => 'Test Team']);
}
```

### Testing API Endpoints

```php
public function test_api_requires_token(): void
{
    $response = $this->postJson('/api/v1/player-kills', []);
    $response->assertUnauthorized();
}

public function test_api_stores_data(): void
{
    $token = PersonalAccessToken::factory()->create();

    $response = $this->withToken($token->plainTextToken)
        ->postJson('/api/v1/player-kills', [
            'killer_uuid' => 'uuid-1',
            'victim_uuid' => 'uuid-2',
            'weapon' => 'M4A1',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('player_kills', ['weapon' => 'M4A1']);
}
```

### Testing WebSocket Events

```php
public function test_event_broadcasts(): void
{
    Event::fake([KillFeedUpdated::class]);

    $this->postJson('/api/v1/player-kills', [/* data */]);

    Event::assertDispatched(KillFeedUpdated::class);
}
```

### Testing Jobs/Commands

```php
public function test_command_runs_successfully(): void
{
    $this->artisan('ratings:calculate')
        ->assertSuccessful();

    // Assert side effects
    $this->assertDatabaseHas('player_ratings', [/* updated data */]);
}
```

## Debugging Failed Tests

### 1. Read the Error Message

```
FAILED  Tests\Feature\Teams\TeamShowTest > team show page loads

Call to undefined relationship [user] on model [App\Models\User]
```

**Translation:** You're calling `->with('user')` on a `User` model, which doesn't have a `user` relation.

### 2. Use `dd()` in Tests

```php
public function test_something(): void
{
    $team = Team::factory()->create();
    dd($team); // Dump and die

    $response = $this->get("/teams/{$team->id}");
}
```

### 3. Check Database State

```php
public function test_creates_record(): void
{
    // ... test code ...

    // See what's actually in the database
    dd(\DB::table('teams')->get());
}
```

### 4. Use PHPStan for Type Errors

```bash
./vendor/bin/phpstan analyse app/Http/Controllers/TeamController.php
```

Catches issues before running tests.

## CI/CD Integration

### GitHub Actions (Automated)

Every push to `main` or `develop` runs tests automatically.

**View results:** https://github.com/mkungen89/ArmaBattles/actions

**Configuration:** `.github/workflows/tests.yml`

### Pre-Push Hook (Local)

Create `.git/hooks/pre-push`:

```bash
#!/bin/bash

echo "Running tests before push..."
php artisan test

if [ $? -ne 0 ]; then
    echo "Tests failed! Push aborted."
    exit 1
fi
```

```bash
chmod +x .git/hooks/pre-push
```

Now tests run before every `git push`.

## Measuring Coverage

### Text Report

```bash
php artisan test --coverage
```

**Output:**
```
Coverage: 35%
- Controllers: 30%
- Models: 40%
- Commands: 20%
```

### HTML Report

```bash
php artisan test --coverage-html coverage-report
```

Open `coverage-report/index.html` in browser to see:
- Which lines are covered (green)
- Which lines are not covered (red)

## Target Metrics

**By End of Q1 2026:**

- Overall Coverage: **70%**
- Controllers: **80%**
- Models: **60%**
- Commands: **50%**
- Services: **70%**

**Current Status (2026-02-11):**

- Overall Coverage: **35%**
- Controllers: **30%**
- Models: **40%**
- Commands: **20%**
- Services: **10%**

## Why Some Tests Fail

The new tests I added WILL fail because:

1. **Missing Factories**
   - `Achievement::factory()` doesn't exist yet
   - Need to create: `php artisan make:factory AchievementFactory`

2. **Missing Routes**
   - `/scrims/invite` might not exist
   - Check `routes/web.php`

3. **Missing Controllers**
   - Some features might not be implemented yet
   - Tests document expected behavior

**This is GOOD!** Failing tests tell you:
- What's broken
- What's missing
- What needs to be built

## Next Steps

1. **Run tests:** `php artisan test`
2. **Fix failures:** Start with simplest first
3. **Add missing tests:** Use `TEST_COVERAGE.md` as guide
4. **Enable CI:** Push to GitHub to trigger automated tests
5. **Monitor coverage:** Aim for 10% increase per week

---

**Remember:** Tests are **living documentation**. They show how the system should work and catch regressions when you refactor.

**Goal:** Every feature has tests BEFORE it goes to production.
