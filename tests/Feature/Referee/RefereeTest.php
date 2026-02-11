<?php

namespace Tests\Feature\Referee;

use App\Models\MatchReport;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefereeTest extends TestCase
{
    use RefreshDatabase;

    protected User $referee;

    protected User $admin;

    protected Tournament $tournament;

    protected TournamentMatch $match;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referee = User::factory()->create(['role' => 'referee']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->tournament = Tournament::factory()->create([
            'name' => 'Test Tournament',
            'status' => 'in_progress',
        ]);

        $captain1 = User::factory()->create();
        $captain2 = User::factory()->create();
        $team1 = Team::factory()->create(['captain_id' => $captain1->id]);
        $team2 = Team::factory()->create(['captain_id' => $captain2->id]);

        $this->match = TournamentMatch::create([
            'tournament_id' => $this->tournament->id,
            'team1_id' => $team1->id,
            'team2_id' => $team2->id,
            'round' => 1,
            'status' => 'in_progress',
        ]);
    }

    public function test_referee_dashboard_loads(): void
    {
        $response = $this->actingAs($this->referee)->get('/referee');

        $response->assertOk();
    }

    public function test_non_referee_cannot_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/referee');

        $response->assertStatus(403);
    }

    public function test_referee_can_view_assigned_matches(): void
    {
        $response = $this->actingAs($this->referee)->get('/referee/matches');

        $response->assertOk();
    }

    public function test_referee_can_submit_match_report(): void
    {
        $response = $this->actingAs($this->referee)
            ->post("/referee/matches/{$this->match->id}/report", [
                'team1_score' => 10,
                'team2_score' => 5,
                'winner_id' => $this->match->team1_id,
                'notes' => 'Clean match, no issues',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('match_reports', [
            'match_id' => $this->match->id,
            'referee_id' => $this->referee->id,
            'winner_id' => $this->match->team1_id,
        ]);
    }

    public function test_match_report_updates_match_status(): void
    {
        $this->actingAs($this->referee)
            ->post("/referee/matches/{$this->match->id}/report", [
                'team1_score' => 10,
                'team2_score' => 5,
                'winner_id' => $this->match->team1_id,
                'notes' => 'Match completed',
            ]);

        $this->match->refresh();
        $this->assertEquals('completed', $this->match->status);
        $this->assertEquals($this->match->team1_id, $this->match->winner_id);
    }

    public function test_referee_can_report_dispute(): void
    {
        $response = $this->actingAs($this->referee)
            ->post("/referee/matches/{$this->match->id}/report", [
                'team1_score' => 10,
                'team2_score' => 10,
                'winner_id' => null,
                'has_dispute' => true,
                'dispute_reason' => 'Teams disagree on score',
                'notes' => 'Needs admin review',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('match_reports', [
            'match_id' => $this->match->id,
            'has_dispute' => true,
        ]);
    }

    public function test_referee_cannot_edit_finalized_report(): void
    {
        $report = MatchReport::create([
            'match_id' => $this->match->id,
            'referee_id' => $this->referee->id,
            'team1_score' => 10,
            'team2_score' => 5,
            'winner_id' => $this->match->team1_id,
            'is_finalized' => true,
        ]);

        $response = $this->actingAs($this->referee)
            ->put("/referee/reports/{$report->id}", [
                'notes' => 'Changed my mind',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_override_referee_report(): void
    {
        $report = MatchReport::create([
            'match_id' => $this->match->id,
            'referee_id' => $this->referee->id,
            'team1_score' => 10,
            'team2_score' => 5,
            'winner_id' => $this->match->team1_id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/reports/{$report->id}/override", [
                'winner_id' => $this->match->team2_id,
                'override_reason' => 'Video evidence shows team2 won',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('match_reports', [
            'id' => $report->id,
            'winner_id' => $this->match->team2_id,
        ]);
    }

    public function test_match_check_in_system(): void
    {
        $captain1 = Team::find($this->match->team1_id)->captain;

        $response = $this->actingAs($captain1)
            ->post("/matches/{$this->match->id}/check-in");

        $response->assertRedirect();
        $this->assertDatabaseHas('match_check_ins', [
            'match_id' => $this->match->id,
            'team_id' => $this->match->team1_id,
        ]);
    }

    public function test_both_teams_must_check_in(): void
    {
        $captain1 = Team::find($this->match->team1_id)->captain;
        $captain2 = Team::find($this->match->team2_id)->captain;

        // Team 1 checks in
        $this->actingAs($captain1)->post("/matches/{$this->match->id}/check-in");

        $this->match->refresh();
        $this->assertNotEquals('ready', $this->match->status);

        // Team 2 checks in
        $this->actingAs($captain2)->post("/matches/{$this->match->id}/check-in");

        $this->match->refresh();
        $this->assertEquals('ready', $this->match->status);
    }

    public function test_forfeit_if_team_no_show(): void
    {
        $captain1 = Team::find($this->match->team1_id)->captain;

        // Only team 1 checks in, team 2 is no-show
        $this->actingAs($captain1)->post("/matches/{$this->match->id}/check-in");

        // Simulate check-in deadline passing (15 minutes)
        $this->match->update(['scheduled_time' => now()->subMinutes(20)]);

        $response = $this->actingAs($this->referee)
            ->post("/referee/matches/{$this->match->id}/forfeit", [
                'forfeiting_team_id' => $this->match->team2_id,
                'reason' => 'No show',
            ]);

        $response->assertRedirect();
        $this->match->refresh();
        $this->assertEquals('completed', $this->match->status);
        $this->assertEquals($this->match->team1_id, $this->match->winner_id);
    }
}
