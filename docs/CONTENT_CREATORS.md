# Content Creator System

Multi-platform streamer directory with live status tracking and creator dashboard.

## Overview

The content creator system provides a directory of Arma Reforger streamers and content creators across Twitch, YouTube, Kick, and TikTok. Features include live status tracking, viewer count history, clip submission, and a creator dashboard with analytics.

## Database Tables

### `content_creators` additions
New columns added to existing `content_creators` table:

**Live Status Tracking:**
- `is_live` (boolean, default false) - Currently streaming
- `live_since` (datetime, nullable) - When stream started
- `last_live_check` (datetime, nullable) - Last API check timestamp
- `current_viewers` (integer, nullable) - Current viewer count
- `stream_title` (string, nullable) - Current stream title
- `stream_thumbnail` (string, nullable) - Stream thumbnail URL

**Approval System:**
- `is_approved` (boolean, default false) - Admin approved
- `approved_at` (datetime, nullable) - Approval timestamp
- `approved_by` (bigint, nullable) - Admin who approved (FK to users)

**Featured System:**
- `is_featured` (boolean, default false) - Featured creator
- `featured_at` (datetime, nullable) - When featured
- `featured_until` (datetime, nullable) - Featured expiration

**Existing columns:**
- `user_id` (FK to users)
- `platform` (twitch|youtube|kick|tiktok)
- `channel_url`
- `display_name`
- `bio`
- `avatar_url`
- `follower_count`
- `is_verified`
- `created_at`, `updated_at`

## Models

### `ContentCreator`
**Location:** `app/Models/ContentCreator.php`

**Constants:**
```php
const PLATFORM_TWITCH = 'twitch';
const PLATFORM_YOUTUBE = 'youtube';
const PLATFORM_KICK = 'kick';
const PLATFORM_TIKTOK = 'tiktok';
```

**Relationships:**
- `belongsTo(User::class)` - Associated user
- `belongsTo(User::class, 'approved_by')` - Admin approver
- `hasMany(HighlightClip::class)` - Creator's clips

**Scopes:**
- `scopeApproved($query)` - is_approved = true
- `scopeLive($query)` - is_live = true
- `scopeFeatured($query)` - is_featured = true AND featured_until > now()
- `scopeByPlatform($query, string $platform)`

**Methods:**
- `isLive(): bool`
- `isFeatured(): bool`
- `getChannelUsername(): string` - Extract username from URL
- `getPlatformIcon(): string` - Platform-specific icon class
- `updateLiveStatus(array $data): void` - Update from API data

**Accessors:**
- `live_duration` - Carbon diff if currently live

## Services

### Streaming Services Architecture

All streaming services implement a common interface for consistency.

**Base methods:**
- `getChannelInfo(string $identifier): ?array`
- `isLive(string $identifier): bool`
- `getLiveStreamData(string $identifier): ?array`

### `TwitchStreamService`
**Location:** `app/Services/Streaming/TwitchStreamService.php`

**Dependencies:**
- `TWITCH_CLIENT_ID` - Twitch application client ID
- `TWITCH_CLIENT_SECRET` - Twitch application secret

**Configuration:** `config/services.php` → `twitch`

**API Endpoints:**
- OAuth: `https://id.twitch.tv/oauth2/token`
- Helix API: `https://api.twitch.tv/helix/`

**Methods:**

#### `getAccessToken(): ?string`
Get OAuth2 app access token (cached for 1 hour).

**Returns:** Access token or null on failure

#### `getChannelInfo(string $username): ?array`
Get channel metadata.

**Parameters:**
- `$username` - Twitch username (no @ prefix)

**Returns:**
```php
[
    'id' => '12345',
    'username' => 'channel_name',
    'display_name' => 'Channel Name',
    'bio' => 'Channel description',
    'avatar_url' => 'https://...',
    'follower_count' => 1234,
    'view_count' => 56789,
]
```

#### `isLive(string $username): bool`
Check if channel is currently streaming.

#### `getLiveStreamData(string $username): ?array`
Get live stream details.

