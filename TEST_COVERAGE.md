# Test Coverage Status

**Last Updated:** 2026-02-11
**Total Tests:** 120 tests, 497 assertions
**Skipped:** 10 tests

## ✅ Well-Tested Areas (>80% coverage)

### API Endpoints (`StatsController`)
- ✅ All write endpoints (kills, damage, distance, etc.)
- ✅ All read endpoints (leaderboards, player stats, events)
- ✅ Authentication (Sanctum bearer tokens)
- ✅ Rate limiting (standard/high-volume/premium)
- ✅ Stats aggregation (kills → player_stats)
- ✅ API deprecation headers (legacy vs v1)

**Test Files:**
- `tests/Feature/Api/GameStatsApiTest.php` (6 tests)
- `tests/Feature/Api/StatsControllerV1Test.php` (20+ tests)
- `tests/Feature/Api/StatsAggregationTest.php` (40+ tests)
- `tests/Feature/Api/RateLimitingTest.php` (9 tests)
- `tests/Feature/Api/DeprecationTest.php` (5 tests)

### Team Management (`TeamController`)
- ✅ Team creation/update/delete
- ✅ Invitations (send, accept, decline)
- ✅ Applications (apply, approve, reject)
- ✅ Member management (kick, promote, demote)
- ✅ Captain transfer
- ✅ Team disbanding
- ✅ Edge cases (duplicate invites, already on team)

**Test Files:**
- `tests/Feature/Teams/TeamManagementTest.php` (45+ tests)
- `tests/Feature/Teams/TeamShowTest.php` (4 tests)

### Tournament System (`TournamentController`)
- ✅ Bracket generation (single/double/round-robin/swiss)
- ✅ Match advancement logic
- ✅ Seeding algorithms

**Test Files:**
- `tests/Feature/Tournaments/BracketGenerationTest.php` (15+ tests)

### Authentication (`TwoFactorController`)
- ✅ 2FA setup flow
- ✅ TOTP verification
- ✅ Recovery codes
- ✅ Steam OAuth login

**Test Files:**
- `tests/Feature/Auth/TwoFactorTest.php` (10+ tests)
- `tests/Feature/Auth/AuthenticationTest.php` (5+ tests)

### Scheduled Commands
- ✅ Cron job execution
- ✅ Command output validation

**Test Files:**
- `tests/Feature/Commands/ScheduledCommandsTest.php` (5+ tests)

## ⚠️ Partially Tested Areas (20-50% coverage)

### Server Management
- ✅ Server status tracking
- ❌ Server control (restart, stop)
- ❌ Mod management
- ❌ RCON commands
- ❌ Performance metrics
- ❌ Scheduled restarts

### User Profiles
- ❌ Profile viewing
- ❌ Stats display
- ❌ Social links
- ❌ Privacy settings

## ❌ Untested Areas (0% coverage)

### Achievements System
**Files:** `AchievementController.php`, `AchievementAdminController.php`
- ❌ Achievement unlocking
- ❌ Progress tracking
- ❌ Showcase management
- ❌ Rarity calculation

**Critical:** Medium (feature works but no regression protection)

### Scrims (Practice Matches)
**Files:** `ScrimController.php`, `ScrimAdminController.php`
- ❌ Scrim creation
- ❌ Team invitations
- ❌ Match scheduling
- ❌ Score reporting

**Critical:** Medium

### Ranked/Competitive Rating
**Files:** `RankedController.php`, `RankedAdminController.php`
- ❌ Opt-in/opt-out
- ❌ Glicko-2 calculations
- ❌ Placement games
- ❌ Tier assignments
- ❌ Rating decay
- ❌ Leaderboard display

**Critical:** HIGH (complex math, easy to break)

### Reputation System
**Files:** `ReputationController.php`, `ReputationAdminController.php`
- ❌ +Rep/-Rep voting
- ❌ Tier calculation
- ❌ Leaderboard

**Critical:** Low

### Content Creators
**Files:** `ContentCreatorController.php`, `ContentCreatorAdminController.php`
- ❌ Creator registration
- ❌ Platform linking
- ❌ Directory display

**Critical:** Low

### Highlight Clips
**Files:** `HighlightClipController.php`, `HighlightClipAdminController.php`
- ❌ Clip submission
- ❌ Voting system
- ❌ Clip of the week

**Critical:** Low

### News System
**Files:** `NewsController.php`, `NewsAdminController.php`
- ❌ Article creation
- ❌ Comments
- ❌ Hoorah reactions

**Critical:** Low

### Favorites
**Files:** `FavoriteController.php`
- ❌ Add/remove favorites
- ❌ Polymorphic favoriting (players/teams/servers)

**Critical:** Low

### Discord Integration
**Files:** `DiscordPresenceController.php`, `DiscordAdminController.php`
- ❌ Rich presence updates
- ❌ Activity tracking

**Critical:** Low

### Referee System
**Files:** `RefereeController.php`
- ❌ Match reporting
- ❌ Score submission
- ❌ Dispute handling

**Critical:** Medium

### Admin Panels
**Files:** `*AdminController.php` (many)
- ❌ Metrics dashboard
- ❌ Audit logs
- ❌ Player reports
- ❌ Anticheat dashboard
- ❌ Weapon/Vehicle image management

