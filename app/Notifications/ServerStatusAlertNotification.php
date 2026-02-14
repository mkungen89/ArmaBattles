<?php

namespace App\Notifications;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ServerStatusAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Server $server,
        public string $status, // 'offline' or 'online'
        public ?string $previousStatus = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->status === 'offline'
            ? "Server '{$this->server->name}' went OFFLINE"
            : "Server '{$this->server->name}' is back ONLINE";

        return [
            'type' => 'server_status',
            'server_id' => $this->server->id,
            'server_name' => $this->server->name,
            'status' => $this->status,
            'previous_status' => $this->previousStatus,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];
    }
}