**Returns:**
```php
[
    'is_live' => true,
    'title' => 'Stream title',
    'viewer_count' => 123,
    'started_at' => '2024-02-14T10:00:00Z',
    'thumbnail_url' => 'https://...',
    'game_name' => 'Arma Reforger',
]
```

**Caching:**
- Access token: 1 hour
- Live status: 3 minutes (aligned with check command)
- Channel info: 1 hour

### `YouTubeStreamService`
**Location:** `app/Services/Streaming/YouTubeStreamService.php`

**Dependencies:**
- `YOUTUBE_API_KEY` - YouTube Data API v3 key

**Configuration:** `config/services.php` → `youtube`

**API Endpoints:**
- `https://www.googleapis.com/youtube/v3/channels`
- `https://www.googleapis.com/youtube/v3/search`

**Methods:**

#### `getChannelInfo(string $channelIdOrUsername): ?array`
Get channel metadata. Supports:
- Channel IDs (UC...)
- @username handles
- Legacy usernames

**Auto-resolution:** Tries forHandle → forUsername → direct ID lookup

**Returns:** Same structure as Twitch

#### `isLive(string $identifier): bool`
Check if channel has active live stream.

**Uses:** YouTube Search API with eventType=live filter

#### `getLiveStreamData(string $identifier): ?array`
Get live stream details.

**Returns:** Same structure as Twitch

**Caching:**
- Channel info: 1 hour
- Live status: 3 minutes
- Search results: 5 minutes

**Quota considerations:**
- YouTube API has daily quota limits
- Channel info: 3 units
- Search: 100 units
- Default quota: 10,000 units/day
- Monitor usage in Google Cloud Console

### `KickStreamService`
**Location:** `app/Services/Streaming/KickStreamService.php`

**Dependencies:** None (web scraping)

**Note:** Kick has no official public API. Uses web scraping.

**Methods:**

#### `getChannelInfo(string $username): ?array`
Scrapes channel page for metadata.

**Technique:**
- Fetches `https://kick.com/{username}`
- Parses JSON-LD schema or meta tags
- Extracts follower count from page HTML

#### `isLive(string $username): bool`
Checks for livestream badge on channel page.

#### `getLiveStreamData(string $username): ?array`
Extracts stream data from page source.

**Caching:**
- All methods: 5 minutes (higher due to scraping overhead)

**Limitations:**
- Fragile (breaks if Kick changes HTML structure)
- No historical data
- Rate limiting may occur

**Future:** Monitor for official API release

### `TikTokStreamService`
**Location:** `app/Services/Streaming/TikTokStreamService.php`

**Dependencies:** None (web scraping)

**Note:** TikTok has no official live streaming API for third parties.

**Methods:**

Similar to KickStreamService - web scraping based.

**Challenges:**
- TikTok aggressively blocks scrapers
- Requires user-agent spoofing
- Live status unreliable
- Consider this a placeholder until official API

**Recommendation:**
Currently marked as "experimental" - may not work reliably.

## Commands

### `CheckCreatorsLiveStatus`
**Command:** `creators:check-live`

**Schedule:** Every 3 minutes (`routes/console.php` line 117-119)

**Signature:** `php artisan creators:check-live`

**Functionality:**

1. Fetches all approved creators with non-null `channel_url`
2. Instantiates appropriate streaming service per platform
3. For each creator:
   - Extracts username from URL
   - Calls `getLiveStreamData()`
   - Updates creator record with live status
   - If status changed (went live/offline), logs event
4. Outputs stats: X creators checked, Y currently live

**Performance:**
- Runs concurrently for different platforms (no blocking)
- Uses cached API responses (3-5min TTL)
- Processes ~100 creators in <10 seconds

**Example output:**
```
Checking live status for all creators...
Checking Twitch: username1, username2
Checking YouTube: @channel1, UCxxx
Checking Kick: kickuser1
Checked 15 creators. Currently live: 3
```

## Controllers

### `ContentCreatorAdminController`
**Location:** `app/Http/Controllers/Admin/ContentCreatorAdminController.php`

**Routes prefix:** `/admin/creators`

Admin management of creators.

**Methods:**

| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/` | List all creators with filters |
| `edit()` | GET `/{creator}/edit` | Edit creator details |
| `update()` | PUT `/{creator}` | Save changes |
| `show()` | GET `/{creator}/show` | View creator details |
| `approve()` | POST `/{creator}/approve` | Approve creator |
| `reject()` | POST `/{creator}/reject` | Reject/unapprove |
| `feature()` | POST `/{creator}/feature` | Make featured |
| `unfeature()` | POST `/{creator}/unfeature` | Remove featured |
| `destroy()` | DELETE `/{creator}` | Delete creator |

**Index filters:**
- Platform (all|twitch|youtube|kick|tiktok)
- Status (all|pending|approved)
- Live status (all|live|offline)
- Featured (all|featured|not_featured)

### `CreatorDashboardController`
**Location:** `app/Http/Controllers/Creator/CreatorDashboardController.php`

**Routes prefix:** `/creator` (auth middleware, creator must exist)

Creator-facing dashboard.

**Methods:**

| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/dashboard` | Main dashboard |
| `stats()` | GET `/stats` | Detailed stats |
| `edit()` | GET `/edit` | Edit profile |
| `update()` | PUT `/update` | Save changes |
| `checkLiveStatus()` | POST `/check-live` | Manual live check |

**Dashboard features:**
- Current live status
- Total views/followers
- Recent stream history (last 7 days)
- Viewer count trends (Chart.js)
- Clip count and recent clips
- Featured status

**Admin methods:**

| Method | Route | Description |
|--------|-------|-------------|
| `adminView()` | GET `/admin/creators/{creator}/dashboard` | View creator's dashboard as admin |
| `adminStats()` | GET `/admin/creators/{creator}/stats-view` | View creator's stats as admin |

### `ContentCreatorController`
**Location:** `app/Http/Controllers/ContentCreatorController.php`

**Routes prefix:** `/creators`

Public creator directory.

**Methods:**

| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/` | Creator directory with filters |
| `show()` | GET `/{creator}` | Creator profile page |
| `register()` | GET `/register` | Registration form (auth) |
| `store()` | POST `/` | Submit registration |

**Registration validation:**
```php
[
    'platform' => 'required|in:twitch,youtube,kick,tiktok',
    'channel_url' => 'required|url|unique:content_creators',
    'display_name' => 'required|string|max:100',
    'bio' => 'nullable|string|max:500',
]
```

**Auto-verification:**
On submission, attempts to fetch channel info from API to verify:
- Channel exists
- URL is valid
- Fetches follower count, avatar

**Approval workflow:**
1. User submits registration
2. System fetches channel data via API
3. Creator created with `is_approved = false`
4. Admin reviews at `/admin/creators`
5. Admin approves/rejects
6. If approved, appears in public directory

## Views

### Admin Views
**Location:** `resources/views/admin/creators/`

- `index.blade.php` - Table with platform icons, live badges, approval status, actions
- `edit.blade.php` - Edit form (display_name, bio, featured, verified)
- `show.blade.php` - Detailed view with stream history, stats

### Creator Dashboard Views
**Location:** `resources/views/creator/`

- `dashboard.blade.php` - Main dashboard with stats cards, live status, charts
- `stats.blade.php` - Detailed analytics (Chart.js - viewers over time, peak viewers, avg duration)
- `edit.blade.php` - Edit bio, social links

### Public Views
**Location:** `resources/views/content-creators/`

- `index.blade.php` - Card grid, filters, search, live badge
- `show.blade.php` - Creator profile with clips, bio, stats

**Live indicator:**
- Pulsing red dot for live creators
- "LIVE" badge with viewer count
- Link to stream

## Live Status UI

**Real-time updates:**
Uses polling (every 3 min aligned with command) or WebSocket (future).

**Alpine.js component:**
```javascript
Alpine.data('liveStatus', (creatorId) => ({
    isLive: false,
    viewers: 0,

    init() {
        this.check();
        setInterval(() => this.check(), 180000); // 3 min
    },

    async check() {
        const res = await fetch(`/api/creators/${creatorId}/live`);
        const data = await res.json();
        this.isLive = data.is_live;
        this.viewers = data.viewers;
    }
}))
```

## Site Settings

**Creator-related settings:**

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `creator_verification_followers` | integer | 50 | Min followers for auto-verify |
| `creator_featured_slots` | integer | 4 | Max featured creators |
| `creator_featured_duration_days` | integer | 7 | Featured duration |

## API Configuration

### Required .env variables:

```bash
# Twitch (required for Twitch creators)
TWITCH_CLIENT_ID=your_client_id_here
TWITCH_CLIENT_SECRET=your_client_secret_here