**Critical:** Medium (admin-only, but important)

### Export Features
**Files:** `StatsExportController.php`
- ❌ CSV export
- ❌ JSON export
- ❌ Match history export

**Critical:** Low

### Player Search & Comparison
**Files:** `PlayerSearchController.php`, `PlayerComparisonController.php`
- ❌ Player autocomplete
- ❌ Head-to-head comparison
- ❌ Multi-player comparison (up to 4)

**Critical:** Low

### Leaderboards
**Files:** `LeaderboardController.php`
- ❌ Sorting
- ❌ Time periods
- ❌ Export

**Critical:** Low (basic functionality works, edge cases untested)

### Kill Feed & Heatmap
**Files:** `KillFeedController.php`, `KillController.php`
- ❌ WebSocket updates
- ❌ Live player positions
- ❌ Heatmap rendering

**Critical:** Low

### Server Widgets
**Files:** `ServerWidgetController.php`
- ❌ Embed code generation
- ❌ Theme customization
- ❌ Public API endpoint

**Critical:** Low

## Coverage by Layer

### Controllers: ~30%
- 61 total controllers
- ~15 controllers have tests
- 46 controllers untested

### Models: ~40%
- Most relationships tested indirectly via controllers
- Business logic (accessors, scopes) mostly untested

### Commands: ~20%
- `server:track` - ❌ Untested
- `ratings:calculate` - ❌ Untested
- `ratings:decay` - ❌ Untested
- `achievements:check` - ❌ Untested
- `metrics:collect` - ❌ Untested
- `leaderboards:warm-cache` - ❌ Untested
- `mods:sync` - ❌ Untested

### Services: ~10%
- `BattleMetricsService` - ❌ Untested
- `A2SQueryService` - ❌ Untested
- `ReforgerWorkshopService` - ❌ Untested
- `GameServerManager` - ❌ Untested
- `TournamentBracketService` - ✅ Well tested
- `Glicko2Service` - ❌ Untested (CRITICAL!)
- `RatingCalculationService` - ❌ Untested (CRITICAL!)
- `MetricsTracker` - ❌ Untested

### Events/Listeners: 5%
- Most broadcasting events untested
- Notification listeners untested

## Priority Test Additions

**HIGH Priority (write these first):**

1. **Ranked System** (`RankedController`)
   - Glicko-2 math is complex and error-prone
   - Affects competitive integrity
   - ~20 tests needed

2. **Server Manager** (`ServerManagerController`)
   - Controls production servers
   - Can cause downtime if broken
   - ~15 tests needed

3. **Achievement System** (`AchievementController`)
   - Active feature with user engagement
   - Progress tracking can break silently
   - ~10 tests needed

4. **Scrim System** (`ScrimController`)
   - Used frequently by teams
   - Invitation flow can break
   - ~10 tests needed

**MEDIUM Priority:**

5. **Player Comparison** (`PlayerComparisonController`)
   - Popular feature
   - ~5 tests needed

6. **Referee System** (`RefereeController`)
   - Important for tournament integrity
   - ~8 tests needed

7. **Leaderboard Edge Cases** (`LeaderboardController`)
   - Sorting, pagination, filters
   - ~5 tests needed

**LOW Priority:**

8. **News/Comments** (`NewsController`)
9. **Reputation** (`ReputationController`)
10. **Favorites** (`FavoriteController`)

## How to Add Tests

### Example: Testing RankedController

```php
<?php

namespace Tests\Feature\Ranked;

use App\Models\User;
use App\Models\PlayerRating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankedSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_opt_in_to_ranked(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid']);

        $response = $this->actingAs($user)->post('/ranked/opt-in');

        $response->assertRedirect();
        $this->assertDatabaseHas('player_ratings', [
            'user_id' => $user->id,
        ]);
    }

    public function test_placement_games_track_progress(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid']);
        PlayerRating::create([
            'user_id' => $user->id,
            'rating' => 1500,
            'rating_deviation' => 350,
            'placement_games' => 5,
            'is_placed' => false,
        ]);

        $response = $this->actingAs($user)->get('/ranked');

        $response->assertSee('5/10 placement games');
    }

    public function test_rating_calculation_updates_tier(): void
    {
        // Create rated kill in queue
        // Run ratings:calculate command
        // Assert rating updated
        // Assert tier assigned
    }
}
```

### Running Specific Test Suites

```bash
# Run only API tests
php artisan test --testsuite=Feature --filter=Api

# Run only Team tests
php artisan test --filter=Team

# Run with coverage report
php artisan test --coverage --min=70
```

## GitHub Actions CI

Tests run automatically on:
- Every push to `main` or `develop`
- Every pull request

**Configuration:** `.github/workflows/tests.yml`

## Manual Testing Still Required

Even with 100% test coverage, manual testing is needed for:
- UI/UX issues
- Browser compatibility
- WebSocket connections
- Performance under load
- Visual regressions
- Accessibility

See `TESTING.md` for manual test checklist.

---

**Goal:** Reach 70% overall coverage by end of Q1 2026
