# Moderation System

Player warnings, moderator notes, and automated chat moderation.

## Overview

The moderation system provides tools for managing player behavior through warnings, internal notes, and automated chat filtering. This is separate from the ban system - moderation is for tracking minor issues and managing escalation before bans are necessary.

## Database Tables

### `player_warnings`
Formal warnings issued to players.

**Columns:**
- `id` - Primary key
- `user_id` - Warned user (FK to users, nullable - can warn by player_uuid only)
- `player_uuid` - Player's game UUID
- `player_name` - Player name at time of warning
- `issued_by` - Admin/moderator who issued warning (FK to users)
- `reason` - Warning reason
- `severity` - Severity level (low/medium/high/critical)
- `notes` - Internal notes
- `acknowledged` - Whether player has seen the warning (boolean)
- `acknowledged_at` - When player acknowledged (nullable)
- `expires_at` - Optional expiration date (nullable)
- `created_at`, `updated_at`

**Indexes:**
- `user_id`, `player_uuid`, `issued_by`, `severity`, `expires_at`

### `moderator_notes`
Internal notes for tracking player behavior.

**Columns:**
- `id` - Primary key
- `user_id` - Subject user (FK to users, nullable)
- `player_uuid` - Player's game UUID
- `player_name` - Player name
- `moderator_id` - Moderator who created note (FK to users)
- `note` - Note content
- `category` - Category (behavior/language/cheating/griefing/other)
- `is_flagged` - Mark for follow-up (boolean)
- `created_at`, `updated_at`

**Indexes:**
- `user_id`, `player_uuid`, `moderator_id`, `category`, `is_flagged`

### `chat_events` additions
New columns added to existing `chat_events` table:

- `is_flagged` (boolean, default false) - Automatically flagged by keyword filter
- `flagged_reason` (text, nullable) - Which keyword(s) triggered the flag
- `reviewed` (boolean, default false) - Moderator has reviewed
- `reviewed_by` (bigint, nullable) - Moderator who reviewed (FK to users)
- `reviewed_at` (datetime, nullable) - Review timestamp
- `action_taken` (string, nullable) - Action taken (none/warned/temp_banned/banned)

## Models

### `PlayerWarning`
**Location:** `app/Models/PlayerWarning.php`

**Relationships:**
- `belongsTo(User::class)` - Warned user (optional)
- `belongsTo(User::class, 'issued_by')` - Issuer

**Scopes:**
- `scopeActive($query)` - Non-expired warnings
- `scopeForPlayer($query, string $uuid)`
- `scopeBySeverity($query, string $severity)`

**Constants:**
```php
const SEVERITY_LOW = 'low';
const SEVERITY_MEDIUM = 'medium';
const SEVERITY_HIGH = 'high';
const SEVERITY_CRITICAL = 'critical';
```

**Methods:**
- `isExpired(): bool`
- `isActive(): bool`

### `ModeratorNote`
**Location:** `app/Models/ModeratorNote.php`

**Relationships:**
- `belongsTo(User::class)` - Subject user (optional)
- `belongsTo(User::class, 'moderator_id')` - Author

**Scopes:**
- `scopeForPlayer($query, string $uuid)`
- `scopeFlagged($query)`
- `scopeByCategory($query, string $category)`

**Constants:**
```php
const CATEGORY_BEHAVIOR = 'behavior';
const CATEGORY_LANGUAGE = 'language';
const CATEGORY_CHEATING = 'cheating';
const CATEGORY_GRIEFING = 'griefing';
const CATEGORY_OTHER = 'other';
```

## Services

### `ModerationService`
**Location:** `app/Services/ModerationService.php`

**Core Methods:**

#### `issueWarning(array $data, User $moderator): PlayerWarning`
Issue a warning to a player.

**Parameters:**
- `$data` - Array with:
  - `player_uuid` (required)
  - `player_name` (required)
  - `reason` (required)
  - `severity` (required) - low|medium|high|critical
  - `notes` (optional)
  - `expires_at` (optional) - Carbon date or null
- `$moderator` - Moderator issuing warning

**Returns:** `PlayerWarning` instance

**Side effects:**
- Creates PlayerWarning record
- Links to User if found by player_uuid
- Sends in-game notification if player is online
- Logs admin action

#### `addNote(array $data, User $moderator): ModeratorNote`
Add internal moderator note.

**Parameters:**
- `$data` - Array with:
  - `player_uuid` (required)
  - `player_name` (required)
  - `note` (required)
  - `category` (required)
  - `is_flagged` (optional, default false)
- `$moderator` - Moderator creating note

**Returns:** `ModeratorNote` instance

#### `flagChatMessage(ChatEvent $chatEvent, string $reason): void`
Flag a chat message for review.

**Parameters:**
- `$chatEvent` - The chat message
- `$reason` - Why it was flagged (matched keywords)

**Side effects:**
- Sets `is_flagged = true`
- Sets `flagged_reason`
- Does NOT auto-ban or warn (manual review required)

#### `reviewChatMessage(ChatEvent $chatEvent, User $moderator, string $action): void`
Review a flagged chat message.

**Parameters:**
- `$chatEvent` - Flagged message
- `$moderator` - Reviewing moderator
- `$action` - Action taken (none|warned|temp_banned|banned)

**Side effects:**
- Marks as reviewed
- Records action taken
- If action requires warning/ban, triggers appropriate service

#### `getPlayerHistory(string $playerUuid): array`
Get complete moderation history for a player.

**Returns:** Array with:
- `warnings` - Collection of PlayerWarning
- `notes` - Collection of ModeratorNote
- `flagged_chat` - Collection of flagged ChatEvent
- `bans` - Collection of BanHistory (from BanService)

