# Email Notifications

## Overview

The Reforger Community platform sends automated email notifications to keep users engaged and informed about important events.

## Available Notifications

### 1. Team Invitations

**Mail Class:** `TeamInvitationMail`
**Trigger:** When a team captain invites a user to join their team
**Recipients:** Invited user
**Template:** `emails.team-invitation`

**Content:**
- Team name and tag
- Captain name
- Accept/Decline action buttons
- Expiration date (7 days)

**Integration Point:**
```php
use App\Mail\TeamInvitationMail;
use Illuminate\Support\Facades\Mail;

// When creating invitation
$invitation = TeamInvitation::create([...]);
Mail::to($invitation->user->email)->send(new TeamInvitationMail($invitation));
```

### 2. Match Reminders

**Mail Class:** `MatchReminderMail`
**Trigger:** Automated via cron job (24h and 1h before match)
**Recipients:** All team members of both teams
**Template:** `emails.match-reminder`

**Content:**
- Tournament name
- Match details (round, match number)
- Team names
- Scheduled time
- Check-in action button
- Match details link

**Scheduling:**
```bash
# Add to routes/console.php or scheduler
Schedule::command('matches:send-reminders')->hourly();
```

**Manual Trigger:**
```bash
php artisan matches:send-reminders
```

### 3. Achievement Unlocked

**Mail Class:** `AchievementUnlockedMail`
**Trigger:** When a user unlocks a new achievement
**Recipients:** User who unlocked the achievement
**Template:** `emails.achievement-unlocked`

**Content:**
- Achievement name and icon
- Description
- Rarity percentage
- Special badge for rare achievements (<5%)
- Links to profile and achievements page

**Integration Point:**
```php
use App\Mail\AchievementUnlockedMail;

// When achievement is unlocked
$user->achievements()->attach($achievement->id);
Mail::to($user->email)->send(new AchievementUnlockedMail($achievement, $user));
```

### 4. Tournament Registration

**Mail Class:** `TournamentRegistrationMail`
**Trigger:** When a team registers for a tournament
**Recipients:** Team captain
**Template:** `emails.tournament-registration`

**Content:**
- Tournament name and format
- Team name
- Start date
- Registration status (pending/approved)
- Current teams registered
- Tournament and rules links

**Integration Point:**
```php
use App\Mail\TournamentRegistrationMail;

// When registration is created or approved
$registration = TournamentRegistration::create([...]);
Mail::to($registration->team->captain->email)
    ->send(new TournamentRegistrationMail($registration));
```

## User Preferences

All notification emails respect user preferences stored in `users.notification_preferences` (JSON column):

```json
{
  "team_invitations": true,
  "match_reminders": true,
  "achievement_unlocks": false,
  "tournament_registrations": true
}
```

**Default:** All notifications are enabled (`true`) unless explicitly disabled.

**Checking Preferences:**
```php
$preferences = $user->notification_preferences ?? [];
if ($preferences['match_reminders'] ?? true) {
    // Send email
}
```

## Queue Configuration

All mail classes implement `ShouldQueue` for async processing:

```php
class TeamInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    // ...
}
```

**Queue Setup:**
```bash
# Start queue worker
php artisan queue:work

# Or use Supervisor in production
[program:reforger-queue]
command=php /path/to/artisan queue:work --sleep=3 --tries=3
```

## Email Templates

All templates use Laravel's markdown mail components for consistent styling.

**Location:** `resources/views/emails/`

**Components Used:**
- `<x-mail::message>` - Main wrapper
- `<x-mail::button>` - Action buttons
- `<x-mail::panel>` - Highlighted content boxes

**Customizing:**
```bash
# Publish mail templates
php artisan vendor:publish --tag=laravel-mail

# Edit in resources/views/vendor/mail/
```

## Environment Configuration

**Required `.env` Settings:**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io  # Or your SMTP provider
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@armabattles.se
MAIL_FROM_NAME="${APP_NAME}"
```

**Popular Providers:**
- **Production:** Postmark, SendGrid, Mailgun, Amazon SES
- **Testing:** Mailtrap, MailHog
- **Development:** `log` driver (writes to `storage/logs/laravel.log`)

**Testing Setup:**
```env
MAIL_MAILER=log
```

## Testing Emails

### 1. Preview in Browser

```php
// Add a route for previewing
Route::get('/mail-preview', function () {
    $invitation = TeamInvitation::first();
    return new TeamInvitationMail($invitation);
});
```

### 2. Send Test Email

```bash
php artisan tinker

