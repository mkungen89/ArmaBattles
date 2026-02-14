<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General (5)
            ['key' => 'site_name', 'value' => 'Reforger Community', 'group' => 'General', 'type' => 'string', 'label' => 'Site Name', 'description' => 'The name displayed in the browser title and header', 'sort_order' => 1],
            ['key' => 'site_description', 'value' => 'Arma Reforger Community Hub', 'group' => 'General', 'type' => 'text', 'label' => 'Site Description', 'description' => 'A short description of your community', 'sort_order' => 2],
            ['key' => 'custom_footer_text', 'value' => null, 'group' => 'General', 'type' => 'text', 'label' => 'Custom Footer Text', 'description' => 'Additional text displayed in the footer', 'sort_order' => 3],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'General', 'type' => 'boolean', 'label' => 'Maintenance Mode', 'description' => 'When enabled, only admins can access the site', 'sort_order' => 4],
            ['key' => 'maintenance_message', 'value' => 'We are performing scheduled maintenance. Please check back soon.', 'group' => 'General', 'type' => 'text', 'label' => 'Maintenance Message', 'description' => 'Message displayed to visitors during maintenance', 'sort_order' => 5],

            // SEO (6)
            ['key' => 'meta_description', 'value' => 'Arma Reforger community hub with server tracking, player statistics, tournaments, and more.', 'group' => 'SEO', 'type' => 'text', 'label' => 'Meta Description', 'description' => 'Default meta description for search engines', 'sort_order' => 1],
            ['key' => 'meta_keywords', 'value' => 'arma reforger, gaming, community, server tracker, tournaments', 'group' => 'SEO', 'type' => 'string', 'label' => 'Meta Keywords', 'description' => 'Comma-separated keywords for SEO', 'sort_order' => 2],
            ['key' => 'og_image_url', 'value' => null, 'group' => 'SEO', 'type' => 'string', 'label' => 'OG Image URL', 'description' => 'Image URL for social media sharing (Open Graph)', 'sort_order' => 3],
            ['key' => 'analytics_code', 'value' => null, 'group' => 'SEO', 'type' => 'text', 'label' => 'Analytics Code', 'description' => 'Google Analytics or other tracking code (placed in head)', 'sort_order' => 4],
            ['key' => 'robots_meta', 'value' => 'index, follow', 'group' => 'SEO', 'type' => 'string', 'label' => 'Robots Meta', 'description' => 'Controls search engine indexing behavior', 'options' => json_encode(['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow']), 'sort_order' => 5],
            ['key' => 'custom_head_html', 'value' => null, 'group' => 'SEO', 'type' => 'text', 'label' => 'Custom Head HTML', 'description' => 'Custom HTML injected before </head> (use with care)', 'sort_order' => 6],

            // Security (6)
            ['key' => 'allow_registration', 'value' => '1', 'group' => 'Security', 'type' => 'boolean', 'label' => 'Allow Registration', 'description' => 'Allow new users to register with email/password', 'sort_order' => 1],
            ['key' => 'allow_steam_login', 'value' => '1', 'group' => 'Security', 'type' => 'boolean', 'label' => 'Allow Steam Login', 'description' => 'Allow users to log in via Steam', 'sort_order' => 2],
            ['key' => 'allow_google_login', 'value' => '1', 'group' => 'Security', 'type' => 'boolean', 'label' => 'Allow Google Login', 'description' => 'Allow users to log in via Google', 'sort_order' => 3],
            ['key' => 'force_2fa_staff', 'value' => '0', 'group' => 'Security', 'type' => 'boolean', 'label' => 'Force 2FA for Staff', 'description' => 'Require two-factor authentication for admins and moderators', 'sort_order' => 4],
            ['key' => 'login_rate_limit', 'value' => '5', 'group' => 'Security', 'type' => 'integer', 'label' => 'Login Rate Limit', 'description' => 'Maximum login attempts per minute', 'sort_order' => 5],
            ['key' => 'session_lifetime', 'value' => '120', 'group' => 'Security', 'type' => 'integer', 'label' => 'Session Lifetime (minutes)', 'description' => 'How long user sessions last in minutes', 'sort_order' => 6],

            // Server Tracking (6)
            ['key' => 'bm_cache_ttl_live', 'value' => '60', 'group' => 'Server Tracking', 'type' => 'integer', 'label' => 'BM Live Cache TTL (seconds)', 'description' => 'Cache duration for live server/player data from BattleMetrics', 'sort_order' => 1],
            ['key' => 'bm_cache_ttl_history', 'value' => '300', 'group' => 'Server Tracking', 'type' => 'integer', 'label' => 'BM History Cache TTL (seconds)', 'description' => 'Cache duration for history/uptime data from BattleMetrics', 'sort_order' => 2],
            ['key' => 'a2s_query_timeout', 'value' => '3', 'group' => 'Server Tracking', 'type' => 'integer', 'label' => 'A2S Query Timeout (seconds)', 'description' => 'Timeout for direct server UDP queries', 'sort_order' => 3],
            ['key' => 'a2s_query_retries', 'value' => '2', 'group' => 'Server Tracking', 'type' => 'integer', 'label' => 'A2S Query Retries', 'description' => 'Number of retry attempts for failed UDP queries', 'sort_order' => 4],
            ['key' => 'default_server_id', 'value' => null, 'group' => 'Server Tracking', 'type' => 'string', 'label' => 'Default Server ID', 'description' => 'BattleMetrics server ID used as default (overrides .env)', 'sort_order' => 5],
            ['key' => 'server_tracking_interval', 'value' => '5', 'group' => 'Server Tracking', 'type' => 'integer', 'label' => 'Tracking Interval (minutes)', 'description' => 'How often the server tracker runs', 'sort_order' => 6],

            // Leaderboard (3)
            ['key' => 'leaderboard_per_page', 'value' => '50', 'group' => 'Leaderboard', 'type' => 'integer', 'label' => 'Results Per Page', 'description' => 'Number of players shown per page on the leaderboard', 'sort_order' => 1],
            ['key' => 'leaderboard_min_playtime', 'value' => '0', 'group' => 'Leaderboard', 'type' => 'integer', 'label' => 'Minimum Playtime (seconds)', 'description' => 'Minimum playtime required to appear on leaderboard (0 = no minimum)', 'sort_order' => 2],
            ['key' => 'leaderboard_categories', 'value' => json_encode(['kills', 'deaths', 'headshots', 'playtime_seconds', 'total_distance', 'bases_captured', 'heals_given', 'supplies_delivered', 'xp_total']), 'group' => 'Leaderboard', 'type' => 'json', 'label' => 'Visible Categories', 'description' => 'Which leaderboard sorting categories are shown', 'options' => json_encode(['kills', 'deaths', 'headshots', 'playtime_seconds', 'total_distance', 'bases_captured', 'heals_given', 'supplies_delivered', 'xp_total']), 'sort_order' => 3],

            // Tournaments (4)
            ['key' => 'tournament_default_swiss_rounds', 'value' => '5', 'group' => 'Tournaments', 'type' => 'integer', 'label' => 'Default Swiss Rounds', 'description' => 'Default number of rounds for Swiss-format tournaments', 'sort_order' => 1],
            ['key' => 'tournament_default_min_teams', 'value' => '4', 'group' => 'Tournaments', 'type' => 'integer', 'label' => 'Default Min Teams', 'description' => 'Default minimum number of teams for a tournament', 'sort_order' => 2],
            ['key' => 'tournament_default_max_teams', 'value' => '32', 'group' => 'Tournaments', 'type' => 'integer', 'label' => 'Default Max Teams', 'description' => 'Default maximum number of teams for a tournament', 'sort_order' => 3],
            ['key' => 'tournament_registration_reminder_hours', 'value' => '24', 'group' => 'Tournaments', 'type' => 'integer', 'label' => 'Registration Reminder (hours)', 'description' => 'Hours before deadline to send registration reminder', 'sort_order' => 4],

            // Moderation (8)
            ['key' => 'blocked_chat_words', 'value' => null, 'group' => 'Moderation', 'type' => 'text', 'label' => 'Blocked Chat Words', 'description' => 'Comma-separated list of blocked words for chat filtering', 'sort_order' => 1],
            ['key' => 'auto_ban_threshold', 'value' => '0', 'group' => 'Moderation', 'type' => 'integer', 'label' => 'Auto-Ban Threshold', 'description' => 'Number of anti-cheat flags before automatic ban (0 = disabled)', 'sort_order' => 2],
            ['key' => 'reputation_vote_cooldown_hours', 'value' => '24', 'group' => 'Moderation', 'type' => 'integer', 'label' => 'Reputation Vote Cooldown (hours)', 'description' => 'Hours before a user can change their reputation vote', 'sort_order' => 3],
            ['key' => 'reputation_tier_trusted', 'value' => '100', 'group' => 'Moderation', 'type' => 'integer', 'label' => 'Reputation: Trusted Tier', 'description' => 'Minimum score for Trusted tier', 'sort_order' => 4],
            ['key' => 'reputation_tier_good', 'value' => '50', 'group' => 'Moderation', 'type' => 'integer', 'label' => 'Reputation: Good Tier', 'description' => 'Minimum score for Good tier', 'sort_order' => 5],
            ['key' => 'reputation_tier_poor', 'value' => '-50', 'group' => 'Moderation', 'type' => 'integer', 'label' => 'Reputation: Poor Tier', 'description' => 'Minimum score for Poor tier (below this is Flagged)', 'sort_order' => 6],
            ['key' => 'reputation_max_votes_per_day', 'value' => '10', 'group' => 'Moderation', 'type' => 'integer', 'label' => 'Max Reputation Votes/Day', 'description' => 'Maximum reputation votes a user can cast per day (0 = unlimited)', 'sort_order' => 7],
            ['key' => 'player_report_auto_flag_count', 'value' => '3', 'group' => 'Moderation', 'type' => 'integer', 'label' => 'Auto-Flag Report Count', 'description' => 'Number of reports before player is auto-flagged for review (0 = disabled)', 'sort_order' => 8],

            // Notifications (3)
            ['key' => 'discord_webhook_url', 'value' => null, 'group' => 'Notifications', 'type' => 'string', 'label' => 'Discord Webhook URL', 'description' => 'Discord webhook for admin notifications', 'sort_order' => 1],
            ['key' => 'notification_retention_days', 'value' => '90', 'group' => 'Notifications', 'type' => 'integer', 'label' => 'Notification Retention (days)', 'description' => 'Days to keep read notifications before cleanup', 'sort_order' => 2],
            ['key' => 'audit_log_retention_days', 'value' => '365', 'group' => 'Notifications', 'type' => 'integer', 'label' => 'Audit Log Retention (days)', 'description' => 'Days to keep audit log entries before cleanup', 'sort_order' => 3],
            ['key' => 'discord_notify_server_restart', 'value' => '0', 'group' => 'Notifications', 'type' => 'boolean', 'label' => 'Discord: Server Restart', 'description' => 'Send Discord notification on server restart', 'sort_order' => 4],
            ['key' => 'discord_notify_notable_kills', 'value' => '0', 'group' => 'Notifications', 'type' => 'boolean', 'label' => 'Discord: Notable Kills', 'description' => 'Send Discord notification for long-distance kills', 'sort_order' => 5],
            ['key' => 'discord_notable_kill_distance', 'value' => '500', 'group' => 'Notifications', 'type' => 'integer', 'label' => 'Discord: Notable Kill Distance (m)', 'description' => 'Minimum kill distance to trigger Discord notification', 'sort_order' => 6],
            ['key' => 'discord_notify_tournament_results', 'value' => '0', 'group' => 'Notifications', 'type' => 'boolean', 'label' => 'Discord: Tournament Results', 'description' => 'Send Discord notification when tournaments are completed', 'sort_order' => 7],
            ['key' => 'discord_notify_match_results', 'value' => '0', 'group' => 'Notifications', 'type' => 'boolean', 'label' => 'Discord: Match Results', 'description' => 'Send Discord notification for match results', 'sort_order' => 8],

            // Server Manager (1)
            ['key' => 'broadcast_templates', 'value' => json_encode([
                ['label' => 'Restart Warning', 'message' => 'Server will restart in 5 minutes. Please finish your current tasks.'],
                ['label' => 'No Teamkills', 'message' => 'Reminder: Intentional teamkilling is not allowed and will result in a ban.'],
                ['label' => 'Welcome', 'message' => 'Welcome to the server! Check our rules at our website.'],
                ['label' => 'Map Rotation', 'message' => 'Map rotation will occur at the end of this round.'],
            ]), 'group' => 'Server Manager', 'type' => 'json', 'label' => 'Broadcast Templates', 'description' => 'Quick broadcast message templates for server managers', 'sort_order' => 1],

            // Appearance (3)
            ['key' => 'custom_logo_url', 'value' => null, 'group' => 'Appearance', 'type' => 'string', 'label' => 'Custom Logo URL', 'description' => 'URL to a custom logo image (replaces text brand)', 'sort_order' => 1],
            ['key' => 'primary_accent_color', 'value' => '#22c55e', 'group' => 'Appearance', 'type' => 'color', 'label' => 'Primary Accent Color', 'description' => 'Main accent color used across the site', 'sort_order' => 2],
            ['key' => 'custom_css', 'value' => null, 'group' => 'Appearance', 'type' => 'text', 'label' => 'Custom CSS', 'description' => 'Additional CSS injected into every page', 'sort_order' => 3],

            // Player Progression (10)
            ['key' => 'leveling_base_xp', 'value' => '1000', 'group' => 'Player Progression', 'type' => 'integer', 'label' => 'Leveling: Base XP', 'description' => 'Base XP required for level 1 (exponential curve from this)', 'sort_order' => 1],
            ['key' => 'leveling_xp_curve_exponent', 'value' => '1.15', 'group' => 'Player Progression', 'type' => 'string', 'label' => 'Leveling: XP Curve Exponent', 'description' => 'Exponent for XP curve formula: BASE_XP * pow(level, exponent)', 'sort_order' => 2],
            ['key' => 'leveling_level_cap', 'value' => '500', 'group' => 'Player Progression', 'type' => 'integer', 'label' => 'Leveling: Max Level', 'description' => 'Maximum player level (50 ranks across 10 eras)', 'sort_order' => 3],
            ['key' => 'leveling_achievement_points_weight', 'value' => '1.0', 'group' => 'Player Progression', 'type' => 'string', 'label' => 'Leveling: Achievement Points Weight', 'description' => 'Multiplier for achievement points in level XP calculation', 'sort_order' => 4],
            ['key' => 'achievement_rarity_common_max', 'value' => '50', 'group' => 'Player Progression', 'type' => 'integer', 'label' => 'Achievement: Common Rarity (%)', 'description' => 'Maximum percentage of players with achievement for Common rarity', 'sort_order' => 5],
            ['key' => 'achievement_rarity_rare_max', 'value' => '25', 'group' => 'Player Progression', 'type' => 'integer', 'label' => 'Achievement: Rare Rarity (%)', 'description' => 'Maximum percentage of players with achievement for Rare rarity', 'sort_order' => 6],
            ['key' => 'achievement_rarity_epic_max', 'value' => '10', 'group' => 'Player Progression', 'type' => 'integer', 'label' => 'Achievement: Epic Rarity (%)', 'description' => 'Maximum percentage of players with achievement for Epic rarity', 'sort_order' => 7],
            ['key' => 'achievement_rarity_legendary_max', 'value' => '5', 'group' => 'Player Progression', 'type' => 'integer', 'label' => 'Achievement: Legendary Rarity (%)', 'description' => 'Below this percentage is Legendary rarity', 'sort_order' => 8],
            ['key' => 'achievement_showcase_max', 'value' => '3', 'group' => 'Player Progression', 'type' => 'integer', 'label' => 'Achievement: Max Showcase', 'description' => 'Maximum achievements a player can showcase on profile', 'sort_order' => 9],
            ['key' => 'achievement_points_multiplier', 'value' => '100', 'group' => 'Player Progression', 'type' => 'integer', 'label' => 'Achievement: Base Points', 'description' => 'Base achievement points (multiplied by rarity)', 'sort_order' => 10],

            // Ranked System (13)
            ['key' => 'ranked_base_rating', 'value' => '1500', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Base Rating', 'description' => 'Starting rating for new competitive players (Glicko-2)', 'sort_order' => 1],
            ['key' => 'ranked_starting_rd', 'value' => '350', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Starting Rating Deviation', 'description' => 'Initial RD (rating uncertainty) for new players', 'sort_order' => 2],
            ['key' => 'ranked_volatility', 'value' => '0.06', 'group' => 'Ranked System', 'type' => 'string', 'label' => 'Volatility', 'description' => 'System volatility constant (how quickly ratings change)', 'sort_order' => 3],
            ['key' => 'ranked_placement_games', 'value' => '10', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Placement Games', 'description' => 'Number of games required before placement', 'sort_order' => 4],
            ['key' => 'ranked_decay_days', 'value' => '14', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Rating Decay Days', 'description' => 'Days of inactivity before rating decay starts', 'sort_order' => 5],
            ['key' => 'ranked_phantom_vehicle', 'value' => '1600', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Phantom: Vehicle Destroy', 'description' => 'Phantom opponent rating for vehicle kills', 'sort_order' => 6],
            ['key' => 'ranked_phantom_base', 'value' => '1500', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Phantom: Base Capture', 'description' => 'Phantom opponent rating for base captures', 'sort_order' => 7],
            ['key' => 'ranked_phantom_heal', 'value' => '1300', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Phantom: Heal', 'description' => 'Phantom opponent rating for healing teammates', 'sort_order' => 8],
            ['key' => 'ranked_phantom_supply', 'value' => '1300', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Phantom: Supply Delivery', 'description' => 'Phantom opponent rating for supply deliveries', 'sort_order' => 9],
            ['key' => 'ranked_phantom_building', 'value' => '1200', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Phantom: Building', 'description' => 'Phantom opponent rating for placing buildings', 'sort_order' => 10],
            ['key' => 'ranked_teamkill_penalty_rd', 'value' => '150', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Team Kill Penalty RD', 'description' => 'Phantom opponent RD for team kill penalties (higher = more severe)', 'sort_order' => 11],
            ['key' => 'ranked_friendly_fire_penalty_rd', 'value' => '250', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Friendly Fire Penalty RD', 'description' => 'Phantom opponent RD for friendly fire penalties', 'sort_order' => 12],
            ['key' => 'ranked_calculation_interval_hours', 'value' => '4', 'group' => 'Ranked System', 'type' => 'integer', 'label' => 'Calculation Interval (hours)', 'description' => 'How often the rating calculation job runs', 'sort_order' => 13],

            // Teams (5)
            ['key' => 'team_max_size', 'value' => '16', 'group' => 'Teams', 'type' => 'integer', 'label' => 'Max Team Size', 'description' => 'Maximum number of members per team', 'sort_order' => 1],
            ['key' => 'team_min_size_tournament', 'value' => '4', 'group' => 'Teams', 'type' => 'integer', 'label' => 'Min Size for Tournaments', 'description' => 'Minimum team size to register for tournaments', 'sort_order' => 2],
            ['key' => 'team_invitation_expiry_days', 'value' => '7', 'group' => 'Teams', 'type' => 'integer', 'label' => 'Invitation Expiry (days)', 'description' => 'Days before team invitations expire', 'sort_order' => 3],
            ['key' => 'team_application_expiry_days', 'value' => '7', 'group' => 'Teams', 'type' => 'integer', 'label' => 'Application Expiry (days)', 'description' => 'Days before team applications expire', 'sort_order' => 4],
            ['key' => 'scrim_invitation_expiry_days', 'value' => '7', 'group' => 'Teams', 'type' => 'integer', 'label' => 'Scrim Invitation Expiry (days)', 'description' => 'Days before scrim invitations expire', 'sort_order' => 5],

            // Content Creators (5)
            ['key' => 'creator_verification_followers', 'value' => '100', 'group' => 'Content Creators', 'type' => 'integer', 'label' => 'Verification: Min Followers', 'description' => 'Minimum followers for automatic verification consideration', 'sort_order' => 1],
            ['key' => 'creator_featured_slots', 'value' => '3', 'group' => 'Content Creators', 'type' => 'integer', 'label' => 'Featured Creator Slots', 'description' => 'Number of featured creator slots on homepage', 'sort_order' => 2],
            ['key' => 'clip_approval_threshold', 'value' => '10', 'group' => 'Content Creators', 'type' => 'integer', 'label' => 'Clip: Auto-Approval Votes', 'description' => 'Votes needed for automatic clip approval (0 = manual only)', 'sort_order' => 3],
            ['key' => 'clip_of_week_rotation_day', 'value' => '1', 'group' => 'Content Creators', 'type' => 'integer', 'label' => 'Clip of Week: Rotation Day', 'description' => 'Day of week for Clip of the Week rotation (1 = Monday, 7 = Sunday)', 'sort_order' => 4],
            ['key' => 'clip_max_duration_seconds', 'value' => '120', 'group' => 'Content Creators', 'type' => 'integer', 'label' => 'Clip: Max Duration (seconds)', 'description' => 'Maximum clip duration in seconds', 'sort_order' => 5],

            // System Performance (5)
            ['key' => 'analytics_retention_days', 'value' => '90', 'group' => 'System Performance', 'type' => 'integer', 'label' => 'Analytics Retention (days)', 'description' => 'Days to keep analytics events before cleanup', 'sort_order' => 1],
            ['key' => 'metrics_retention_days', 'value' => '90', 'group' => 'System Performance', 'type' => 'integer', 'label' => 'Metrics Retention (days)', 'description' => 'Days to keep system metrics before cleanup', 'sort_order' => 2],
            ['key' => 'cache_warm_interval_minutes', 'value' => '4', 'group' => 'System Performance', 'type' => 'integer', 'label' => 'Cache Warm Interval (minutes)', 'description' => 'How often leaderboard cache warming runs', 'sort_order' => 3],
            ['key' => 'max_concurrent_jobs', 'value' => '3', 'group' => 'System Performance', 'type' => 'integer', 'label' => 'Max Concurrent Queue Jobs', 'description' => 'Maximum number of queue jobs to process simultaneously', 'sort_order' => 4],
            ['key' => 'api_rate_limit_burst', 'value' => '10', 'group' => 'System Performance', 'type' => 'integer', 'label' => 'API Rate Limit Burst', 'description' => 'Additional requests allowed in burst for API rate limiting', 'sort_order' => 5],
        ];

        foreach ($settings as $setting) {
            SiteSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