## Controllers

### `ModerationController`
**Location:** `app/Http/Controllers/Admin/ModerationController.php`

**Routes prefix:** `/admin/moderation`

**Methods:**

| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/` | Moderation dashboard |
| `warnings()` | GET `/warnings` | List all warnings with filters |
| `issueWarning()` | POST `/users/{user}/warn` | Issue warning form handler |
| `notes()` | GET `/notes` | List moderator notes |
| `addNote()` | POST `/users/{user}/note` | Add note form handler |
| `flaggedChat()` | GET `/flagged-chat` | Flagged chat messages |
| `reviewChat()` | POST `/chat/{chat}/review` | Review flagged message |
| `importBans()` | POST `/import-bans` | Import bans from external source |

**Dashboard stats (index):**
- Total active warnings
- Flagged chat messages pending review
- Notes created this week
- Top warning reasons

## Views

**Location:** `resources/views/admin/moderation/`

- `index.blade.php` - Dashboard with stats, recent warnings, flagged chat
- `warnings.blade.php` - Full warnings list with severity badges
- `notes.blade.php` - Moderator notes list with search/filter
- `flagged-chat.blade.php` - Flagged messages requiring review

**Components:**
- Severity badges (color-coded by severity level)
- Player lookup modal (search by name/UUID)
- Quick action buttons (warn/note/ban)

## Chat Filtering

### Blocked Words System

**Site Setting:** `blocked_chat_words` (text area, one per line)

**Automatic flagging:**
- When a player sends a chat message (via `/api/v1/chat-events`)
- StatsController checks message against blocked words list
- If match found: `ModerationService::flagChatMessage()` is called
- Message is stored but flagged for review

**Example blocked words:**
```
hack
cheat
exploit
[racial slur]
[offensive term]
```

**Case insensitive matching** with word boundary detection (won't flag "hacker" in "hackneyed").

### Manual Review Workflow

1. Moderator views `/admin/moderation/flagged-chat`
2. Sees message content, player, matched keywords
3. Takes action:
   - **None** - False positive, dismiss flag
   - **Warned** - Issue formal warning
   - **Temp Banned** - Temporary ban (24h default)
   - **Banned** - Permanent ban
4. Action is recorded and executed automatically

## Warning Escalation

**Recommended escalation path:**

1. **First offense (low)** - Verbal warning (mod note)
2. **Second offense (medium)** - Formal warning
3. **Third offense (high)** - Formal warning + 24h temp ban
4. **Fourth offense (critical)** - Permanent ban

**Auto-escalation setting:** `auto_ban_threshold`
- Site setting (integer, default 5)
- If player accumulates X active warnings, auto-ban is triggered
- Requires manual admin approval

## Audit Logging

**Logged actions:**
- `moderation.warning.issued`
- `moderation.note.added`
- `moderation.chat.flagged`
- `moderation.chat.reviewed`
- `moderation.bans.imported`

**Metadata:**
- Target player UUID/name
- Moderator ID
- Reason/category
- Action taken

## Admin Sidebar

**Category:** Moderation

**Menu items:**
- **Reports** - Player conduct reports (links to `/admin/reports`)
- **Warnings** - Warning management (shows active warning count badge)
- **Bans** - Ban system (shows pending appeals badge)

**Badge for flagged chat:**
```php
@php
$flaggedChatCount = \DB::table('chat_events')
    ->where('is_flagged', true)
    ->where('reviewed', false)
    ->count();
@endphp
```

## Integration with Other Systems

### With Ban System
- High severity warnings link to ban creation
- Ban history includes associated warnings
- Auto-ban threshold integration

### With Player Stats
- Warning count displayed on player profiles
- Moderator notes visible in admin player view
- Chat history with flagged messages highlighted

### With Discord
Optional Discord webhook notifications for:
- Critical warnings issued
- Auto-ban threshold reached
- Mass flagged chat events (spam detection)

## Usage Examples

### Issue a warning
```php
$moderationService = app(ModerationService::class);

$warning = $moderationService->issueWarning([
    'player_uuid' => '550e8400-e29b-41d4-a716-446655440000',
    'player_name' => 'PlayerName',
    'reason' => 'Excessive griefing of teammates',
    'severity' => PlayerWarning::SEVERITY_MEDIUM,
    'notes' => 'Destroyed friendly radio twice',
    'expires_at' => now()->addDays(30),
], $moderator);
```

### Add moderator note
```php
$note = $moderationService->addNote([
    'player_uuid' => '550e8400-e29b-41d4-a716-446655440000',
    'player_name' => 'PlayerName',
    'note' => 'Player apologized, seems to understand now',
    'category' => ModeratorNote::CATEGORY_BEHAVIOR,
    'is_flagged' => false,
], $moderator);
```

### Get player history
```php
$history = $moderationService->getPlayerHistory('550e8400-...');

echo "Warnings: " . $history['warnings']->count();
echo "Notes: " . $history['notes']->count();
echo "Bans: " . $history['bans']->count();
```

### Review flagged chat
```php
$chatEvent = ChatEvent::find($id);
$moderationService->reviewChatMessage($chatEvent, $moderator, 'warned');
```

## Site Settings

**Moderation-related settings:**

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `blocked_chat_words` | text | - | One keyword per line |
| `auto_ban_threshold` | integer | 5 | Warnings before auto-ban |
| `reputation_vote_cooldown_hours` | integer | 24 | Rep vote cooldown |
| `player_report_auto_flag_count` | integer | 3 | Reports before auto-flag |

## Future Enhancements

- Automated warning expiration
- Warning templates
- Bulk moderation actions
- Chat sentiment analysis
- Player behavior scores
- Moderator performance metrics
