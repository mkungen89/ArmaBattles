<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordWebhookService
{
    public function send(string $content, array $embeds = []): bool
    {
        $url = site_setting('discord_webhook_url');
        if (empty($url)) {
            return false;
        }

        try {
            $payload = [];
            if ($content) {
                $payload['content'] = $content;
            }
            if (! empty($embeds)) {
                $payload['embeds'] = $embeds;
            }

            $response = Http::timeout(5)->post($url, $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Discord webhook failed: '.$e->getMessage());

            return false;
        }
    }

    public function sendEmbed(string $title, string $description, int $color = 0x22C55E, array $fields = []): bool
    {
        $embed = [
            'title' => $title,
            'description' => $description,
            'color' => $color,
            'timestamp' => now()->toIso8601String(),
        ];

        if (! empty($fields)) {
            $embed['fields'] = $fields;
        }

        return $this->send('', [$embed]);
    }
}
