# Recruitment System

Player recruitment board for finding groups and team members.

## Overview

The recruitment system allows players to create "Looking for Group" (LFG) or "Looking for Members" (LFM) listings to find teammates. Players can specify roles, experience levels, playstyles, and availability.

## Database Tables

### `recruitment_listings`
Public recruitment posts.

**Columns:**
- `id` - Primary key
- `user_id` - Listing creator (FK to users)
- `player_uuid` - Creator's game UUID
- `type` - Listing type (lfg|lfm)
- `title` - Listing title
- `description` - Full description
- `roles` - JSON array of sought roles
- `playstyle` - Playstyle preference (casual|tactical|competitive|milsim)
- `experience_level` - Required experience (beginner|intermediate|advanced|veteran)
- `region` - Geographic region (na|eu|asia|oce|global)
- `availability` - When they play (weekdays|weekends|anytime)
- `discord_contact` - Discord username (optional)
- `is_active` - Currently active (boolean)
- `expires_at` - Auto-expiration date
- `created_at`, `updated_at`

**Indexes:**
- `user_id`, `type`, `is_active`, `playstyle`, `region`, `expires_at`

### `player_roles`
Available roles for recruitment.

**Columns:**
- `id` - Primary key
- `name` - Role name (Infantry, Medic, Engineer, etc.)
- `icon` - Icon class or SVG
- `description` - Role description
- `category` - Role category (combat|support|leadership|specialist)
- `is_active` - Available for selection (boolean)
- `sort_order` - Display order

**Seeded roles:**
- Infantry
- Medic
- Engineer
- Driver
- Anti-Tank (AT)
- Sniper
- Support (MG/Ammo)
- Squad Leader
- Platoon Leader
- Radio Operator
- Pilot
- Tank Crew

### User model additions
New fields on `users` table:

- `looking_for_team` (boolean, default false) - Quick LFG flag
- `preferred_roles` (JSON, nullable) - Array of preferred role IDs
- `playstyle` (string, nullable) - Preferred playstyle
- `experience_level` (string, nullable) - Self-assessed experience

## Models

### `RecruitmentListing`
**Location:** `app/Models/RecruitmentListing.php`

**Constants:**
```php
const TYPE_LFG = 'lfg';  // Looking for group
const TYPE_LFM = 'lfm';  // Looking for members

const PLAYSTYLE_CASUAL = 'casual';
const PLAYSTYLE_TACTICAL = 'tactical';
const PLAYSTYLE_COMPETITIVE = 'competitive';
const PLAYSTYLE_MILSIM = 'milsim';

const EXPERIENCE_BEGINNER = 'beginner';
const EXPERIENCE_INTERMEDIATE = 'intermediate';
const EXPERIENCE_ADVANCED = 'advanced';
const EXPERIENCE_VETERAN = 'veteran';

const REGION_NA = 'na';
const REGION_EU = 'eu';
const REGION_ASIA = 'asia';
const REGION_OCE = 'oce';
const REGION_GLOBAL = 'global';

const AVAILABILITY_WEEKDAYS = 'weekdays';
const AVAILABILITY_WEEKENDS = 'weekends';
const AVAILABILITY_ANYTIME = 'anytime';
```

**Relationships:**
- `belongsTo(User::class)` - Creator

**Casts:**
- `roles` → array
- `is_active` → boolean
- `expires_at` → datetime

**Scopes:**
- `scopeActive($query)` - is_active = true AND expires_at > now()
- `scopeLfg($query)` - Type = lfg
- `scopeLfm($query)` - Type = lfm
- `scopeByRegion($query, string $region)`
- `scopeByPlaystyle($query, string $playstyle)`

**Methods:**
- `isExpired(): bool`
- `deactivate(): void`

### `PlayerRole`
**Location:** `app/Models/PlayerRole.php`

**Relationships:**
- None (reference data)

**Scopes:**
- `scopeActive($query)`
- `scopeByCategory($query, string $category)`

## Controllers

### `RecruitmentController`
**Location:** `app/Http/Controllers/RecruitmentController.php`

**Routes prefix:** `/recruitment`

**Methods:**

| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| `index()` | GET `/` | No | Public recruitment board |
| `create()` | GET `/create` | Yes | Create listing form |
| `store()` | POST `/` | Yes | Save new listing |
| `myListing()` | GET `/my-listing` | Yes | User's active listing |
| `deactivate()` | POST `/deactivate` | Yes | Deactivate own listing |

