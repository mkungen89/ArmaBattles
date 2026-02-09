# Discord Rich Presence Integration

This document explains how the Discord Rich Presence system works and how to integrate it with a Discord bot.

## Architecture

The Discord Rich Presence system consists of three main components:

1. **Laravel Application** (this codebase) - Tracks user activity and provides presence data
2. **Discord Bot** (separate service) - Fetches presence data and updates Discord RPC
3. **Discord Client** - Displays the Rich Presence on user profiles

```
User Activity → Laravel → Discord Bot → Discord RPC → Discord Client
```

## Laravel Implementation (Complete)

### Database

The `discord_rich_presence` table stores presence data:

- `user_id` - Link to users table
- `discord_user_id` - Discord snowflake ID (optional)
- `current_activity` - Activity type (playing, watching_tournament, browsing)
- `activity_details` - JSON with activity specifics
- `server_id` / `tournament_id` - Optional FK to related entities
- `started_at` - When activity started (for elapsed time)
- `enabled` - User's preference toggle
- `last_updated_at` - Last update timestamp

### Service Layer

`DiscordPresenceService` provides methods for:

- `updatePlayingPresence()` - Track server gameplay
- `updateWatchingPresence()` - Track tournament viewing
- `updateBrowsingPresence()` - Track general browsing
- `clearPresence()` - Clear user's presence
- `enablePresence()` / `disablePresence()` - User preferences
- `getDiscordPayload()` - Generate Discord RPC payload
- `getActivePresences()` - Fetch all active presences
- `refreshStalePresences()` - Update outdated records

### API Endpoints

**User Management:**
- `GET /discord/presence/settings` - Settings page
- `POST /discord/presence/enable` - Enable presence
- `DELETE /discord/presence/disable` - Disable presence
- `GET /discord/presence/current` - Get current user presence (auth required)
- `POST /discord/presence/activity` - Update activity (auth required)

**Bot Integration (Public):**
- `GET /api/discord/presences/active` - Get all active presences (no auth)

### Activity Tracking

Activity is automatically tracked when users:

1. **Play on Servers** - Call `updatePlayingPresence()` when viewing server pages
2. **Watch Tournaments** - Call `updateWatchingPresence()` on tournament pages
3. **Browse Community** - Call `updateBrowsingPresence()` on other pages

**Example Integration:**

```javascript
// In your Blade templates or frontend
fetch('/discord/presence/activity', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        activity: 'playing',
        server_id: 123,
        details: {
            map: 'Everon',
            player_count: '50/64'
        }
    })
});
```

### Discord Payload Format

The `getDiscordPayload()` method generates payloads like:

```php
[
    'details' => 'Playing on [Server Name]',
    'state' => '50/64 players',
    'timestamps' => [
        'start' => 1704398400  // Unix timestamp
    ],
    'assets' => [
        'large_image' => 'arma_reforger_logo',
        'large_text' => 'Arma Reforger Community',
        'small_image' => 'playing_icon',
        'small_text' => 'Playing'
    ],
    'buttons' => [
        ['label' => 'View Server', 'url' => 'https://...']
    ]
]
```

## Discord Bot Implementation (To-Do)

### Requirements

- Discord Application with Rich Presence enabled
- Discord Bot token with proper permissions
- Node.js or Python bot framework
- Access to `/api/discord/presences/active` endpoint

### Bot Flow

1. **Poll Laravel API** - Fetch active presences every 30 seconds
2. **Match Users** - Match `discord_user_id` from Laravel to Discord user IDs
3. **Update RPC** - Use Discord Gateway to update Rich Presence
4. **Handle Errors** - Log failures, retry on timeout

### Example Bot (Pseudo-code)

```javascript
const Discord = require('discord.js');
const axios = require('axios');

const client = new Discord.Client();
const LARAVEL_API = 'https://your-domain.com/api/discord/presences/active';

// Poll presences every 30 seconds
setInterval(async () => {
    try {
        const response = await axios.get(LARAVEL_API);
        const presences = response.data.presences;

        for (const presence of presences) {
            if (!presence.discord_user_id) continue;

            const user = await client.users.fetch(presence.discord_user_id);

            // Update Rich Presence
            await user.setActivity({
                type: 'PLAYING',
                name: 'Arma Reforger',
                details: presence.payload.details,
                state: presence.payload.state,
                timestamps: presence.payload.timestamps,
                assets: presence.payload.assets,
                buttons: presence.payload.buttons
            });
        }
    } catch (error) {
        console.error('Failed to update presences:', error);
    }
}, 30000);

client.login(process.env.DISCORD_BOT_TOKEN);
```

