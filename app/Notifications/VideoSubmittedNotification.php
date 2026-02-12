<?php

namespace App\Notifications;

use App\Models\HighlightClip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VideoSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public HighlightClip $clip
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'video_submitted',
            'video_id' => $this->clip->id,
            'video_title' => $this->clip->title,
            'platform' => $this->clip->platform,
            'message' => "Your video \"{$this->clip->title}\" has been submitted and is awaiting moderator approval.",
            'url' => route('clips.show', $this->clip),
            'icon' => 'clock',
            'color' => 'yellow',
        ];
    }
}