**Validation rules for store():**
```php
[
    'type' => 'required|in:lfg,lfm',
    'title' => 'required|string|max:100',
    'description' => 'required|string|max:1000',
    'roles' => 'required|array|min:1',
    'roles.*' => 'exists:player_roles,id',
    'playstyle' => 'required|in:casual,tactical,competitive,milsim',
    'experience_level' => 'required|in:beginner,intermediate,advanced,veteran',
    'region' => 'required|in:na,eu,asia,oce,global',
    'availability' => 'required|in:weekdays,weekends,anytime',
    'discord_contact' => 'nullable|string|max:100',
]
```

**Business rules:**
- User can only have ONE active listing at a time
- Listings expire after 30 days (configurable via site setting)
- Auto-deactivated if user joins a team (optional)

## Views

**Location:** `resources/views/recruitment/`

### `index.blade.php`
Public recruitment board with:
- Filter sidebar (type, region, playstyle, roles)
- Search by title/description
- Card grid layout
- Pagination

**Card displays:**
- User avatar and name
- Listing type badge (LFG/LFM)
- Title and description (truncated)
- Roles with icons
- Playstyle, experience, region badges
- Contact button (shows Discord if provided)

### `create.blade.php`
Listing creation form with:
- Type selector (LFG/LFM)
- Title input
- Description textarea (1000 char max)
- Role multi-select checkboxes (grouped by category)
- Playstyle radio buttons
- Experience level select
- Region select
- Availability checkboxes
- Optional Discord username

**Alpine.js features:**
- Character counter for description
- Role category collapsing
- Preview panel

## Site Settings

**Recruitment-related settings:**

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `recruitment_listing_duration_days` | integer | 30 | Days before auto-expiration |
| `recruitment_max_roles` | integer | 3 | Max roles per listing |
| `recruitment_cooldown_hours` | integer | 24 | Cooldown between listings |

## Scheduled Tasks

### Auto-expiration
No dedicated command - handled by `scopeActive()` which filters `expires_at > now()`.

**Alternative:** Add to `routes/console.php`:
```php
Schedule::command('recruitment:cleanup')
    ->daily()
    ->description('Deactivate expired recruitment listings');
```

Command would:
```php
RecruitmentListing::where('is_active', true)
    ->where('expires_at', '<', now())
    ->update(['is_active' => false]);
```

## Integration with Teams

**When user joins a team:**
Option to auto-deactivate LFG listings (user found a team).

**Team recruitment:**
Teams can create LFM listings on behalf of the team (future enhancement).

## Usage Examples

### Create LFG listing
```php
$listing = RecruitmentListing::create([
    'user_id' => auth()->id(),
    'player_uuid' => auth()->user()->player_uuid,
    'type' => RecruitmentListing::TYPE_LFG,
    'title' => 'Experienced medic looking for tactical squad',
    'description' => 'I have 200+ hours as medic, looking for...',
    'roles' => [2, 8], // Medic, Squad Leader
    'playstyle' => RecruitmentListing::PLAYSTYLE_TACTICAL,
    'experience_level' => RecruitmentListing::EXPERIENCE_ADVANCED,
    'region' => RecruitmentListing::REGION_EU,
    'availability' => RecruitmentListing::AVAILABILITY_WEEKENDS,
    'discord_contact' => 'username#1234',
    'is_active' => true,
    'expires_at' => now()->addDays(30),
]);
```

### Query active LFM listings for EU region
```php
$listings = RecruitmentListing::active()
    ->lfm()
    ->byRegion('eu')
    ->with('user')
    ->latest()
    ->paginate(12);
```

### Check if user has active listing
```php
$activeListing = RecruitmentListing::active()
    ->where('user_id', auth()->id())
    ->first();

if ($activeListing) {
    // User already has an active listing
}
```

## Role Seeder

**Database seeder:** `PlayerRoleSeeder`

Seeds the `player_roles` table with predefined roles:

```php
PlayerRole::create([
    'name' => 'Infantry',
    'icon' => 'fa-soldier',
    'description' => 'Standard rifleman or assault role',
    'category' => 'combat',
    'is_active' => true,
    'sort_order' => 1,
]);

// ... etc for all roles
```

Run with: `php artisan db:seed --class=PlayerRoleSeeder`

## API Integration (Future)

Potential API endpoints for external tools:

- `GET /api/v1/recruitment/listings` - Public listings
- `GET /api/v1/recruitment/roles` - Available roles
- `POST /api/v1/recruitment/contact` - Contact listing creator

## Future Enhancements

- Team-based LFM listings (not just individual)
- Application system (apply to LFM listings)
- Messaging between users (DM system)
- Saved searches / notification alerts
- Role preferences on user profiles
- Integration with Discord roles
- "Bump" listing to top (once per day)
- Featured listings (premium)
- Statistics (roles in demand, regions, etc.)
