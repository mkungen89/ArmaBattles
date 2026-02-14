# Ban Management System

Comprehensive ban management with appeals, hardware bans, and automated processing.

## Overview

The ban management system provides admins with complete control over player bans, including temporary/permanent bans, hardware ID bans, IP bans, and a full appeal system where players can contest their bans.

## Database Tables

### `ban_appeals`
Tracks player ban appeals with admin review workflow.

**Columns:**
- `id` - Primary key
- `user_id` - User who submitted appeal (FK to users)
- `reason` - Original ban reason
- `appeal_message` - Player's appeal message
- `status` - Appeal status (pending/approved/rejected)
- `reviewed_by` - Admin who reviewed (FK to users)
- `admin_response` - Admin's response message
- `reviewed_at` - Timestamp of review
- `created_at`, `updated_at`

**Indexes:**
- `user_id`, `status`, `reviewed_by`

### `ban_history`
Complete audit trail of all ban/unban actions.

**Columns:**
- `id` - Primary key
- `user_id` - Banned user (FK to users)
- `admin_id` - Admin who issued ban (FK to users)
- `action` - Action type (ban/unban/appeal_approved/appeal_rejected)
- `reason` - Ban/unban reason
- `ban_type` - Type (temporary/permanent/hardware/ip)
- `duration_hours` - Duration for temp bans (nullable)
- `hardware_id` - Hardware ID for hardware bans (nullable)
- `ip_address` - IP for IP bans (nullable)
- `notes` - Internal admin notes
- `created_at`

**Indexes:**
- `user_id`, `admin_id`, `action`, `created_at`

### User model additions
New fields on `users` table:
- `is_banned` (boolean) - Current ban status
- `banned_until` (datetime, nullable) - For temporary bans
- `banned_at` (datetime, nullable) - When ban was issued
- `ban_reason` (text, nullable) - Current ban reason
- `banned_by` (bigint, nullable) - Admin who banned (FK to users)

## Models

### `BanAppeal`
**Location:** `app/Models/BanAppeal.php`

**Relationships:**
- `belongsTo(User::class)` - User who submitted appeal
- `belongsTo(User::class, 'reviewed_by')` - Admin reviewer

**Scopes:**
- `scopePending($query)` - Filter pending appeals

**Methods:**
- `isPending(): bool`
- `isApproved(): bool`
- `isRejected(): bool`

### `BanHistory`
**Location:** `app/Models/BanHistory.php`

**Relationships:**
- `belongsTo(User::class, 'user_id')` - Banned user
- `belongsTo(User::class, 'admin_id')` - Admin who took action

**Scopes:**
- `scopeForUser($query, User $user)`
- `scopeRecentActions($query, int $days = 30)`

## Services

### `BanService`
**Location:** `app/Services/BanService.php`

**Core Methods:**

#### `banUser(User $user, User $admin, array $data): bool`
Ban a user with specified parameters.

**Parameters:**
- `$user` - User to ban
- `$admin` - Admin issuing ban
- `$data` - Array with keys:
  - `reason` (required) - Ban reason
  - `duration_hours` (optional) - For temporary bans
  - `ban_type` (optional) - permanent|temporary|hardware|ip
  - `hardware_id` (optional) - For hardware bans
  - `ip_address` (optional) - For IP bans
  - `notes` (optional) - Internal notes

**Returns:** `bool` - Success status

**Side effects:**
- Updates user record (is_banned, banned_at, banned_until, ban_reason, banned_by)
- Creates BanHistory record
- Logs admin action via LogsAdminActions trait

#### `unbanUser(User $user, User $admin, ?string $reason = null): bool`
Unban a user.

**Parameters:**
- `$user` - User to unban
- `$admin` - Admin unbanning
- `$reason` - Optional reason for unban

**Returns:** `bool` - Success status

#### `processExpiredBans(): int`
Process all expired temporary bans (called by scheduled command).

**Returns:** `int` - Count of processed bans

**Logic:**
- Finds all users where `is_banned = true` AND `banned_until < now()`
- Unbans each user
- Creates BanHistory record with action='unban'
- Logs as system action

#### `banByHardwareId(string $hardwareId, User $admin, array $data): bool`
Ban by hardware ID (GUID).

#### `banByIpAddress(string $ipAddress, User $admin, array $data): bool`
Ban by IP address.

#### `importBansFromFile(UploadedFile $file, User $admin): array`
Import bans from CSV/JSON file.

**Returns:** Array with success/error counts

## Controllers

### `BanManagementController`
**Location:** `app/Http/Controllers/Admin/BanManagementController.php`

**Routes prefix:** `/admin/bans`

**Methods:**

| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/` | Ban dashboard with stats |
| `bannedUsers()` | GET `/users` | List all banned users |
| `appeals()` | GET `/appeals` | List all ban appeals |
| `showAppeal()` | GET `/appeals/{appeal}` | Appeal detail view |
| `approveAppeal()` | POST `/appeals/{appeal}/approve` | Approve appeal and unban |
| `rejectAppeal()` | POST `/appeals/{appeal}/reject` | Reject appeal |
| `userHistory()` | GET `/users/{user}/history` | User's ban history |
| `banUser()` | POST `/users/{user}/ban` | Ban user form handler |
| `unbanUser()` | POST `/users/{user}/unban` | Unban user |
| `hardwareBanForm()` | GET `/hardware` | Hardware ban form |
| `banByHardwareId()` | POST `/hardware` | Process hardware ban |
| `ipBanForm()` | GET `/ip` | IP ban form |
| `banByIpAddress()` | POST `/ip` | Process IP ban |
| `importForm()` | GET `/import` | Ban import form |
| `importBans()` | POST `/import` | Process ban import |

### `BanAppealController`
**Location:** `app/Http/Controllers/BanAppealController.php`

**Routes prefix:** `/ban-appeals`

**User-facing routes:**

| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/` | User's appeals list |
| `create()` | GET `/create` | Appeal creation form |
| `store()` | POST `/` | Submit appeal |
| `show()` | GET `/{appeal}` | View appeal status |

**Middleware:** `auth` - Only authenticated users

**Validation rules for store():**
```php
[
    'appeal_message' => 'required|string|min:50|max:2000',
]
```

**Business rules:**
- User must be banned to create appeal
- User can only have one pending appeal at a time
- Appeals created with status='pending'

## Views

### Admin Views
**Location:** `resources/views/admin/bans/`

- `index.blade.php` - Dashboard with stats cards, recent bans table
- `users.blade.php` - Paginated list of banned users with unban buttons
- `appeals.blade.php` - Table of all appeals with status filters
- `appeal-detail.blade.php` - Full appeal details with approve/reject form
- `user-history.blade.php` - Timeline of user's ban history
- `hardware.blade.php` - Form for hardware ID bans
- `ip.blade.php` - Form for IP address bans
- `import.blade.php` - CSV/JSON upload form for bulk import

### User Views
**Location:** `resources/views/ban-appeals/`

- `index.blade.php` - User's appeals with status badges
- `create.blade.php` - Appeal submission form with 50-2000 char requirement
- `show.blade.php` - Appeal detail with admin response (if any)

## Scheduled Tasks

### `ProcessExpiredBans`
**Command:** `bans:process-expired`

**Schedule:** Hourly (`routes/console.php` line 43-45)

**Signature:** `php artisan bans:process-expired`

**Functionality:**
- Calls `BanService::processExpiredBans()`
- Automatically unbans users whose `banned_until` has passed
- Creates BanHistory records for each auto-unban
- Outputs count of processed bans

**Example output:**
```
Processing expired temporary bans...
Processed 3 expired ban(s).
```

## Admin Sidebar

**Category:** Moderation

**Menu items:**
- **Reports** - Player conduct reports
- **Warnings** - Player warnings list
- **Bans** - Ban management dashboard (shows pending appeals badge)

**Badge logic:**
```php
@php $pendingAppealsCount = \App\Models\BanAppeal::pending()->count(); @endphp
@if($pendingAppealsCount > 0)
    <span class="badge">{{ $pendingAppealsCount }}</span>
@endif
```

## Audit Logging

All ban actions are logged via the `LogsAdminActions` trait:

**Logged actions:**
- `ban.issued` - When user is banned
- `ban.lifted` - When user is unbanned
- `ban.appeal.approved` - Appeal approved
- `ban.appeal.rejected` - Appeal rejected
- `ban.hardware` - Hardware ID ban
- `ban.ip` - IP address ban
- `ban.import` - Bulk import

**Metadata includes:**
- User ID
- Admin ID
- Ban reason
- Ban type
- Duration (if temp)
- IP address (for context)

View audit logs at `/admin/audit-log` with filter by action type.

## Usage Examples

### Ban a user for 24 hours
```php
$banService = app(BanService::class);
$banService->banUser($user, $admin, [
    'reason' => 'Griefing teammates',
    'duration_hours' => 24,
    'ban_type' => 'temporary',
    'notes' => 'First offense, warned before ban',
]);
```

### Permanent ban
```php
$banService->banUser($user, $admin, [
    'reason' => 'Repeated cheating',
    'ban_type' => 'permanent',
]);
```

### Hardware ban
```php
$banService->banByHardwareId('550e8400-e29b-41d4-a716-446655440000', $admin, [
    'reason' => 'Ban evasion via alt account',
]);
```

### Check if user is banned
```php
if ($user->is_banned) {
    if ($user->banned_until && $user->banned_until > now()) {
        $remaining = $user->banned_until->diffForHumans();
        echo "Temporarily banned until {$user->banned_until} ({$remaining})";
    } else {
        echo "Permanently banned";
    }
}
```

## API Integration

Ban status is checked on:
- Login (prevents banned users from logging in)
- All authenticated routes (middleware check)
- Game server connections (via player_uuid lookup)

**Game server ban check:**
```php
// In StatsController or similar
$user = User::where('player_uuid', $playerUuid)->first();
if ($user && $user->is_banned) {
    return response()->json([
        'error' => 'Player is banned',
        'reason' => $user->ban_reason,
        'until' => $user->banned_until,
    ], 403);
}
```

## Future Enhancements

Potential additions:
- Discord webhook notifications for bans
- Email notifications to banned users
- Ban appeal templates
- Automated ban suggestions based on repeat offenses
- Integration with Raven Anti-Cheat for auto-bans
- Ban statistics dashboard (avg ban duration, most common reasons, etc.)
