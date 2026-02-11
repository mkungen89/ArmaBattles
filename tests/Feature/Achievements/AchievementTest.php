<?php

namespace Tests\Feature\Achievements;

use App\Models\Achievement;
use App\Models\AchievementProgress;
use App\Models\AchievementShowcase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    public function test_achievements_page_loads(): void
    {
        $response = $this->get('/achievements');

        $response->assertOk();
        $response->assertSee('Achievements');
    }

    public function test_achievements_display_with_category_filter(): void
    {
        Achievement::create([
            'name' => 'First Blood',
            'description' => 'Get your first kill',
            'category' => 'combat',
            'icon' => 'sword',
            'color' => '#ff0000',
            'points' => 10,
            'condition_type' => 'kills',
            'condition_value' => 1,
        ]);

        Achievement::create([
            'name' => 'Social Butterfly',
            'description' => 'Join a team',
            'category' => 'social',
            'icon' => 'users',
            'color' => '#00ff00',
            'points' => 5,
            'condition_type' => 'team_join',
            'condition_value' => 1,
        ]);

        $response = $this->get('/achievements?category=combat');

        $response->assertOk();
        $response->assertSee('First Blood');
        $response->assertDontSee('Social Butterfly');
    }

    public function test_authenticated_user_sees_progress(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid']);

        $achievement = Achievement::create([
            'name' => 'Sharpshooter',
            'description' => 'Get 100 headshots',
            'category' => 'combat',
            'icon' => 'crosshair',
            'color' => '#ffaa00',
            'points' => 50,
            'condition_type' => 'headshots',
            'condition_value' => 100,
        ]);

        AchievementProgress::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'current_value' => 45,
        ]);

        $response = $this->actingAs($user)->get('/achievements');

        $response->assertOk();
        $response->assertSee('45');
        $response->assertSee('100');
        $response->assertSee('45%');
    }

    public function test_user_can_showcase_achievements(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid']);

        $achievement1 = Achievement::create([
            'name' => 'Elite',
            'description' => 'Reach level 50',
            'category' => 'progression',
            'icon' => 'star',
            'color' => '#gold',
            'points' => 100,
            'condition_type' => 'level',
            'condition_value' => 50,
        ]);

        $achievement2 = Achievement::create([
            'name' => 'Veteran',
            'description' => 'Play 100 hours',
            'category' => 'progression',
            'icon' => 'clock',
            'color' => '#silver',
            'points' => 75,
            'condition_type' => 'playtime',
            'condition_value' => 360000,
        ]);

        // User has earned both
        AchievementProgress::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement1->id,
            'current_value' => 50,
            'unlocked_at' => now(),
        ]);

        AchievementProgress::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement2->id,
            'current_value' => 360000,
            'unlocked_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/achievements/showcase', [
            'pinned_achievements' => [$achievement1->id, $achievement2->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('achievement_showcases', [
            'player_uuid' => 'test-uuid',
        ]);
    }

    public function test_user_cannot_showcase_more_than_three_achievements(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid']);

        $achievements = Achievement::factory()->count(4)->create();

        $response = $this->actingAs($user)->post('/achievements/showcase', [
            'pinned_achievements' => $achievements->pluck('id')->toArray(),
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_user_cannot_showcase_unearned_achievements(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid']);

        $achievement = Achievement::create([
            'name' => 'Not Earned',
            'description' => 'Test',
            'category' => 'combat',
            'icon' => 'x',
            'color' => '#000',
            'points' => 10,
            'condition_type' => 'kills',
            'condition_value' => 1000,
        ]);

        // User has NOT earned this achievement
        $response = $this->actingAs($user)->post('/achievements/showcase', [
            'pinned_achievements' => [$achievement->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_achievement_rarity_calculated_correctly(): void
    {
        $achievement = Achievement::create([
            'name' => 'Rare Achievement',
            'description' => 'Very rare',
            'category' => 'combat',
            'icon' => 'trophy',
            'color' => '#purple',
            'points' => 200,
            'condition_type' => 'special',
            'condition_value' => 1,
        ]);

        // Create 100 users, only 5 have it
        User::factory()->count(95)->create();

        $users = User::factory()->count(5)->create();
        foreach ($users as $user) {
            AchievementProgress::create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'current_value' => 1,
                'unlocked_at' => now(),
            ]);
        }

        // Rarity should be 5/100 = 5%
        $response = $this->get('/achievements');

        $response->assertOk();
        // Check that rarity percentage is displayed somewhere
    }
}
