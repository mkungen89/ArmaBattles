<?php

namespace App\Console\Commands;

use App\Mail\MatchReminderMail;
use App\Models\TournamentMatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMatchReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matches:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for upcoming matches (24h and 1h before)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending match reminders...');

        $sent = 0;

        // Send 24-hour reminders
        $matches24h = TournamentMatch::where('status', 'pending')
            ->whereBetween('scheduled_at', [
                now()->addHours(23)->addMinutes(50),
                now()->addHours(24)->addMinutes(10),
            ])
            ->with(['team1.members.user', 'team2.members.user', 'tournament'])
            ->get();

        foreach ($matches24h as $match) {
            $this->sendRemindersForMatch($match, '24 hours');
            $sent++;
        }

        // Send 1-hour reminders
        $matches1h = TournamentMatch::where('status', 'pending')
            ->whereBetween('scheduled_at', [
                now()->addMinutes(50),
                now()->addMinutes(70),
            ])
            ->with(['team1.members.user', 'team2.members.user', 'tournament'])
            ->get();

        foreach ($matches1h as $match) {
            $this->sendRemindersForMatch($match, '1 hour');
            $sent++;
        }

        $this->info("Sent reminders for {$sent} matches.");

        return Command::SUCCESS;
    }

    /**
     * Send reminders to all team members for a match
     */
    protected function sendRemindersForMatch(TournamentMatch $match, string $timeUntil): void
    {
        $recipients = collect();

        // Add team 1 members
        if ($match->team1) {
            foreach ($match->team1->activeMembers as $member) {
                if ($member->user && $member->user->email) {
                    $recipients->push($member->user);
                }
            }
        }

        // Add team 2 members
        if ($match->team2) {
            foreach ($match->team2->activeMembers as $member) {
                if ($member->user && $member->user->email) {
                    $recipients->push($member->user);
                }
            }
        }

        // Send emails
        $recipients->unique('id')->each(function ($user) use ($match, $timeUntil) {
            // Check user notification preferences
            $preferences = $user->notification_preferences ?? [];
            if (($preferences['match_reminders'] ?? true)) {
                Mail::to($user->email)->send(new MatchReminderMail($match, $timeUntil));
                $this->line("  → Sent to {$user->name} ({$user->email})");
            }
        });
    }
}
