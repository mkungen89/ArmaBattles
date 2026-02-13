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

    protected function setUp(): void
    {
        parent::setUp();
    }

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
            'platform' => 'twitch',
            'channel_url' => 'https://twitch.tv/teststreamer',
            'channel_name' => 'TestStreamer',
            'bio' => 'I stream Reforger',
            'is_verified' => true,
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
            'platform' => 'twitch',
            'channel_url' => 'https://twitch.tv/newstreamer',
            'channel_name' => 'NewStreamer',
            'bio' => 'New streamer here',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('content_creators', [
            'user_id' => $user->id,
            'channel_name' => 'NewStreamer',
        ]);
    }

    public function test_creator_can_update_profile(): void
    {
        $user = User::factory()->create();
        $creator = ContentCreator::create([
            'user_id' => $user->id,
            'platform' => 'twitch',
            'channel_url' => 'https://twitch.tv/oldname',
            'channel_name' => 'OldName',
            'bio' => 'Old bio',
        ]);

        $response = $this->actingAs($user)->put("/creators/{$creator->id}", [
            'channel_name' => 'NewName',
            'bio' => 'New bio',
            'channel_url' => 'https://twitch.tv/newname',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('content_creators', [
            'id' => $creator->id,
            'channel_name' => 'NewName',
        ]);
    }

    public function test_non_creator_cannot_update_others_profile(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $creator = ContentCreator::create([
            'user_id' => $user1->id,
            'platform' => 'twitch',
            'channel_url' => 'https://twitch.tv/creator1',
            'channel_name' => 'Creator1',
            'bio' => 'Bio',
        ]);

        $response = $this->actingAs($user2)->put("/creators/{$creator->id}", [
            'channel_name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_creators_filter_by_platform(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ContentCreator::create([
            'user_id' => $user1->id,
            'platform' => 'twitch',
            'channel_url' => 'https://twitch.tv/twitchcreator',
            'channel_name' => 'Twitch Creator',
        ]);

        ContentCreator::create([
            'user_id' => $user2->id,
            'platform' => 'youtube',
            'channel_url' => 'https://youtube.com/youtubecreator',
            'channel_name' => 'YouTube Creator',
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
            'platform' => 'youtube',
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
            'platform' => 'youtube',
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
            'platform' => 'youtube',
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
            'platform' => 'youtube',
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
            'platform' => 'youtube',
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
            'platform' => 'youtube',
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
            'platform' => 'youtube',
            'status' => 'approved',
            'is_featured' => true,
        ]);

        $response = $this->get('/clips');

        $response->assertOk();
        $response->assertSee('Video of the Week');
        $response->assertSee('Best Clip Ever');
    }

    public function test_cannot_register_duplicate_channel_url(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ContentCreator::create([
            'user_id' => $user1->id,
            'platform' => 'twitch',
            'channel_url' => 'https://twitch.tv/samechannel',
            'channel_name' => 'SameChannel',
        ]);

        $response = $this->actingAs($user2)->post('/creators/register', [
            'platform' => 'twitch',
            'channel_url' => 'https://twitch.tv/samechannel',
            'channel_name' => 'DifferentName',
        ]);

        $response->assertSessionHasErrors('channel_url');
        $this->assertEquals(1, ContentCreator::where('channel_url', 'https://twitch.tv/samechannel')->count());
    }

    public function test_clip_auto_approves_when_threshold_reached(): void
    {
        // Set auto-approval threshold to 3 votes
        \App\Models\SiteSetting::updateOrCreate(
            ['key' => 'clip_approval_threshold'],
            [
                'value' => '3',
                'group' => 'Content Creators',
                'type' => 'integer',
                'label' => 'Test Setting',
                'description' => 'Test',
                'sort_order' => 1
            ]
        );

        $submitter = User::factory()->create();
        $clip = HighlightClip::create([
            'user_id' => $submitter->id,
            'title' => 'Pending Clip',
            'url' => 'https://youtube.com/watch?v=test',
            'platform' => 'youtube',
            'status' => 'pending',
        ]);

        // Add 3 upvotes
        $voters = User::factory()->count(3)->create();
        foreach ($voters as $voter) {
            ClipVote::create([
                'user_id' => $voter->id,
                'clip_id' => $clip->id,
                'vote_type' => 'upvote',
            ]);
        }

        // Recalculate votes (triggers auto-approval check)
        $clip->recalculateVotes();

        // Clip should now be approved
        $this->assertEquals('approved', $clip->fresh()->status);
    }

    public function test_duration_validation_rejects_long_videos(): void
    {
        // Set max duration to 60 seconds
        \App\Models\SiteSetting::updateOrCreate(
            ['key' => 'clip_max_duration_seconds'],
            [
                'value' => '60',
                'group' => 'Content Creators',
                'type' => 'integer',
                'label' => 'Test Setting',
                'description' => 'Test',
                'sort_order' => 1
            ]
        );

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clips', [
            'title' => 'Too Long Video',
            'url' => 'https://youtube.com/watch?v=test',
            'platform' => 'youtube',
            'duration_seconds' => 120, // Exceeds 60 second limit
        ]);

        $response->assertSessionHasErrors('duration_seconds');
    }

    public function test_duration_validation_accepts_valid_videos(): void
    {
        // Set max duration to 120 seconds
        \App\Models\SiteSetting::updateOrCreate(
            ['key' => 'clip_max_duration_seconds'],
            [
                'value' => '120',
                'group' => 'Content Creators',
                'type' => 'integer',
                'label' => 'Test Setting',
                'description' => 'Test',
                'sort_order' => 1
            ]
        );

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clips', [
            'title' => 'Valid Duration Video',
            'url' => 'https://youtube.com/watch?v=test',
            'platform' => 'youtube',
            'duration_seconds' => 90, // Within 120 second limit
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('highlight_clips', [
            'user_id' => $user->id,
            'title' => 'Valid Duration Video',
            'duration_seconds' => 90,
        ]);
    }
}
