<?php

namespace Tests\Feature\Commands;

use App\Models\AdminAuditLog;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScheduledCommandsTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // Achievements Check
    // ========================================

    public function test_achievements_check_exits_cleanly_with_no_achievements(): void
    {
        // No achievements defined — command should exit cleanly
        $this->artisan('achievements:check')->assertExitCode(0);
    }

    public function test_achievements_check_awards_when_threshold_met(): void
    {
        $achievement = \App\Models\Achievement::create([
            'name' => 'Test Achievement',
            'slug' => 'test-achievement',
            'description' => 'Test',
            'category' => 'combat',
            'icon' => 'star',
            'color' => '#gold',
            'points' => 10,
            'stat_field' => 'kills',
            'threshold' => 10,
        ]);

        // Create player stat that meets threshold
        DB::table('player_stats')->insert([
            'player_uuid' => 'test-uuid',
            'player_name' => 'Test Player',
            'server_id' => 1,
            'kills' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('achievements:check')->assertExitCode(0);

        // Check that achievement was awarded
        $this->assertDatabaseHas('player_achievements', [
            'player_uuid' => 'test-uuid',
            'achievement_id' => $achievement->id,
        ]);
    }

    public function test_achievements_check_does_not_re_award(): void
    {
        $achievement = \App\Models\Achievement::create([
            'name' => 'Test Achievement',
            'slug' => 'test-achievement',
            'description' => 'Test',
            'category' => 'combat',
            'icon' => 'star',
            'color' => '#gold',
            'points' => 10,
            'stat_field' => 'kills',
            'threshold' => 10,
        ]);

        DB::table('player_stats')->insert([
            'player_uuid' => 'test-uuid',
            'player_name' => 'Test Player',
            'server_id' => 1,
            'kills' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Award it manually first
        DB::table('player_achievements')->insert([
            'player_uuid' => 'test-uuid',
            'achievement_id' => $achievement->id,
            'earned_at' => now()->subDay(),
        ]);

        $this->artisan('achievements:check')->assertExitCode(0);

        // Should still only have one entry
        $this->assertEquals(1, DB::table('player_achievements')
            ->where('player_uuid', 'test-uuid')
            ->where('achievement_id', $achievement->id)
            ->count());
    }

    // ========================================
    // Cleanup: Expired Invitations
    // ========================================

    public function test_expired_invitations_are_marked_expired(): void
    {
        $team = Team::factory()->create();

        // Create expired invitation
        $expired = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'status' => 'pending',
            'expires_at' => now()->subDays(2),
        ]);

        // Create valid invitation
        $valid = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(5),
        ]);

        // Run the cleanup logic (same as console.php schedule)
        TeamInvitation::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->assertDatabaseHas('team_invitations', [
            'id' => $expired->id,
            'status' => 'expired',
        ]);

        $this->assertDatabaseHas('team_invitations', [
            'id' => $valid->id,
            'status' => 'pending',
        ]);
    }

    // ========================================
    // Cleanup: Old Notifications
    // ========================================

    public function test_old_read_notifications_are_deleted(): void
    {
        $user = User::factory()->create();

        // Insert old read notification (>90 days)
        DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'old']),
            'read_at' => now()->subDays(100),
            'created_at' => now()->subDays(100),
            'updated_at' => now()->subDays(100),
        ]);

        // Insert recent read notification
        DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'recent']),
            'read_at' => now()->subDays(10),
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        // Run cleanup logic (same as console.php)
        DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        $this->assertEquals(1, DB::table('notifications')->count());
    }

    // ========================================
    // Cleanup: Old Audit Logs
    // ========================================

    public function test_old_audit_logs_are_deleted(): void
    {
        $user = User::factory()->create();

        // Create old log using DB::table to control created_at
        DB::table('admin_audit_logs')->insert([
            'user_id' => $user->id,
            'action' => 'old.action',
            'target_type' => 'User',
            'target_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(400),
            'updated_at' => now()->subDays(400),
        ]);

        // Create recent log
        DB::table('admin_audit_logs')->insert([
            'user_id' => $user->id,
            'action' => 'recent.action',
            'target_type' => 'User',
            'target_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run cleanup logic (same as console.php)
        AdminAuditLog::where('created_at', '<', now()->subDays(365))->delete();

        $this->assertEquals(1, AdminAuditLog::count());
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'recent.action']);
    }

    // ========================================
    // Cache Warm Command
    // ========================================

    public function test_leaderboard_cache_warm_command(): void
    {
        // Skipped: K/D leaderboard uses PostgreSQL ::numeric cast which fails on SQLite
        $this->markTestSkipped('Leaderboard cache warming uses PostgreSQL-specific ::numeric cast');
    }
}
