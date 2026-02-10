<?php

namespace Tests\Feature\Teams;

use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $captain;

    protected User $member;

    protected User $applicant;

    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->captain = User::factory()->create();
        $this->member = User::factory()->create();
        $this->applicant = User::factory()->create();

        $this->team = Team::factory()->create([
            'captain_id' => $this->captain->id,
        ]);

        // Captain must be an active member for isUserCaptainOrOfficer checks
        $this->team->members()->attach($this->captain->id, [
            'role' => 'captain',
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    // === Team Creation ===

    public function test_user_can_create_team(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/teams', [
            'name' => 'Elite Warriors',
            'tag' => 'EW',
            'description' => 'A competitive team',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('teams', [
            'name' => 'Elite Warriors',
            'tag' => 'EW',
            'captain_id' => $user->id,
            'is_active' => true,
        ]);

        // Captain should be automatically added as member
        $team = Team::where('name', 'Elite Warriors')->first();
        $this->assertTrue($team->members()->where('user_id', $user->id)->exists());
    }

    public function test_team_name_must_be_unique(): void
    {
        Team::factory()->create(['name' => 'Existing Team']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/teams', [
            'name' => 'Existing Team',
            'tag' => 'ET',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_team_tag_must_be_unique(): void
    {
        Team::factory()->create(['tag' => 'TAKEN']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/teams', [
            'name' => 'New Team',
            'tag' => 'TAKEN',
        ]);

        $response->assertSessionHasErrors('tag');
    }

    // === Team Invitations ===

    public function test_captain_can_invite_user(): void
    {
        $invitee = User::factory()->create(['steam_id' => '76561198000000001']);

        $response = $this->actingAs($this->captain)
            ->post("/teams/{$this->team->id}/invite", [
                'steam_id' => '76561198000000001',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $this->team->id,
            'user_id' => $invitee->id,
            'status' => 'pending',
        ]);
    }

    public function test_non_captain_cannot_invite_user(): void
    {
        $nonCaptain = User::factory()->create();
        User::factory()->create(['steam_id' => '76561198000000002']);

        $response = $this->actingAs($nonCaptain)
            ->post("/teams/{$this->team->id}/invite", [
                'steam_id' => '76561198000000002',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_accept_invitation(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->member->id,
            'invited_by' => $this->captain->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->member)
            ->post("/invitations/{$invitation->id}/accept");

        $response->assertRedirect();

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'status' => 'accepted',
        ]);

        // User should be added to team
        $this->assertTrue(
            $this->team->members()->where('user_id', $this->member->id)->exists()
        );
    }

    public function test_user_can_decline_invitation(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->member->id,
            'invited_by' => $this->captain->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->member)
            ->post("/invitations/{$invitation->id}/decline");

        $response->assertRedirect();

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'status' => 'declined',
        ]);

        // User should NOT be added to team
        $this->assertFalse(
            $this->team->members()->where('user_id', $this->member->id)->exists()
        );
    }

    public function test_invitation_expires_after_7_days(): void
    {
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->member->id,
            'invited_by' => $this->captain->id,
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->member)
            ->post("/invitations/{$invitation->id}/accept");

        // isValid() returns false for expired invitations → abort(403)
        $response->assertStatus(403);
    }

    public function test_cannot_invite_user_already_on_team(): void
    {
        $existingMember = User::factory()->create(['steam_id' => '76561198000000003']);
        $this->team->members()->attach($existingMember->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->captain)
            ->post("/teams/{$this->team->id}/invite", [
                'steam_id' => '76561198000000003',
            ]);

        // Controller returns back()->with('error', ...) for already-member
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // === Team Applications ===

    public function test_user_can_apply_to_recruiting_team(): void
    {
        $this->team->update(['is_recruiting' => true]);

        $response = $this->actingAs($this->applicant)
            ->post("/teams/{$this->team->id}/apply", [
                'message' => 'I would like to join your team!',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('team_applications', [
            'team_id' => $this->team->id,
            'user_id' => $this->applicant->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_apply_to_non_recruiting_team(): void
    {
        $this->team->update(['is_recruiting' => false]);

        $response = $this->actingAs($this->applicant)
            ->post("/teams/{$this->team->id}/apply", [
                'message' => 'I would like to join!',
            ]);

        // Controller returns back()->with('error', ...) not abort(403)
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_captain_can_approve_application(): void
    {
        $application = TeamApplication::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->applicant->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->captain)
            ->post("/teams/{$this->team->id}/applications/{$application->id}/accept");

        $response->assertRedirect();

        $this->assertDatabaseHas('team_applications', [
            'id' => $application->id,
            'status' => 'accepted',
        ]);

        // User should be added to team
        $this->assertTrue(
            $this->team->members()->where('user_id', $this->applicant->id)->exists()
        );
    }

    public function test_captain_can_reject_application(): void
    {
        $application = TeamApplication::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->applicant->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->captain)
            ->post("/teams/{$this->team->id}/applications/{$application->id}/reject");

        $response->assertRedirect();

        $this->assertDatabaseHas('team_applications', [
            'id' => $application->id,
            'status' => 'rejected',
        ]);

        // User should NOT be added to team
        $this->assertFalse(
            $this->team->members()->where('user_id', $this->applicant->id)->exists()
        );
    }

    public function test_non_captain_cannot_approve_application(): void
    {
        $application = TeamApplication::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->applicant->id,
            'status' => 'pending',
        ]);

        $nonCaptain = User::factory()->create();

        $response = $this->actingAs($nonCaptain)
            ->post("/teams/{$this->team->id}/applications/{$application->id}/accept");

        $response->assertStatus(403);
    }

    public function test_user_cannot_apply_twice_to_same_team(): void
    {
        $this->team->update(['is_recruiting' => true]);

        // First application
        TeamApplication::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->applicant->id,
            'status' => 'pending',
        ]);

        // Second application - returns back()->with('error', ...) due to hasPendingApplicationTo
        $response = $this->actingAs($this->applicant)
            ->post("/teams/{$this->team->id}/apply", [
                'message' => 'Applying again!',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // === Team Membership ===

    public function test_captain_can_remove_member(): void
    {
        $this->team->members()->attach($this->member->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->captain)
            ->post("/teams/{$this->team->id}/members/{$this->member->id}/kick");

        $response->assertRedirect();

        // Member should have status=kicked (not removed entirely)
        $this->assertDatabaseHas('team_members', [
            'team_id' => $this->team->id,
            'user_id' => $this->member->id,
            'status' => 'kicked',
        ]);
    }

    public function test_captain_cannot_kick_themselves(): void
    {
        $response = $this->actingAs($this->captain)
            ->post("/teams/{$this->team->id}/members/{$this->captain->id}/kick");

        // Controller returns back()->with('error', 'You cannot kick yourself.')
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_member_can_leave_team(): void
    {
        $this->team->members()->attach($this->member->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->member)
            ->post("/teams/{$this->team->id}/leave");

        $response->assertRedirect();

        // Member should have status=left
        $this->assertDatabaseHas('team_members', [
            'team_id' => $this->team->id,
            'user_id' => $this->member->id,
            'status' => 'left',
        ]);
    }

    public function test_captain_cannot_leave_team_with_members(): void
    {
        $this->team->members()->attach($this->member->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->captain)
            ->post("/teams/{$this->team->id}/leave");

        // Controller returns back()->with('error', 'Captains must transfer leadership...')
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // === Team Update ===

    public function test_captain_can_update_team(): void
    {
        $response = $this->actingAs($this->captain)
            ->put("/teams/{$this->team->id}", [
                'name' => $this->team->name,
                'tag' => $this->team->tag,
                'description' => 'Updated description',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('teams', [
            'id' => $this->team->id,
            'description' => 'Updated description',
        ]);
    }

    public function test_non_captain_cannot_update_team(): void
    {
        $nonCaptain = User::factory()->create();

        $response = $this->actingAs($nonCaptain)
            ->put("/teams/{$this->team->id}", [
                'description' => 'Hacked!',
            ]);

        $response->assertStatus(403);
    }

    // === Team Disband ===

    public function test_captain_can_disband_team(): void
    {
        $response = $this->actingAs($this->captain)
            ->post("/teams/{$this->team->id}/disband");

        $response->assertRedirect();

        $this->team->refresh();
        $this->assertFalse($this->team->is_active);
    }

    public function test_non_captain_cannot_disband_team(): void
    {
        $nonCaptain = User::factory()->create();

        $response = $this->actingAs($nonCaptain)
            ->post("/teams/{$this->team->id}/disband");

        $response->assertStatus(403);
    }

    // === Edge Cases ===

    public function test_user_already_on_team_cannot_apply(): void
    {
        // User is on team 1
        $team1 = Team::factory()->create();
        $team1->members()->attach($this->member->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Try to apply to team 2
        $team2 = Team::factory()->create(['is_recruiting' => true]);

        $response = $this->actingAs($this->member)
            ->post("/teams/{$team2->id}/apply", [
                'message' => 'I want to join!',
            ]);

        // Controller returns back()->with('error', 'You must leave your current platoon first.')
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_user_already_on_team_cannot_accept_invitation(): void
    {
        // Member is already on another team
        $otherTeam = Team::factory()->create();
        $otherTeam->members()->attach($this->member->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $invitation = TeamInvitation::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->member->id,
            'invited_by' => $this->captain->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->member)
            ->post("/invitations/{$invitation->id}/accept");

        // hasTeam() returns true, so controller returns back()->with('error', ...)
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
