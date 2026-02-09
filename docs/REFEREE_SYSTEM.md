# Referee & Observer System

## Overview

The Reforger Community platform includes a comprehensive referee system for managing tournament matches, submitting official match reports, and maintaining fair play through audit logging.

## User Roles

### Referee (`referee`)

**Permissions:**
- Access referee dashboard
- Submit match reports
- View all tournament matches
- Report incidents and rule violations
- Cannot approve own reports

**Use Case:** Official tournament referees who observe matches and report results.

### Observer (`observer`)

**Permissions:**
- View live tournament matches
- Access to observer-specific features (spectator mode)
- Cannot submit reports

**Use Case:** Tournament observers, spectators, and staff who monitor matches without official reporting duties.

### Caster (`caster`)

**Permissions:**
- Access to casting tools
- Can add live commentary
- View tournament schedules and match details

**Use Case:** Tournament casters and commentators providing live coverage.

## Role Hierarchy

All roles have hierarchical permissions:

- **Referee**: Admin, Moderator, Referee (`isReferee()`)
- **Observer**: Admin, Moderator, Referee, Observer (`isObserver()`)
- **Caster**: Admin, Moderator, Caster (`isCaster()`)
- **Tournament Management**: Admin, Moderator, Referee (`canManageTournaments()`)

This means admins and moderators automatically have all referee/observer/caster permissions.

## Database Schema

### match_reports Table

```sql
CREATE TABLE match_reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    match_id BIGINT NOT NULL,
    referee_id BIGINT NOT NULL,
    winning_team_id BIGINT NULL,
    team1_score INT DEFAULT 0,
    team2_score INT DEFAULT 0,
    notes TEXT NULL,
    incidents JSON NULL,
    status VARCHAR(50) DEFAULT 'submitted',
    reported_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (match_id) REFERENCES tournament_matches(id) ON DELETE CASCADE,
    FOREIGN KEY (referee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (winning_team_id) REFERENCES teams(id) ON DELETE SET NULL,

    INDEX idx_match_id (match_id),
    INDEX idx_referee_id (referee_id),
    INDEX idx_status (status)
);
```

### Report Status Workflow

```
submitted → approved   (admin approves)
         ↓
      disputed        (requires admin review)
```

## Assigning Roles

### Via Database (Manual)

```sql
-- Make user a referee
UPDATE users SET role = 'referee' WHERE id = 123;

-- Make user an observer
UPDATE users SET role = 'observer' WHERE id = 456;

-- Make user a caster
UPDATE users SET role = 'caster' WHERE id = 789;
```

### Via Tinker

```bash
php artisan tinker

> $user = User::find(123);
> $user->update(['role' => 'referee']);
>
> # Verify role
> $user->isReferee(); // true
```

### Future: Admin Panel UI

_(To be implemented)_

A dedicated admin panel section for managing user roles, with filters, search, and bulk assignment.

## Referee Dashboard

**Route:** `/referee`

**Access:** `referee` middleware (admin, moderator, referee)

**Sections:**

### 1. Statistics Cards

- **Active Tournaments** - Count of ongoing tournaments
- **Matches Needing Reports** - Scheduled/in-progress matches without reports
- **My Reports** - Total reports submitted by logged-in referee

### 2. Matches Needing Reports

Lists all matches that:
- Status: `scheduled` or `in_progress`
- Have no `submitted` or `approved` reports

Each match shows:
- Tournament name
- Round label (Final, Semi-Final, etc.)
- Team names
- Scheduled time
- Quick actions (View Match, Submit Report)

### 3. My Recent Reports

Last 10 reports submitted by the referee:
- Match details
- Score
- Winner
- Report status (badge: submitted/approved/disputed)
- Time since reported

### 4. Disputed Matches (Alert)

Highlighted section showing matches with disputed reports:
- Red alert banner
- Match details
- Link to view full report

## Submitting Match Reports

### Access Report Form

1. Navigate to Referee Dashboard
2. Find match in "Matches Needing Reports"
3. Click "Submit Report"

**Route:** `/referee/match/{match}/report`

### Report Form Fields

#### Required Fields

- **Winning Team** (dropdown) - Select Team 1 or Team 2
- **Team 1 Score** (number) - Goals/points scored by Team 1
- **Team 2 Score** (number) - Goals/points scored by Team 2

#### Optional Fields

- **Match Notes** (textarea) - General observations, notable plays, MVP mentions
- **Incidents** (dynamic list) - Report rule violations or issues

### Incident Reporting

Click "Add Incident" to report violations or issues.

