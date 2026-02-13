<?php

namespace Tests\Feature\Scrims;

use App\Models\ScrimInvitation;
use App\Models\ScrimMatch;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrimTest extends TestCase
{
    use RefreshDatabase;

    protected User $captain1;

    protected User $captain2;

    protected Team $team1;

    protected Team $team2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->captain1 = User::factory()->create();
        $this->captain2 = User::factory()->create();

        $this->team1 = Team::factory()->create(['captain_id' => $this->captain1->id]);
        $this->team1->members()->attach($this->captain1->id, [
            'role' => 'captain',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->team2 = Team::factory()->create(['captain_id' => $this->captain2->id]);
        $this->team2->members()->attach($this->captain2->id, [
            'role' => 'captain',
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function test_scrims_page_loads(): void
    {
        $response = $this->actingAs($this->captain1)->get(route('scrims.index'));

        $response->assertOk();
    }

    public function test_captain_can_invite_team_to_scrim(): void
    {
        $response = $this->actingAs($this->captain1)->post(route('scrims.invite'), [
            'invited_team_id' => $this->team2->id,
            'proposed_time' => now()->addDay()->toDateTimeString(),
            'message' => 'Want to scrim?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scrim_invitations', [
            'inviting_team_id' => $this->team1->id,
            'invited_team_id' => $this->team2->id,
            'status' => 'pending',
        ]);
    }

    public function test_non_captain_cannot_invite_to_scrim(): void
    {
        $member = User::factory()->create();
        $this->team1->members()->attach($member->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($member)->post(route('scrims.invite'), [
            'invited_team_id' => $this->team2->id,
            'proposed_time' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_captain_can_accept_scrim_invitation(): void
    {
        $invitation = ScrimInvitation::create([
            'inviting_team_id' => $this->team1->id,
            'invited_team_id' => $this->team2->id,
            'proposed_time' => now()->addDay(),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->captain2)
            ->post(route('scrims.invitations.accept', $invitation));

        $response->assertRedirect();
        $this->assertDatabaseHas('scrim_invitations', [
            'id' => $invitation->id,
            'status' => 'accepted',
        ]);

        // Scrim match should be created
        $this->assertDatabaseHas('scrim_matches', [
            'team1_id' => $this->team1->id,
            'team2_id' => $this->team2->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_captain_can_decline_scrim_invitation(): void
    {
        $invitation = ScrimInvitation::create([
            'inviting_team_id' => $this->team1->id,
            'invited_team_id' => $this->team2->id,
            'proposed_time' => now()->addDay(),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->captain2)
            ->post(route('scrims.invitations.decline', $invitation));

        $response->assertRedirect();
        $this->assertDatabaseHas('scrim_invitations', [
            'id' => $invitation->id,
            'status' => 'declined',
        ]);
    }

    public function test_scrim_invitation_expires_after_7_days(): void
    {
        $invitation = ScrimInvitation::create([
            'inviting_team_id' => $this->team1->id,
            'invited_team_id' => $this->team2->id,
            'proposed_time' => now()->addDay(),
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->captain2)
            ->post(route('scrims.invitations.accept', $invitation));

        $response->assertStatus(403);
    }

    public function test_scrim_can_be_cancelled(): void
    {
        $scrim = ScrimMatch::create([
            'team1_id' => $this->team1->id,
            'team2_id' => $this->team2->id,
            'created_by' => $this->captain1->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->captain1)
            ->post(route('scrims.cancel', $scrim));

        $response->assertRedirect();
        $this->assertDatabaseHas('scrim_matches', [
            'id' => $scrim->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_scrim_result_can_be_submitted(): void
    {
        $scrim = ScrimMatch::create([
            'team1_id' => $this->team1->id,
            'team2_id' => $this->team2->id,
            'created_by' => $this->captain1->id,
            'scheduled_at' => now()->subHour(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->captain1)
            ->post(route('scrims.report', $scrim), [
                'team1_score' => 10,
                'team2_score' => 5,
                'winner_id' => $this->team1->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scrim_matches', [
            'id' => $scrim->id,
            'winner_id' => $this->team1->id,
            'status' => 'completed',
        ]);
    }

    public function test_both_teams_must_agree_on_result(): void
    {
        $scrim = ScrimMatch::create([
            'team1_id' => $this->team1->id,
            'team2_id' => $this->team2->id,
            'created_by' => $this->captain1->id,
            'scheduled_at' => now()->subHour(),
            'status' => 'in_progress',
        ]);

        // Team 1 reports
        $this->actingAs($this->captain1)
            ->post(route('scrims.report', $scrim), [
                'team1_score' => 10,
                'team2_score' => 5,
                'winner_id' => $this->team1->id,
            ]);

        // Team 2 disagrees
        $response = $this->actingAs($this->captain2)
            ->post(route('scrims.report', $scrim), [
                'team1_score' => 5,
                'team2_score' => 10,
                'winner_id' => $this->team2->id,
            ]);

        $scrim->refresh();
        // Match should still be in dispute or in_progress
        $this->assertNotEquals('completed', $scrim->status);
    }
}
