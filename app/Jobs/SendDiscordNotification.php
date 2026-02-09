<?php

namespace App\Jobs;

use App\Services\DiscordWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDiscordNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 15;

    public function __construct(
        protected string $title,
        protected string $description,
        protected int $color = 0x22c55e,
        protected array $fields = [],
    ) {}

    public function handle(DiscordWebhookService $discord): void
    {
        $discord->sendEmbed($this->title, $this->description, $this->color, $this->fields);
    }
}