> use App\Mail\TeamInvitationMail;
> use App\Models\TeamInvitation;
> Mail::to('test@example.com')->send(new TeamInvitationMail(TeamInvitation::first()));
```

### 3. Automated Tests

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\TeamInvitationMail;

public function test_team_invitation_email_is_sent()
{
    Mail::fake();

    // Trigger invitation
    $captain->invite($user);

    Mail::assertSent(TeamInvitationMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
}
```

## Scheduled Commands

Add to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('matches:send-reminders')->hourly();
```

**Available Commands:**
- `php artisan matches:send-reminders` - Send match reminder emails

## Monitoring

### Email Logs

All sent emails are logged when using the `log` driver:
```bash
tail -f storage/logs/laravel.log | grep "Mailable"
```

### Queue Monitoring

```bash
# Check queue status
php artisan queue:work --once

# Failed jobs
php artisan queue:failed

# Retry failed
php artisan queue:retry all
```

### Success Rate

Track email delivery in production:
- Use provider webhooks (Postmark, SendGrid)
- Monitor bounce rates
- Track open rates (optional, requires tracking pixels)

## Best Practices

### 1. Always Queue Emails

```php
// ✅ Good: Queued (fast response)
Mail::to($user)->send(new TeamInvitationMail($invitation));

// ❌ Bad: Synchronous (slow response)
Mail::to($user)->sendNow(new TeamInvitationMail($invitation));
```

### 2. Respect User Preferences

```php
// Check before sending
if ($user->wantsNotification('match_reminders')) {
    Mail::to($user)->send(...);
}
```

### 3. Use Meaningful Subjects

```php
// ✅ Good: Specific and actionable
subject: "Match Reminder: Winter Cup in 1 hour"

// ❌ Bad: Vague
subject: "Notification"
```

### 4. Include Unsubscribe Links

Add to email templates:
```blade
<x-mail::subcopy>
Don't want these emails? [Manage your notification preferences]({{ route('profile.settings') }}).
</x-mail::subcopy>
```

### 5. Handle Failures Gracefully

```php
use Illuminate\Support\Facades\Mail;

try {
    Mail::to($user)->send(new SomeEmail());
} catch (\Exception $e) {
    \Log::error('Failed to send email: ' . $e->getMessage());
    // Don't break the user flow
}
```

## Future Enhancements

- [ ] **Weekly digest emails** - Summary of stats, achievements, upcoming matches
- [ ] **Admin notifications** - Server issues, reports, suspicious activity
- [ ] **Social notifications** - Friend requests, mentions in chat
- [ ] **Email verification** - Verify email addresses on registration
- [ ] **Password reset emails** - Use Laravel's built-in password reset
- [ ] **Notification batching** - Group multiple notifications into single email
- [ ] **HTML + Plain Text** - Auto-generate plain text version for accessibility
- [ ] **Email analytics** - Track opens, clicks, conversions

## Troubleshooting

### Emails Not Sending

1. Check queue is running: `php artisan queue:work`
2. Check `.env` mail configuration
3. Check `storage/logs/laravel.log` for errors
4. Test SMTP connection: `php artisan tinker` → `Mail::raw('test', fn($msg) => $msg->to('test@example.com'))`

### Emails Going to Spam

1. Configure SPF, DKIM, DMARC records
2. Use reputable email provider
3. Warm up new domain gradually
4. Don't include spammy words in subject
5. Include unsubscribe link

### Queue Processing Slow

1. Increase queue workers
2. Use Redis instead of database queue
3. Optimize mail sending (batch operations)
4. Use dedicated queue for emails: `->onQueue('emails')`

## Related Files

- **Mail Classes:** `app/Mail/`
- **Templates:** `resources/views/emails/`
- **Commands:** `app/Console/Commands/SendMatchReminders.php`
- **Scheduler:** `routes/console.php`
- **Config:** `config/mail.php`

## See Also

- [Laravel Mail Documentation](https://laravel.com/docs/mail)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Markdown Mail](https://laravel.com/docs/mail#markdown-mailables)