**Incident Fields:**

- **Type** (required) - Dropdown options:
  - Rule Violation
  - Unsportsmanlike Conduct
  - Technical Issue
  - Cheating Allegation
  - Other
- **Description** (required) - Detailed explanation of what happened
- **Player** (optional) - Name of involved player
- **Timestamp** (optional) - When it occurred (e.g., "Round 2, 5:30 remaining")

**Example Incident:**

```
Type: Rule Violation
Player: PlayerName123
Description: Player used restricted weapon class during Round 3
Timestamp: Round 3, 12:45 remaining
```

### Submitting the Report

1. Fill all required fields
2. Add incidents if applicable
3. Click "Submit Report"

**On Success:**
- Report saved with status `submitted`
- Match status changed to `completed`
- Match winner_id, team1_score, team2_score updated
- Referee audit log entry created
- Redirect to dashboard with success message

## Viewing Reports

**Route:** `/referee/report/{report}`

**Access:** All referees can view any report

**Report Details:**

### Match Result Display

Large score display:
- Team names
- Scores (winner in green, loser in gray)
- Winner announcement

### Report Metadata

- Reported by (referee name)
- Reported at (timestamp)
- Match notes (if any)

### Incidents Section

Yellow-highlighted section listing all reported incidents:
- Incident number
- Type badge
- Player name (if provided)
- Description
- Timestamp (if provided)

### Admin Actions (Admin Only)

If report status is `submitted` and user is admin:

**Approve Report:**
- Changes status to `approved`
- Finalizes match result
- Logs admin action

**Dispute Report:**
- Shows dispute form (textarea)
- Requires dispute reason
- Changes report status to `disputed`
- Changes match status to `disputed`
- Adds dispute to incidents list
- Logs admin action

## Audit Logging

All referee actions are logged using the `LogsAdminActions` trait.

### Logged Events

| Action | Target | Metadata |
|--------|--------|----------|
| `referee.match-reported` | TournamentMatch | report_id, tournament, winner, score, incidents_count |
| `referee.report-approved` | MatchReport | match_id, original_referee |
| `referee.report-disputed` | MatchReport | match_id, reason |

### Viewing Audit Logs

**Route:** `/admin/audit-log`

**Filter By:**
- Action type
- User (referee)
- Date range
- Search (tournament name, match ID)

**Example Query:**

```php
AdminAuditLog::where('action', 'LIKE', 'referee.%')
    ->whereDate('created_at', '>=', now()->subDays(7))
    ->with('user')
    ->orderBy('created_at', 'desc')
    ->get();
```

## API Access (Future)

### Get Match Reports

```http
GET /api/tournaments/{tournamentId}/matches/{matchId}/reports
Authorization: Bearer {token}
```

**Response:**

```json
{
  "data": [
    {
      "id": 123,
      "match_id": 456,
      "referee": {
        "id": 789,
        "name": "John Referee",
        "role": "referee"
      },
      "winning_team_id": 10,
      "team1_score": 5,
      "team2_score": 3,
      "status": "approved",
      "incidents": [
        {
          "type": "rule_violation",
          "description": "Player used restricted weapon",
          "player_name": "Player123",
          "timestamp": "Round 2, 5:30"
        }
      ],
      "notes": "Great match, close score throughout",
      "reported_at": "2026-02-08T16:30:00Z"
    }
  ]
}
```

## Navigation Access

Referees have access to the Referee Dashboard from:

1. **Top Navigation Bar** - "Referee" link (blue badge)
2. **Mobile Menu** - "Referee" item in hamburger menu
3. **User Dropdown** - "Referee Dashboard" with checkmark icon

**Visibility:** Only shown to users with `isReferee()` returning true.

## Permissions & Security

### Middleware

```php
// routes/web.php
Route::prefix('referee')->middleware(['auth', 'referee'])->group(function () {
    Route::get('/', [RefereeController::class, 'index']);
    Route::post('/match/{match}/report', [RefereeController::class, 'submitReport']);
    Route::post('/report/{report}/approve', [RefereeController::class, 'approveReport']);
    Route::post('/report/{report}/dispute', [RefereeController::class, 'disputeReport']);
});
```

### Authorization Checks

```php
// In RefereeMiddleware
if (! auth()->user()->isReferee()) {
    abort(403, 'Access denied. Referee privileges required.');
}

// In RefereeController (approve action)
if (! auth()->user()->isAdmin()) {
    abort(403, 'Only administrators can approve reports');
}
```

### Preventing Self-Approval

