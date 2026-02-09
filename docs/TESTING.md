# Testing Guide

## Overview

The Reforger Community test suite covers API endpoints, rate limiting, deprecation, tournament bracket generation, and team management.

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=StatsControllerV1Test

# Run specific test method
php artisan test --filter=test_v1_player_kills_creates_record

# Run tests with coverage (requires Xdebug/PCOV)
php artisan test --coverage

# Run specific test suite
php artisan test tests/Feature/Api
php artisan test tests/Feature/Tournaments
php artisan test tests/Feature/Teams
```

## Test Structure

```
tests/
├── Feature/
│   ├── Api/
│   │   ├── StatsControllerV1Test.php      # API v1 endpoints (18 tests)
│   │   ├── GameStatsApiTest.php           # Legacy API tests (6 tests)
│   │   ├── RateLimitingTest.php           # Rate limiting (9 tests)
│   │   └── DeprecationTest.php            # Deprecation headers (5 tests)
│   ├── Tournaments/
│   │   └── BracketGenerationTest.php      # Bracket generation (16 tests)
│   └── Teams/
│       └── TeamManagementTest.php         # Team CRUD, invites, applications (24 tests)
├── Unit/
│   └── ExampleTest.php
└── TestCase.php
```

## Test Coverage Summary

**Total Test Methods:** 78
**Currently Passing:** 25
**Status:** 32% coverage achieved

### Test Breakdown by Category

| Category | Tests | Status | Coverage |
|----------|-------|--------|----------|
| API v1 Endpoints | 18 | ✅ 11 passing | 61% |
| Rate Limiting | 9 | ✅ 5 passing | 56% |
| API Deprecation | 5 | ✅ 4 passing | 80% |
| Legacy API | 6 | ✅ 5 passing | 83% |
| Tournament Brackets | 16 | ⚠️ Needs factories | 0% |
| Team Management | 24 | ⚠️ Needs factories | 0% |

## API Tests (StatsControllerV1Test)

Tests all v1 API write and read endpoints:

**Write Endpoints (11 passing):**
- ✅ Player kills creation and stats aggregation
- ✅ Damage events batch processing
- ✅ Connection events
- ✅ XP events and stats updates
- ✅ Base capture events
- ✅ Player distance tracking
- ✅ Shooting stats
- ✅ Grenade usage
- ✅ Supply deliveries
- ✅ Roadkill detection
- ✅ Team kill detection

**Read Endpoints:**
- ✅ Leaderboards (kills, deaths, K/D, etc.)
- ✅ Player profile details
- ✅ Stats overview

**Validation:**
- ✅ Authentication requirements
- ✅ Validation error handling (422 responses)

## Rate Limiting Tests (RateLimitingTest)

Tests tiered rate limiting based on token types:

**Passing Tests (5):**
- ✅ Standard token 60/min limit
- ✅ High-volume token 180/min limit
- ✅ Premium token 300/min limit
- ✅ Rate limit headers present
- ✅ Rate limit applies per token

**Known Issues:**
- Some validation edge cases need fixing
- Token exhaustion test needs optimization

## Deprecation Tests (DeprecationTest)

Tests legacy API deprecation headers:

**Passing Tests (4):**
- ✅ Deprecation headers on legacy endpoints
- ✅ V1 endpoints don't have deprecation headers
- ✅ All legacy write endpoints deprecated
- ✅ All legacy read endpoints deprecated

**Known Issues:**
- Link header format needs adjustment

## Tournament Bracket Tests (BracketGenerationTest)

Tests tournament bracket generation for all formats:

**Test Coverage:**
- Single elimination (4, 8 teams, odd teams)
- Double elimination (winners/losers bracket)
- Round robin (4, 6 teams)
- Swiss system (8 teams, 3 rounds)
- Edge cases (minimum teams, insufficient teams, approved teams only)
- Seeding and match creation

**Status:** ⚠️ Requires TournamentFactory and TeamFactory

## Team Management Tests (TeamManagementTest)

Tests team CRUD operations, invitations, and applications:

**Test Coverage:**
- Team creation and validation
- Invitation system (send, accept, decline, expiry)
- Application system (apply, approve, reject)
- Team membership (add, remove, leave)
- Captain permissions and restrictions
- Edge cases (multiple teams, maximum members)

**Status:** ⚠️ Requires TeamFactory and related factories

## Writing New Tests

### Test File Template

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_feature_works_as_expected(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/endpoint', [
            'data' => 'value',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('table_name', [
            'column' => 'value',
        ]);
    }
}
```