### Discord Application Setup

1. Go to [Discord Developer Portal](https://discord.com/developers/applications)
2. Create a new application
3. Enable Rich Presence in Settings → Rich Presence
4. Upload assets (images) for `large_image`, `small_image` keys:
   - `arma_reforger_logo` - Main game logo
   - `tournament_icon` - Tournament icon
   - `community_logo` - Community logo
   - `playing_icon` - Playing indicator
   - `watching_icon` - Watching indicator
5. Create a bot and get the bot token
6. Invite bot to your server with proper permissions

### Asset Guidelines

- **Large Image**: 1024x1024px (minimum 512x512px)
- **Small Image**: 256x256px (minimum 128x128px)
- Formats: PNG, JPG, GIF (non-animated)
- Max file size: 10MB per asset

## User Settings

Users can manage Discord Rich Presence at:

```
/discord/presence/settings
```

Features:
- Enable/disable presence tracking
- Link Discord User ID (optional but recommended)
- View current activity status
- See elapsed time for current activity

## Scheduled Tasks

Add to `routes/console.php` to refresh stale presences:

```php
Schedule::command('discord:refresh-presences')->everyMinute();
```

## Privacy & Security

- Users must explicitly enable Discord Rich Presence
- Only users with `enabled = true` appear in public API
- Discord User ID is optional (improves matching accuracy)
- Presence data is cleared when users disable the feature
- No sensitive data is exposed via API

## Testing

### Manual Testing

1. Enable Discord Presence in user settings
2. Visit a server page → Check activity updates to "Playing"
3. Visit a tournament page → Check activity updates to "Watching"
4. Check `/api/discord/presences/active` → Verify presence appears
5. Disable Discord Presence → Verify presence is cleared

### API Testing

```bash
# Get active presences (no auth)
curl https://your-domain.com/api/discord/presences/active

# Get current user presence (auth required)
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-domain.com/discord/presence/current

# Update activity (auth required)
curl -X POST \
     -H "Content-Type: application/json" \
     -H "X-CSRF-TOKEN: YOUR_CSRF_TOKEN" \
     -d '{"activity":"playing","server_id":123}' \
     https://your-domain.com/discord/presence/activity
```

## Troubleshooting

### Presence Not Updating

- Check user has enabled Discord Presence in settings
- Verify `last_updated_at` is within last 30 seconds
- Check scheduled task `discord:refresh-presences` is running
- Verify Discord User ID is correct (18-digit snowflake)

### Bot Not Receiving Presences

- Verify `/api/discord/presences/active` endpoint is accessible
- Check bot polling interval (recommended: 30s)
- Verify Discord User IDs match between Laravel and Discord
- Check bot has proper permissions in Discord server

### Images Not Showing

- Verify assets are uploaded to Discord Developer Portal
- Check asset keys match exactly (case-sensitive)
- Ensure asset names use underscores, not spaces
- Assets take ~5 minutes to propagate after upload

## Future Enhancements

- [ ] Implement Discord OAuth for automatic Discord User ID linking
- [ ] Add more activity types (creating tournament, scrim match, etc.)
- [ ] Real-time presence updates via WebSockets instead of polling
- [ ] Player stats embed in Discord profile (requires Discord bot commands)
- [ ] Party system (show teammates in presence)
- [ ] Spectator count for tournaments
- [ ] Match outcome display (Win/Loss/Draw)

## References

- [Discord Rich Presence Documentation](https://discord.com/developers/docs/rich-presence/how-to)
- [Discord Gateway Documentation](https://discord.com/developers/docs/topics/gateway)
- [Discord Developer Portal](https://discord.com/developers/applications)
- [Laravel Sanctum API Authentication](https://laravel.com/docs/sanctum)

## Support

For questions or issues:

1. Check this documentation
2. Review `/api/discord/presences/active` response format
3. Test endpoints with cURL/Postman
4. Check Laravel logs at `storage/logs/laravel.log`
5. Verify Discord bot logs for connection issues

---

**Last Updated:** 2026-02-08
**Status:** Backend complete, Discord bot integration pending
**Maintainer:** See CLAUDE.md for project contact
