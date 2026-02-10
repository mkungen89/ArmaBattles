<?php

namespace App\Mail;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AchievementUnlockedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Achievement $achievement;

    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Achievement $achievement, User $user)
    {
        $this->achievement = $achievement;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🏆 Achievement Unlocked: {$this->achievement->name}!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Calculate rarity percentage
        $totalUsers = User::count();
        $usersWithAchievement = $this->achievement->users()->count();
        $rarityPercentage = $totalUsers > 0 ? round(($usersWithAchievement / $totalUsers) * 100, 1) : 0;

        return new Content(
            markdown: 'emails.achievement-unlocked',
            with: [
                'userName' => $this->user->name,
                'achievementName' => $this->achievement->name,
                'achievementDescription' => $this->achievement->description,
                'achievementIcon' => $this->achievement->icon,
                'rarityPercentage' => $rarityPercentage,
                'isRare' => $rarityPercentage < 5, // Rare if <5% have it
                'profileUrl' => route('profile'),
                'achievementsUrl' => route('profile').'#achievements',
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
