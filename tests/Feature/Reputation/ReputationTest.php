<?php

namespace Tests\Feature\Reputation;

use App\Models\PlayerReputation;
use App\Models\ReputationVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReputationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reputation_leaderboard_page_loads(): void
    {
        $response = $this->get('/reputation');

        $response->assertOk();
    }

    public function test_user_can_give_positive_rep(): void
    {
        $voter = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($voter)->post('/reputation/vote', [
            'target_user_id' => $target->id,
            'vote_type' => 'positive',
            'category' => 'teamwork',
            'comment' => 'Great teammate!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reputation_votes', [
            'voter_id' => $voter->id,
            'target_id' => $target->id,
            'vote_type' => 'positive',
        ]);
    }

    public function test_user_can_give_negative_rep(): void
    {
        $voter = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($voter)->post('/reputation/vote', [
            'target_user_id' => $target->id,
            'vote_type' => 'negative',
            'category' => 'sportsmanship',
            'comment' => 'Poor sportsmanship',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reputation_votes', [
            'voter_id' => $voter->id,
            'target_id' => $target->id,
            'vote_type' => 'negative',
        ]);
    }

    public function test_user_cannot_vote_for_themselves(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/reputation/vote', [
            'target_user_id' => $user->id,
            'vote_type' => 'positive',
            'category' => 'teamwork',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_user_cannot_vote_same_target_twice_in_24h(): void
    {
        $voter = User::factory()->create();
        $target = User::factory()->create();

        // First vote
        ReputationVote::create([
            'voter_id' => $voter->id,
            'target_id' => $target->id,
            'vote_type' => 'positive',
            'category' => 'teamwork',
            'created_at' => now(),
        ]);

        // Second vote within 24h
        $response = $this->actingAs($voter)->post('/reputation/vote', [
            'target_user_id' => $target->id,
            'vote_type' => 'positive',
            'category' => 'leadership',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_user_can_change_vote_within_24h(): void
    {
        $voter = User::factory()->create();
        $target = User::factory()->create();

        $vote = ReputationVote::create([
            'voter_id' => $voter->id,
            'target_id' => $target->id,
            'vote_type' => 'positive',
            'category' => 'teamwork',
            'created_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($voter)->put("/reputation/votes/{$vote->id}", [
            'vote_type' => 'negative',
            'category' => 'teamwork',
            'comment' => 'Changed my mind',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reputation_votes', [
            'id' => $vote->id,
            'vote_type' => 'negative',
        ]);
    }

    public function test_user_cannot_change_vote_after_24h(): void
    {
        $voter = User::factory()->create();
        $target = User::factory()->create();

        $vote = ReputationVote::create([
            'voter_id' => $voter->id,
            'target_id' => $target->id,
            'vote_type' => 'positive',
            'category' => 'teamwork',
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($voter)->put("/reputation/votes/{$vote->id}", [
            'vote_type' => 'negative',
        ]);

        $response->assertStatus(403);
    }

    public function test_reputation_score_calculates_correctly(): void
    {
        $target = User::factory()->create();

        // Create 5 positive votes
        for ($i = 0; $i < 5; $i++) {
            $voter = User::factory()->create();
            ReputationVote::create([
                'voter_id' => $voter->id,
                'target_id' => $target->id,
                'vote_type' => 'positive',
                'category' => 'teamwork',
            ]);
        }

        // Create 2 negative votes
        for ($i = 0; $i < 2; $i++) {
            $voter = User::factory()->create();
            ReputationVote::create([
                'voter_id' => $voter->id,
                'target_id' => $target->id,
                'vote_type' => 'negative',
                'category' => 'sportsmanship',
            ]);
        }

        PlayerReputation::updateOrCreate(
            ['user_id' => $target->id],
            [
                'total_score' => 3, // 5 - 2
                'positive_votes' => 5,
                'negative_votes' => 2,
            ]
        );

        $this->assertDatabaseHas('player_reputations', [
            'user_id' => $target->id,
            'total_score' => 3,
        ]);
    }

    public function test_reputation_tier_assigned_correctly(): void
    {
        $user = User::factory()->create();

        PlayerReputation::create([
            'user_id' => $user->id,
            'total_score' => 120,
            'tier' => 'trusted', // 100+
            'positive_votes' => 120,
            'negative_votes' => 0,
        ]);

        $response = $this->get('/reputation');

        $response->assertSee('Trusted');
    }

    public function test_guest_can_view_reputation_but_not_vote(): void
    {
        $response = $this->get('/reputation');

        $response->assertOk();
        $response->assertDontSee('Vote');
    }
}
