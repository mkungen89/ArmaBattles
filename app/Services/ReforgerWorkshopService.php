<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReforgerWorkshopService
{
    protected string $baseUrl = 'https://reforger.armaplatform.com';

    /**
     * Generate a URL slug from mod name
     */
    protected function generateSlug(string $name): string
    {
        // Remove special characters, keep alphanumeric and spaces
        $slug = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $name);
        // Replace spaces with hyphens
        $slug = preg_replace('/\s+/', '-', trim($slug));
        // Remove consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        return $slug;
    }

    /**
     * Build workshop URL with ID and slug
     */
    public function buildWorkshopUrl(string $modId, ?string $modName = null): string
    {
        if ($modName) {
            $slug = $this->generateSlug($modName);
            return "{$this->baseUrl}/workshop/{$modId}-{$slug}";
        }
        return "{$this->baseUrl}/workshop/{$modId}";
    }

    /**
     * Get mod details from Reforger Workshop by scraping the page
     */
    public function getMod(string $modId, ?string $modName = null): ?array
    {
        $cacheKey = "reforger.mod.{$modId}";

        return Cache::remember($cacheKey, 3600, function () use ($modId, $modName) {
            try {
                // Try with slug if we have a name
                $url = $this->buildWorkshopUrl($modId, $modName);

                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    ])
                    ->get($url);

                // If 404 with slug, the slug might be wrong - try to find from workshop listing
                if ($response->status() === 404 && $modName) {
                    Log::info("Workshop URL with slug failed, trying to find correct URL for {$modId}");
                    $correctUrl = $this->findModUrl($modId);
                    if ($correctUrl) {
                        $response = Http::timeout(10)
                            ->withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                            ])
                            ->get($correctUrl);
                    }
                }

                if ($response->failed()) {
                    Log::warning("Failed to fetch mod {$modId} from Reforger Workshop", [
                        'status' => $response->status(),
                        'url' => $url,
                    ]);
                    return null;
                }

                $html = $response->body();

                // Extract data from Next.js __NEXT_DATA__ script
                if (preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
                    $jsonData = json_decode($matches[1], true);
                    $pageProps = $jsonData['props']['pageProps'] ?? [];
                    $asset = $pageProps['asset'] ?? $pageProps['mod'] ?? null;

                    if ($asset) {
                        return $this->normalizeAssetData($asset);
                    }
                }

                // Fallback: Extract from meta tags
                $data = [];

                if (preg_match('/<meta property="og:title" content="([^"]+)"/', $html, $m)) {
                    $data['name'] = html_entity_decode($m[1]);
                }

                if (preg_match('/<meta property="og:description" content="([^"]+)"/', $html, $m)) {
                    $data['description'] = html_entity_decode($m[1]);
                }

                return !empty($data) ? $data : null;
            } catch (\Exception $e) {
                Log::error("Error fetching mod from Reforger Workshop: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Try to find the correct workshop URL for a mod by searching
     */
    protected function findModUrl(string $modId): ?string
    {
        try {
            // Fetch workshop listing and look for the mod ID
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get("{$this->baseUrl}/workshop");

            if ($response->failed()) {
                return null;
            }

            $html = $response->body();

            // Look for href containing our mod ID
            if (preg_match('/href="(\/workshop\/' . preg_quote($modId, '/') . '[^"]*)"/', $html, $matches)) {
                return $this->baseUrl . $matches[1];
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error finding mod URL: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalize asset data from workshop to consistent format
     */
    protected function normalizeAssetData(array $asset): array
    {
        $thumbnailUrl = null;
        if (!empty($asset['previews'][0]['thumbnails']['image/jpeg'][0]['url'])) {
            $thumbnailUrl = $asset['previews'][0]['thumbnails']['image/jpeg'][0]['url'];
        } elseif (!empty($asset['previews'][0]['url'])) {
            $thumbnailUrl = $asset['previews'][0]['url'];
        }

        // Extract author - check nested structure
        $author = null;
        if (isset($asset['author'])) {
            if (is_array($asset['author'])) {
                $author = $asset['author']['username'] ?? $asset['author']['name'] ?? null;
            } else {
                $author = $asset['author'];
            }
        }

        return [
            'id' => $asset['id'] ?? null,
            'name' => $asset['name'] ?? null,
            'author' => $author,
            'version' => $asset['currentVersionNumber'] ?? null,
            'description' => $asset['summary'] ?? $asset['description'] ?? null,
            'thumbnail_url' => $thumbnailUrl,
            'file_size' => $asset['currentVersionSize'] ?? null,
            'subscriptions' => $asset['subscriberCount'] ?? null,
            'updated_at' => $asset['updatedAt'] ?? $asset['currentVersionCreatedAt'] ?? null,
            'rating' => $asset['averageRating'] ?? null,
            'rating_count' => $asset['ratingCount'] ?? null,
        ];
    }

    /**
     * Get multiple mods at once
     * @param array $mods Array of ['id' => modId, 'name' => modName]
     */
    public function getMods(array $mods): array
    {
        $results = [];

        foreach ($mods as $mod) {
            $modId = is_array($mod) ? ($mod['id'] ?? $mod['workshop_id'] ?? null) : $mod;
            $modName = is_array($mod) ? ($mod['name'] ?? null) : null;

            if ($modId) {
                $data = $this->getMod($modId, $modName);
                if ($data) {
                    $results[$modId] = $data;
                }
            }
        }

        return $results;
    }

    /**
     * Sync mod data from Workshop to database
     */
    public function syncMod(string $modId, ?string $modName = null): ?\App\Models\Mod
    {
        // First get existing mod to use its name if we don't have one
        $existingMod = \App\Models\Mod::where('workshop_id', $modId)->first();
        if (!$modName && $existingMod) {
            $modName = $existingMod->name;
        }

        $workshopData = $this->getMod($modId, $modName);

        if (!$workshopData) {
            Log::info("Could not fetch workshop data for mod {$modId}");
            return $existingMod; // Return existing mod if we couldn't fetch new data
        }

        $workshopUrl = $this->buildWorkshopUrl($modId, $workshopData['name'] ?? $modName);

        return \App\Models\Mod::updateOrCreate(
            ['workshop_id' => $modId],
            [
                'name' => $workshopData['name'] ?? $modName ?? 'Unknown',
                'author' => $workshopData['author'] ?? null,
                'version' => $workshopData['version'] ?? null,
                'description' => $workshopData['description'] ?? null,
                'workshop_url' => $workshopUrl,
                'thumbnail_url' => $workshopData['thumbnail_url'] ?? null,
                'file_size' => $workshopData['file_size'] ?? null,
                'subscriptions' => $workshopData['subscriptions'] ?? null,
                'workshop_updated_at' => isset($workshopData['updated_at'])
                    ? \Carbon\Carbon::parse($workshopData['updated_at'])
                    : null,
            ]
        );
    }

    /**
     * Sync all mods for a server from Workshop
     */
    public function syncServerMods(\App\Models\Server $server): int
    {
        $synced = 0;

        foreach ($server->mods as $mod) {
            if ($mod->workshop_id) {
                $updated = $this->syncMod($mod->workshop_id, $mod->name);
                if ($updated) {
                    $synced++;
                }
            }
        }

        return $synced;
    }

    /**
     * Search mods in workshop
     */
    public function search(string $query, int $limit = 20): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/assets", [
                    'search' => $query,
                    'limit' => $limit,
                ]);

            if ($response->failed()) {
                return [];
            }

            return $response->json()['data'] ?? $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error("Error searching Reforger Workshop: " . $e->getMessage());
            return [];
        }
    }
}