Referees cannot approve their own reports. The approve action is admin-only.

## Integration with Tournament System

### Match Status Flow

```
pending → scheduled → in_progress → completed
                                        ↓
                                   (referee reports)
                                        ↓
                                   reported/approved
```

### Automatic Updates

When a report is submitted:

```php
// Match is automatically updated
$match->update([
    'winner_id' => $validated['winning_team_id'],
    'team1_score' => $validated['team1_score'],
    'team2_score' => $validated['team2_score'],
    'status' => 'completed',
    'completed_at' => now(),
]);
```

### Bracket Progression

After match completion and report approval:
1. Winner advances to next round (if applicable)
2. Loser moves to losers bracket (double elimination)
3. Tournament standings updated
4. Next matches become available for scheduling

## Best Practices

### For Referees

1. **Report Immediately** - Submit reports as soon as the match ends while details are fresh
2. **Be Detailed** - Include specific timestamps and player names in incident reports
3. **Be Objective** - Stick to facts, avoid subjective opinions
4. **Document Everything** - Use the notes field for context that doesn't fit elsewhere
5. **Verify Scores** - Double-check scores with both teams before submitting

### For Admins

1. **Review Quickly** - Approve or dispute reports within 24 hours
2. **Investigate Disputes** - Contact both teams and referee before making decisions
3. **Provide Feedback** - Use dispute reasons to explain why a report is questioned
4. **Monitor Patterns** - Track repeated incidents from specific players/teams
5. **Update Rules** - If incidents reveal rule ambiguities, clarify tournament rules

## Troubleshooting

### "Access denied. Referee privileges required."

**Solution:** User role is not set to `referee`. Update via database or tinker:

```bash
php artisan tinker
> User::find(123)->update(['role' => 'referee']);
```

### Match Not Showing in "Matches Needing Reports"

**Possible Causes:**
1. Match status is not `scheduled` or `in_progress`
2. Match already has a submitted/approved report
3. Match scheduled_at is too far in future

**Solution:** Check match status:

```php
$match = TournamentMatch::find(456);
echo $match->status; // Should be 'scheduled' or 'in_progress'
echo $match->reports()->whereIn('status', ['submitted', 'approved'])->count(); // Should be 0
```

### Report Not Saving

**Check Validation Errors:**

```php
// In controller
$validated = $request->validate([
    'winning_team_id' => 'required|exists:teams,id',
    'team1_score' => 'required|integer|min:0',
    'team2_score' => 'required|integer|min:0',
]);
```

**Common Issues:**
- `winning_team_id` must be valid team ID
- Scores must be non-negative integers
- Incident descriptions must be strings

### Disputed Match Not Appearing

**Solution:** Check report status:

```php
$report = MatchReport::find(123);
echo $report->status; // Should be 'disputed'

// Check match status
echo $report->match->status; // Should also be 'disputed'
```

## Related Files

- **Controller:** `app/Http/Controllers/RefereeController.php`
- **Model:** `app/Models/MatchReport.php`
- **Middleware:** `app/Http/Middleware/RefereeMiddleware.php`
- **Migrations:**
  - `database/migrations/2026_02_08_162440_add_referee_observer_caster_roles.php`
  - `database/migrations/2026_02_08_162545_create_match_reports_table.php`
- **Views:**
  - `resources/views/referee/dashboard.blade.php`
  - `resources/views/referee/report-match.blade.php`
  - `resources/views/referee/view-report.blade.php`
- **Routes:** `routes/web.php` (line 379-385)
- **Trait:** `app/Traits/LogsAdminActions.php`

## Future Enhancements

- [ ] **Referee Assignment System** - Assign specific referees to specific matches
- [ ] **Live Match Tracking** - Real-time score updates during matches
- [ ] **Referee Performance Metrics** - Track report accuracy, response times
- [ ] **Multi-Referee Reports** - Allow multiple referees per match
- [ ] **Report Templates** - Pre-defined report formats for different game modes
- [ ] **Photo/Video Evidence** - Upload screenshots/clips to support incidents
- [ ] **Automated Notifications** - Email/SMS reminders for upcoming referee duties
- [ ] **Referee Scheduling** - Calendar integration for referee availability
- [ ] **Report History** - Track changes/edits to reports over time
- [ ] **Referee Training Mode** - Practice environment for new referees

## See Also

- [Tournament System Documentation](TOURNAMENT_SYSTEM.md)
- [Audit Logging Guide](AUDIT_LOGGING.md)
- [User Roles & Permissions](USER_ROLES.md)