# YouTube (required for YouTube creators)
YOUTUBE_API_KEY=your_api_key_here

# Kick - no API key needed (web scraping)
# TikTok - no API key needed (web scraping)
```

### Getting API credentials:

**Twitch:**
1. Go to https://dev.twitch.tv/console/apps
2. Create application
3. Add OAuth redirect: `https://yourdomain.com/auth/twitch/callback`
4. Copy Client ID and generate Client Secret
5. Add to .env

**YouTube:**
1. Go to https://console.cloud.google.com/
2. Create project (or use existing)
3. Enable "YouTube Data API v3"
4. Create API key (Credentials → Create Credentials → API Key)
5. Restrict key to YouTube Data API v3
6. Add to .env

**Testing:**
```bash
# Test Twitch integration
php artisan tinker
$service = new \App\Services\Streaming\TwitchStreamService();
$service->getChannelInfo('shroud'); // Replace with valid username

# Test YouTube integration
$service = new \App\Services\Streaming\YouTubeStreamService();
$service->getChannelInfo('@LinusTechTips');
```

## Admin Sidebar

**Category:** Content

**Menu items:**
- **News** - News articles
- **Creators** - Content creator management (shows pending approval count badge)
- **Clips** - Highlight clip moderation

**Badge for pending creators:**
```php
@php
$pendingCreators = \App\Models\ContentCreator::where('is_approved', false)->count();
@endphp
```

## Usage Examples

### Register as creator
```php
ContentCreator::create([
    'user_id' => auth()->id(),
    'platform' => 'twitch',
    'channel_url' => 'https://twitch.tv/username',
    'display_name' => 'My Channel',
    'bio' => 'I stream Arma Reforger tactics',
    'is_approved' => false,
]);
```

### Check live status manually
```php
$twitchService = new TwitchStreamService();
$liveData = $twitchService->getLiveStreamData('username');

if ($liveData && $liveData['is_live']) {
    $creator->update([
        'is_live' => true,
        'live_since' => now(),
        'current_viewers' => $liveData['viewer_count'],
        'stream_title' => $liveData['title'],
        'stream_thumbnail' => $liveData['thumbnail_url'],
    ]);
}
```

### Feature a creator for 7 days
```php
$creator->update([
    'is_featured' => true,
    'featured_at' => now(),
    'featured_until' => now()->addDays(7),
]);
```

## Performance Considerations

**API rate limits:**
- Twitch: 800 requests/min (OAuth app token)
- YouTube: 10,000 quota units/day (~3,000 requests)
- Kick: No official limit, but aggressive rate limiting
- TikTok: Frequently blocks automated requests

**Optimization strategies:**
1. **Aggressive caching** (3-5 min for live status)
2. **Batch processing** (check all creators in one command run)
3. **Conditional checks** (skip if last_live_check < 3 min ago)
4. **Graceful degradation** (show last known status on API failure)

**Database indexing:**
```sql
CREATE INDEX idx_creators_live ON content_creators(is_live, is_approved);
CREATE INDEX idx_creators_platform ON content_creators(platform, is_approved);
CREATE INDEX idx_creators_featured ON content_creators(is_featured, featured_until);
```

## Future Enhancements

- Stream VOD embedding
- Clip auto-import from Twitch
- Multi-stream support (user streaming on multiple platforms)
- Stream notifications (Discord, email, web push)
- Creator analytics dashboard (retention, avg viewers, peak times)
- Follower growth tracking
- Raid detection and tracking
- Integration with Discord roles (auto-role for verified creators)
- Sponsored/partner program
- Creator leaderboard (most hours streamed, highest viewers)
