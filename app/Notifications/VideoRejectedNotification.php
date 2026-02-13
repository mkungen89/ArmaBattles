<?php

namespace App\Notifications;

use App\Models\HighlightClip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VideoRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public HighlightClip $clip,
        public ?string $reason = null
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
        $message = "Your video \"{$this->clip->title}\" was not approved for publication.";

        if ($this->reason) {
            $message .= " Reason: {$this->reason}";
        }

        return [
            'type' => 'video_rejected',
            'category' => 'video',
            'video_id' => $this->clip->id,
            'video_title' => $this->clip->title,
            'platform' => $this->clip->platform,
            'reason' => $this->reason,
            'message' => $message,
            'url' => route('clips.show', $this->clip),
            'icon' => 'x-circle',
            'color' => 'red',
        ];
    }
}
