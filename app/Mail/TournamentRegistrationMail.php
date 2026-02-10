<?php

namespace App\Mail;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TournamentRegistrationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public TournamentRegistration $registration;

    public Tournament $tournament;

    /**
     * Create a new message instance.
     */
    public function __construct(TournamentRegistration $registration)
    {
        $this->registration = $registration;
        $this->tournament = $registration->tournament;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Registration Confirmed: {$this->tournament->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tournament-registration',
            with: [
                'tournamentName' => $this->tournament->name,
                'teamName' => $this->registration->team->name,
                'format' => ucfirst(str_replace('_', ' ', $this->tournament->format)),
                'maxTeams' => $this->tournament->max_teams,
                'currentTeams' => $this->tournament->registrations()->where('status', 'approved')->count(),
                'startDate' => $this->tournament->start_date,
                'registrationStatus' => $this->registration->status,
                'tournamentUrl' => route('tournaments.show', $this->tournament->id),
                'rulesUrl' => route('tournaments.show', [$this->tournament->id, '#rules']),
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
