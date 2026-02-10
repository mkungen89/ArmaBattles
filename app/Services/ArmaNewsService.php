<?php

namespace App\Services;

use App\Models\NewsArticle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArmaNewsService
{
    /**
     * Fetch raw news from armaplatform.com (for welcome page, no DB).
     */
    public function fetchNews(int $limit = 15): Collection
    {
        return Cache::remember('arma_platform_news', 3600, function () use ($limit) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    ])
                    ->get('https://reforger.armaplatform.com/news');

                if ($response->failed()) {
                    return collect();
                }

                $html = $response->body();

                if (! preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
                    return collect();
                }

                $jsonData = json_decode($matches[1], true);
                $posts = $jsonData['props']['pageProps']['posts'] ?? [];

                return collect($posts)->take($limit)->map(function ($post) {
                    return [
                        'title' => $post['title'] ?? '',
                        'slug' => $post['slug'] ?? '',
                        'category' => $post['category'] ?? '',
                        'date' => Carbon::parse($post['date'] ?? now()),
                        'excerpt' => $post['excerpt'] ?? '',
                        'image_url' => $post['coverImage']['src'] ?? null,
                        'url' => 'https://reforger.armaplatform.com/news/'.($post['slug'] ?? ''),
                    ];
                });
            } catch (\Exception $e) {
                return collect();
            }
        });
    }

    /**
     * Sync official news into the news_articles table.
     * Fetches listing from fetchNews(), then fetches full content for each article.
     */
    public function syncNews(): void
    {
        try {
            $articles = $this->fetchNews();

            foreach ($articles as $article) {
                if (empty($article['slug'])) {
                    continue;
                }

                $fullContent = $this->fetchArticleContent($article['slug']);

                NewsArticle::updateOrCreate(
                    ['external_slug' => $article['slug']],
                    [
                        'title' => $article['title'],
                        'slug' => 'arma-'.Str::slug($article['slug']),
                        'source' => 'armaplatform',
                        'content' => $fullContent ?: $article['excerpt'] ?: null,
                        'excerpt' => $article['excerpt'] ?: null,
                        'featured_image' => $article['image_url'],
                        'external_url' => $article['url'],
                        'category' => $article['category'] ?: null,
                        'author_id' => null,
                        'status' => 'published',
                        'published_at' => $article['date'],
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::warning('Failed to sync Arma Platform news: '.$e->getMessage());
        }
    }

    /**
     * Fetch full article content from an individual article page.
     * Cached for 24 hours per article since content rarely changes.
     */
    private function fetchArticleContent(string $slug): ?string
    {
        return Cache::remember("arma_article_content_{$slug}", 86400, function () use ($slug) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    ])
                    ->get("https://reforger.armaplatform.com/news/{$slug}");

                if ($response->failed()) {
                    return null;
                }

                $html = $response->body();

                if (! preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
                    return null;
                }

                $jsonData = json_decode($matches[1], true);
                $content = $jsonData['props']['pageProps']['post']['content'] ?? null;

                return $content ?: null;
            } catch (\Exception $e) {
                Log::warning("Failed to fetch article content for {$slug}: ".$e->getMessage());

                return null;
            }
        });
    }
}