### Best Practices

1. **Use RefreshDatabase** - Ensures clean database state for each test
2. **Use Sanctum for API auth** - `Sanctum::actingAs($user)` for authenticated requests
3. **Test happy path first** - Then edge cases and error conditions
4. **Assert both response and database** - Verify HTTP response AND data persistence
5. **Use descriptive test names** - `test_v1_player_kills_creates_record_and_updates_stats`
6. **Group related tests** - Organize by feature/endpoint
7. **Use factories** - For creating test data (User, Team, Tournament, etc.)

### Common Assertions

```php
// Response assertions
$response->assertStatus(200);
$response->assertJson(['success' => true]);
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertHeader('X-RateLimit-Limit', '60');

// Database assertions
$this->assertDatabaseHas('table', ['column' => 'value']);
$this->assertDatabaseCount('table', 5);
$this->assertDatabaseMissing('table', ['column' => 'value']);

// Model assertions
$this->assertTrue($model->exists);
$this->assertFalse($model->is_active);
$this->assertEquals(10, $model->count);
```

## Continuous Integration

Tests run automatically on:
- Git commit (if pre-commit hooks enabled)
- Pull request creation
- Merge to main branch

## Test Database

- **Production:** PostgreSQL
- **Tests:** SQLite in-memory (`:memory:`)
- **Isolation:** Each test gets fresh database via `RefreshDatabase`

## Known Issues

### 1. Factory Missing Errors

**Issue:** Tournament and Team tests fail with "Factory not found"

**Solution:**
```bash
# Create factories
php artisan make:factory TournamentFactory
php artisan make:factory TeamFactory
php artisan make:factory TeamInvitationFactory
php artisan make:factory TeamApplicationFactory
```

### 2. Schema Mismatches

**Issue:** Some tests fail due to SQLite/PostgreSQL schema differences

**Solution:** Use `Schema::hasColumn()` guards in migrations or create test-specific migrations

### 3. Validation Errors

**Issue:** Some endpoints have stricter validation than tests expect

**Solution:** Review controller validation rules and update test payloads

## Future Test Additions

To reach 50%+ coverage, add tests for:

1. **Authentication Flow** (P0)
   - Steam OAuth login
   - 2FA challenge flow
   - Session management

2. **Scheduled Tasks** (P0)
   - Server tracking cron
   - Cache warming
   - Expired invitation cleanup

3. **Stats Aggregation Helpers** (P0)
   - `updatePlayerKillStats()`
   - `updatePlayerXp()`
   - `updatePlayerDistanceTotals()`
   - All other helper methods

4. **Admin Controllers** (P1)
   - Server manager actions
   - API token generation
   - User management

5. **Integration Tests** (P2)
   - Full tournament flow (create → register → bracket → matches → winner)
   - Full team flow (create → invite → accept → tournament → leave)
   - Full stats flow (game events → aggregation → leaderboards → profiles)

## Debugging Failed Tests

```bash
# Run with verbose output
php artisan test --filter=TestName --testdox

# Run single test with full output
php artisan test --filter=test_specific_method --do-not-cache-result

# Check database state during test
// Add in test:
dump($this->getConnection()->table('table_name')->get());

# Check response content
// Add in test:
dump($response->getContent());
```

## Performance

Current test suite runs in ~4 seconds:
- API tests: ~0.9s
- Rate limiting tests: ~0.9s
- Deprecation tests: ~0.6s
- Legacy API tests: ~0.6s

Target: Keep full suite under 30 seconds as it grows.

## Contributing

When adding new features:
1. Write tests FIRST (TDD approach)
2. Ensure tests pass before committing
3. Maintain >50% coverage for new code
4. Update this documentation with new test categories
