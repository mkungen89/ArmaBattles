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

            // Security (5)
            ['key' => 'allow_registration', 'value' => '1', 'group' => 'Security', 'type' => 'boolean', 'label' => 'Allow Registration', 'description' => 'Allow new users to register with email/password', 'sort_order' => 1],
            ['key' => 'allow_steam_login', 'value' => '1', 'group' => 'Security', 'type' => 'boolean', 'label' => 'Allow Steam Login', 'description' => 'Allow users to log in via Steam', 'sort_order' => 2],
            ['key' => 'force_2fa_staff', 'value' => '0', 'group' => 'Security', 'type' => 'boolean', 'label' => 'Force 2FA for Staff', 'description' => 'Require two-factor authentication for admins and moderators', 'sort_order' => 3],
            ['key' => 'login_rate_limit', 'value' => '5', 'group' => 'Security', 'type' => 'integer', 'label' => 'Login Rate Limit', 'description' => 'Maximum login attempts per minute', 'sort_order' => 4],
            ['key' => 'session_lifetime', 'value' => '120', 'group' => 'Security', 'type' => 'integer', 'label' => 'Session Lifetime (minutes)', 'description' => 'How long user sessions last in minutes', 'sort_order' => 5],

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

            // Moderation (2)
            ['key' => 'blocked_chat_words', 'value' => null, 'group' => 'Moderation', 'type' => 'text', 'label' => 'Blocked Chat Words', 'description' => 'Comma-separated list of blocked words for chat filtering', 'sort_order' => 1],
            ['key' => 'auto_ban_threshold', 'value' => '0', 'group' => 'Moderation', 'type' => 'integer', 'label' => 'Auto-Ban Threshold', 'description' => 'Number of anti-cheat flags before automatic ban (0 = disabled)', 'sort_order' => 2],

            // Notifications (3)
            ['key' => 'discord_webhook_url', 'value' => null, 'group' => 'Notifications', 'type' => 'string', 'label' => 'Discord Webhook URL', 'description' => 'Discord webhook for admin notifications', 'sort_order' => 1],
            ['key' => 'notification_retention_days', 'value' => '90', 'group' => 'Notifications', 'type' => 'integer', 'label' => 'Notification Retention (days)', 'description' => 'Days to keep read notifications before cleanup', 'sort_order' => 2],
            ['key' => 'audit_log_retention_days', 'value' => '365', 'group' => 'Notifications', 'type' => 'integer', 'label' => 'Audit Log Retention (days)', 'description' => 'Days to keep audit log entries before cleanup', 'sort_order' => 3],
            ['key' => 'discord_notify_server_restart', 'value' => '0', 'group' => 'Notifications', 'type' => 'boolean', 'label' => 'Discord: Server Restart', 'description' => 'Send Discord notification on server restart', 'sort_order' => 4],
            ['key' => 'discord_notify_notable_kills', 'value' => '0', 'group' => 'Notifications', 'type' => 'boolean', 'label' => 'Discord: Notable Kills', 'description' => 'Send Discord notification for long-distance kills', 'sort_order' => 5],
            ['key' => 'discord_notable_kill_distance', 'value' => '500', 'group' => 'Notifications', 'type' => 'integer', 'label' => 'Discord: Notable Kill Distance (m)', 'description' => 'Minimum kill distance to trigger Discord notification', 'sort_order' => 6],

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
        ];

        foreach ($settings as $setting) {
            SiteSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
