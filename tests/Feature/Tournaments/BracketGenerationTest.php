<?php

namespace Tests\Feature\Tournaments;

use App\Models\Tournament;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\TournamentBracketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BracketGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected TournamentBracketService $bracketService;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bracketService = app(TournamentBracketService::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    protected function createTournamentWithTeams(string $format, int $teamCount, array $extra = []): Tournament
    {
        $tournament = Tournament::factory()->create(array_merge([
            'format' => $format,
            'max_teams' => max($teamCount, $extra['max_teams'] ?? $teamCount),
        ], $extra));

        $teams = Team::factory()->count($teamCount)->create();
        foreach ($teams as $index => $team) {
            $tournament->registrations()->create([
                'team_id' => $team->id,
                'status' => 'approved',
                'seed' => $index + 1,
            ]);
        }

        return $tournament;
    }

    // === Single Elimination ===

    public function test_single_elimination_bracket_with_4_teams(): void
    {
        $tournament = $this->createTournamentWithTeams('single_elimination', 4);

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();

        // Should have 2 semi-final matches + 1 final = 3 matches
        $this->assertCount(3, $matches);

        // Round 1 (Semi-finals)
        $round1Matches = $matches->where('round', 1);
        $this->assertCount(2, $round1Matches);

        // Round 2 (Final)
        $round2Matches = $matches->where('round', 2);
        $this->assertCount(1, $round2Matches);

        // All 4 teams should be assigned to round 1
        $assignedTeams = $round1Matches->flatMap(fn ($m) => [$m->team1_id, $m->team2_id])->filter();
        $this->assertCount(4, $assignedTeams);
    }

    public function test_single_elimination_bracket_with_8_teams(): void
    {
        $tournament = $this->createTournamentWithTeams('single_elimination', 8);

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();

        // 4 quarter-finals + 2 semi-finals + 1 final = 7 matches
        $this->assertCount(7, $matches);

        // Rounds
        $this->assertCount(4, $matches->where('round', 1)); // Quarters
        $this->assertCount(2, $matches->where('round', 2)); // Semis
        $this->assertCount(1, $matches->where('round', 3)); // Final
    }

    public function test_single_elimination_bracket_with_odd_teams(): void
    {
        $tournament = $this->createTournamentWithTeams('single_elimination', 5);

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();

        // With 5 teams, bracket size = 8, so 4 + 2 + 1 = 7 matches
        $this->assertGreaterThan(0, $matches->count());

        // Some first-round matches should have byes (null opponent)
        $round1 = $matches->where('round', 1);
        $byeMatches = $round1->filter(
            fn ($m) => $m->team1_id === null || $m->team2_id === null
        );
        $this->assertGreaterThan(0, $byeMatches->count());
    }

    // === Double Elimination ===

    public function test_double_elimination_bracket_with_4_teams(): void
    {
        $tournament = $this->createTournamentWithTeams('double_elimination', 4);

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();

        // Double elimination has winners bracket + losers bracket + grand final
        // Should have more matches than single elimination (3)
        $this->assertGreaterThan(3, $matches->count());

        // Should have both bracket types
        $mainMatches = $matches->where('bracket', 'main');
        $losersMatches = $matches->where('bracket', 'losers');

        $this->assertGreaterThan(0, $mainMatches->count());
        $this->assertGreaterThan(0, $losersMatches->count());
    }

    // === Round Robin ===

    public function test_round_robin_bracket_with_4_teams(): void
    {
        $tournament = $this->createTournamentWithTeams('round_robin', 4);

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();

        // Round robin: each team plays every other team once
        // With 4 teams: (4 * 3) / 2 = 6 matches
        $this->assertCount(6, $matches);

        // Each team should appear exactly 3 times (plays against 3 other teams)
        $teamAppearances = [];
        foreach ($matches as $match) {
            $teamAppearances[$match->team1_id] = ($teamAppearances[$match->team1_id] ?? 0) + 1;
            $teamAppearances[$match->team2_id] = ($teamAppearances[$match->team2_id] ?? 0) + 1;
        }

        foreach ($teamAppearances as $count) {
            $this->assertEquals(3, $count);
        }
    }

    public function test_round_robin_bracket_with_6_teams(): void
    {
        $tournament = $this->createTournamentWithTeams('round_robin', 6);

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();

        // With 6 teams: (6 * 5) / 2 = 15 matches
        $this->assertCount(15, $matches);
    }

    // === Swiss ===

    public function test_swiss_bracket_generates_first_round(): void
    {
        $tournament = $this->createTournamentWithTeams('swiss', 8, ['swiss_rounds' => 3]);

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();

        // Swiss generates only round 1 initially (8 teams / 2 = 4 matches)
        $this->assertCount(4, $matches);

        // All matches should be round 1
        $this->assertTrue($matches->every(fn ($m) => $m->round === 1));

        // Each match should have two teams assigned
        foreach ($matches as $match) {
            $this->assertNotNull($match->team1_id);
            $this->assertNotNull($match->team2_id);
        }
    }

    // === Edge Cases ===

    public function test_bracket_with_minimum_teams(): void
    {
        $tournament = $this->createTournamentWithTeams('single_elimination', 2);

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();

        // With 2 teams, should have just 1 match (the final)
        $this->assertCount(1, $matches);
        $this->assertNotNull($matches->first()->team1_id);
        $this->assertNotNull($matches->first()->team2_id);
    }

    public function test_bracket_with_insufficient_teams_creates_no_matches(): void
    {
        $tournament = Tournament::factory()->create([
            'format' => 'single_elimination',
            'max_teams' => 8,
        ]);

        // Only 1 team registered
        $team = Team::factory()->create();
        $tournament->registrations()->create([
            'team_id' => $team->id,
            'status' => 'approved',
        ]);

        $this->bracketService->generateBracket($tournament);

        // With less than 2 teams, no matches should be created
        $this->assertCount(0, $tournament->matches()->get());
    }

    public function test_bracket_only_includes_approved_teams(): void
    {
        $tournament = Tournament::factory()->create([
            'format' => 'single_elimination',
            'max_teams' => 8,
        ]);

        $teams = Team::factory()->count(6)->create();

        // Approve 4, leave 2 pending
        foreach ($teams->take(4) as $index => $team) {
            $tournament->registrations()->create([
                'team_id' => $team->id,
                'status' => 'approved',
                'seed' => $index + 1,
            ]);
        }

        foreach ($teams->skip(4) as $team) {
            $tournament->registrations()->create([
                'team_id' => $team->id,
                'status' => 'pending',
            ]);
        }

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();

        // Should only use the 4 approved teams
        $assignedTeams = $matches
            ->flatMap(fn ($m) => [$m->team1_id, $m->team2_id])
            ->filter()
            ->unique();

        $this->assertCount(4, $assignedTeams);

        // Pending teams should not be in bracket
        $pendingTeamIds = $teams->skip(4)->pluck('id');
        foreach ($pendingTeamIds as $teamId) {
            $this->assertNotContains($teamId, $assignedTeams);
        }
    }

    public function test_bracket_seeding_assigns_teams_to_round_1(): void
    {
        $tournament = $this->createTournamentWithTeams('single_elimination', 4);

        $this->bracketService->generateBracket($tournament);

        $matches = $tournament->matches()->get();
        $round1 = $matches->where('round', 1);

        // Should have 2 matches in round 1 with teams assigned
        $this->assertCount(2, $round1);

        foreach ($round1 as $match) {
            $this->assertNotNull($match->team1_id);
            $this->assertNotNull($match->team2_id);
        }
    }

    public function test_bracket_matches_are_created_in_database(): void
    {
        $tournament = $this->createTournamentWithTeams('single_elimination', 4);

        $this->bracketService->generateBracket($tournament);

        // Verify matches were created
        $this->assertDatabaseCount('tournament_matches', 3); // 2 semis + 1 final

        // Verify match structure
        $matches = $tournament->matches;
        foreach ($matches as $match) {
            $this->assertNotNull($match->round);
            $this->assertNotNull($match->match_number);
            $this->assertEquals('pending', $match->status);
        }
    }

    // === Swiss Next Round ===

    public function test_swiss_next_round_generates_after_completing_current(): void
    {
        $tournament = $this->createTournamentWithTeams('swiss', 4, ['swiss_rounds' => 3]);

        $this->bracketService->generateBracket($tournament);

        // Complete all round 1 matches
        $round1Matches = $tournament->matches()->where('round', 1)->get();
        foreach ($round1Matches as $match) {
            $match->update([
                'winner_id' => $match->team1_id,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        // Generate next round
        $result = $this->bracketService->generateNextSwissRound($tournament);
        $this->assertTrue($result);

        // Should now have round 2 matches
        $round2Matches = $tournament->matches()->where('round', 2)->get();
        $this->assertGreaterThan(0, $round2Matches->count());
    }
}
