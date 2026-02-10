<?php

namespace App\Mail;

use App\Models\TournamentMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public TournamentMatch $match;

    public string $timeUntil; // "1 hour" or "24 hours"

    /**
     * Create a new message instance.
     */
    public function __construct(TournamentMatch $match, string $timeUntil)
    {
        $this->match = $match;
        $this->timeUntil = $timeUntil;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Match Reminder: {$this->match->tournament->name} in {$this->timeUntil}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.match-reminder',
            with: [
                'tournamentName' => $this->match->tournament->name,
                'matchNumber' => $this->match->match_number,
                'round' => $this->match->round,
                'team1' => $this->match->team1?->name ?? 'TBD',
                'team2' => $this->match->team2?->name ?? 'TBD',
                'scheduledAt' => $this->match->scheduled_at,
                'timeUntil' => $this->timeUntil,
                'matchUrl' => route('matches.show', $this->match->id),
                'checkInUrl' => route('matches.check-in', $this->match->id),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
