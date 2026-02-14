<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\Server;
use App\Models\Tournament;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap
     */
    public function index()
    {
        $urls = [];

        // Static pages (priority: 1.0 = highest, 0.1 = lowest)
        $urls[] = ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('about'), 'priority' => '0.9', 'changefreq' => 'monthly'];
        $urls[] = ['loc' => route('contact'), 'priority' => '0.8', 'changefreq' => 'monthly'];
        $urls[] = ['loc' => route('faq'), 'priority' => '0.8', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => route('privacy'), 'priority' => '0.5', 'changefreq' => 'yearly'];
        $urls[] = ['loc' => route('terms'), 'priority' => '0.5', 'changefreq' => 'yearly'];
        $urls[] = ['loc' => route('rules'), 'priority' => '0.7', 'changefreq' => 'monthly'];

        // Features
        $urls[] = ['loc' => route('leaderboard'), 'priority' => '0.9', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('levels.index'), 'priority' => '0.8', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('players.index'), 'priority' => '0.8', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('weapons.index'), 'priority' => '0.7', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => route('achievements.index'), 'priority' => '0.7', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => route('teams.index'), 'priority' => '0.8', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('tournaments.index'), 'priority' => '0.9', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('ranked.index'), 'priority' => '0.8', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('content-creators.index'), 'priority' => '0.7', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => route('clips.index'), 'priority' => '0.7', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('recruitment.index'), 'priority' => '0.7', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('reputation.index'), 'priority' => '0.6', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => route('news.index'), 'priority' => '0.8', 'changefreq' => 'daily'];

        // Servers
        $servers = Server::all();
        foreach ($servers as $server) {
            $urls[] = [
                'loc' => route('servers.show', $server->id),
                'priority' => '0.7',
                'changefreq' => 'hourly',
                'lastmod' => $server->updated_at?->toAtomString(),
            ];
        }

        // Active Tournaments
        $tournaments = Tournament::whereIn('status', ['registration_open', 'in_progress'])
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        foreach ($tournaments as $tournament) {
            $urls[] = [
                'loc' => route('tournaments.show', $tournament->id),
                'priority' => '0.8',
                'changefreq' => 'daily',
                'lastmod' => $tournament->updated_at?->toAtomString(),
            ];
        }

        // Recent News Articles
        $articles = NewsArticle::where('published', true)
            ->orderBy('published_at', 'desc')
            ->limit(100)
            ->get();

        foreach ($articles as $article) {
            $urls[] = [
                'loc' => route('news.show', $article->id),
                'priority' => '0.6',
                'changefreq' => 'weekly',
                'lastmod' => $article->updated_at?->toAtomString(),
            ];
        }

        // Generate XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . PHP_EOL;

            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            }

            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
