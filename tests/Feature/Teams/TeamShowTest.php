<?php

namespace Tests\Feature\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_show_page_loads_without_errors(): void
    {
        $captain = User::factory()->create();
        $team = Team::factory()->create([
            'captain_id' => $captain->id,
        ]);

        $team->members()->attach($captain->id, [
            'role' => 'captain',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Add a regular member
        $member = User::factory()->create();
        $team->members()->attach($member->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->get("/teams/{$team->id}");

        $response->assertOk();
        $response->assertSee($team->name);
        $response->assertSee($captain->name);
        $response->assertSee($member->name);
    }

    public function test_team_show_page_eager_loads_active_members(): void
    {
        $captain = User::factory()->create();
        $team = Team::factory()->create([
            'captain_id' => $captain->id,
        ]);

        $team->members()->attach($captain->id, [
            'role' => 'captain',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // This test would have caught the ->with('user') bug
        // because accessing activeMembers would throw RelationNotFoundException
        $response = $this->get("/teams/{$team->id}");

        $response->assertOk();
    }

    // TODO: Fix this test - needs Server factory or better stats setup
    // public function test_team_show_displays_game_stats(): void { ... }

    public function test_team_my_page_loads_for_team_member(): void
    {
        $captain = User::factory()->create();
        $team = Team::factory()->create([
            'captain_id' => $captain->id,
        ]);

        $team->members()->attach($captain->id, [
            'role' => 'captain',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($captain)->get('/teams/my');

        $response->assertOk();
        $response->assertSee($team->name);
    }

    public function test_team_my_page_shows_no_team_for_non_members(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/teams/my');

        $response->assertOk();
        $response->assertSee("You don't have a platoon", false);
    }
}
