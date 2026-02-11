<?php

namespace Tests\Feature\ContentCreators;

use App\Models\ClipVote;
use App\Models\ContentCreator;
use App\Models\HighlightClip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorsTest extends TestCase
{
    use RefreshDatabase;

    // === Content Creators ===

    public function test_creators_directory_page_loads(): void
    {
        $response = $this->get('/creators');

        $response->assertOk();
    }

    public function test_creator_profile_displays_correctly(): void
    {
        $user = User::factory()->create();
        $creator = ContentCreator::create([
            'user_id' => $user->id,
            'name' => 'TestStreamer',
            'bio' => 'I stream Reforger',
            'platform_twitch' => 'teststreamer',
            'is_verified' => true,
            'is_featured' => false,
        ]);

        $response = $this->get("/creators/{$creator->id}");

        $response->assertOk();
        $response->assertSee('TestStreamer');
        $response->assertSee('I stream Reforger');
    }

    public function test_user_can_register_as_creator(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/creators/register', [
            'name' => 'NewStreamer',
            'bio' => 'New streamer here',
            'platform_twitch' => 'newstreamer',
            'platform_youtube' => 'newstreamer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('content_creators', [
            'user_id' => $user->id,
            'name' => 'NewStreamer',
        ]);
    }

    public function test_creator_can_update_profile(): void
    {
        $user = User::factory()->create();
        $creator = ContentCreator::create([
            'user_id' => $user->id,
            'name' => 'OldName',
            'bio' => 'Old bio',
            'platform_twitch' => 'oldname',
        ]);

        $response = $this->actingAs($user)->put("/creators/{$creator->id}", [
            'name' => 'NewName',
            'bio' => 'New bio',
            'platform_twitch' => 'newname',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('content_creators', [
            'id' => $creator->id,
            'name' => 'NewName',
        ]);
    }

    public function test_non_creator_cannot_update_others_profile(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $creator = ContentCreator::create([
            'user_id' => $user1->id,
            'name' => 'Creator1',
            'bio' => 'Bio',
        ]);

        $response = $this->actingAs($user2)->put("/creators/{$creator->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_creators_filter_by_platform(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ContentCreator::create([
            'user_id' => $user1->id,
            'name' => 'Twitch Creator',
            'platform_twitch' => 'twitchcreator',
        ]);

        ContentCreator::create([
            'user_id' => $user2->id,
            'name' => 'YouTube Creator',
            'platform_youtube' => 'youtubecreator',
        ]);

        $response = $this->get('/creators?platform=twitch');

        $response->assertOk();
        $response->assertSee('Twitch Creator');
        $response->assertDontSee('YouTube Creator');
    }

    // === Highlight Clips ===

    public function test_clips_gallery_page_loads(): void
    {
        $response = $this->get('/clips');

        $response->assertOk();
    }

    public function test_user_can_submit_clip(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clips', [
            'title' => 'Epic Kill',
            'url' => 'https://youtube.com/watch?v=test123',
            'description' => 'Amazing headshot',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('highlight_clips', [
            'user_id' => $user->id,
            'title' => 'Epic Kill',
        ]);
    }

    public function test_guest_cannot_submit_clip(): void
    {
        $response = $this->post('/clips', [
            'title' => 'Clip',
            'url' => 'https://youtube.com/watch?v=test',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_user_can_vote_on_clip(): void
    {
        $voter = User::factory()->create();
        $submitter = User::factory()->create();
        $clip = HighlightClip::create([
            'user_id' => $submitter->id,
            'title' => 'Great Clip',
            'url' => 'https://youtube.com/watch?v=test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($voter)->post("/clips/{$clip->id}/vote", [
            'vote_type' => 'upvote',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clip_votes', [
            'clip_id' => $clip->id,
            'user_id' => $voter->id,
            'vote_type' => 'upvote',
        ]);
    }

    public function test_user_can_change_vote(): void
    {
        $voter = User::factory()->create();
        $submitter = User::factory()->create();
        $clip = HighlightClip::create([
            'user_id' => $submitter->id,
            'title' => 'Clip',
            'url' => 'https://youtube.com/watch?v=test',
            'status' => 'approved',
        ]);

        ClipVote::create([
            'clip_id' => $clip->id,
            'user_id' => $voter->id,
            'vote_type' => 'upvote',
        ]);

        $response = $this->actingAs($voter)->post("/clips/{$clip->id}/vote", [
            'vote_type' => 'downvote',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clip_votes', [
            'clip_id' => $clip->id,
            'user_id' => $voter->id,
            'vote_type' => 'downvote',
        ]);
    }

    public function test_user_cannot_vote_on_pending_clip(): void
    {
        $voter = User::factory()->create();
        $submitter = User::factory()->create();
        $clip = HighlightClip::create([
            'user_id' => $submitter->id,
            'title' => 'Pending Clip',
            'url' => 'https://youtube.com/watch?v=test',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($voter)->post("/clips/{$clip->id}/vote", [
            'vote_type' => 'upvote',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_clip(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submitter = User::factory()->create();
        $clip = HighlightClip::create([
            'user_id' => $submitter->id,
            'title' => 'Pending Clip',
            'url' => 'https://youtube.com/watch?v=test',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->post("/admin/clips/{$clip->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('highlight_clips', [
            'id' => $clip->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_clip(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submitter = User::factory()->create();
        $clip = HighlightClip::create([
            'user_id' => $submitter->id,
            'title' => 'Inappropriate Clip',
            'url' => 'https://youtube.com/watch?v=test',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->post("/admin/clips/{$clip->id}/reject");

        $response->assertRedirect();
        $this->assertDatabaseHas('highlight_clips', [
            'id' => $clip->id,
            'status' => 'rejected',
        ]);
    }

    public function test_clip_of_the_week_displays(): void
    {
        $user = User::factory()->create();
        $clip = HighlightClip::create([
            'user_id' => $user->id,
            'title' => 'Best Clip Ever',
            'url' => 'https://youtube.com/watch?v=test',
            'status' => 'approved',
            'is_featured' => true,
        ]);

        $response = $this->get('/clips');

        $response->assertOk();
        $response->assertSee('Clip of the Week');
        $response->assertSee('Best Clip Ever');
    }
}
