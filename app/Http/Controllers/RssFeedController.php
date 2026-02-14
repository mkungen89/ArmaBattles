<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Http\Response;

class RssFeedController extends Controller
{
    /**
     * Generate RSS feed for news articles
     */
    public function news(): Response
    {
        $articles = NewsArticle::with('author')
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit(50)
            ->get();

        $siteName = site_setting('site_name', config('app.name'));
        $siteUrl = config('app.url');
        $siteDescription = site_setting('meta_description', 'Community news and updates');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>'.htmlspecialchars($siteName.' - News').'</title>';
        $xml .= '<link>'.htmlspecialchars($siteUrl.'/news').'</link>';
        $xml .= '<description>'.htmlspecialchars($siteDescription).'</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<atom:link href="'.htmlspecialchars(route('rss.news')).'" rel="self" type="application/rss+xml" />';

        if ($articles->isNotEmpty()) {
            $xml .= '<lastBuildDate>'.htmlspecialchars($articles->first()->published_at->toRfc2822String()).'</lastBuildDate>';
        }

        foreach ($articles as $article) {
            $xml .= '<item>';
            $xml .= '<title>'.htmlspecialchars($article->title).'</title>';
            $xml .= '<link>'.htmlspecialchars(route('news.show', $article)).'</link>';
            $xml .= '<guid isPermaLink="true">'.htmlspecialchars(route('news.show', $article)).'</guid>';
            $xml .= '<description>'.htmlspecialchars($article->excerpt ?? strip_tags(substr($article->content, 0, 200)).'...').'</description>';

            if ($article->author) {
                $xml .= '<author>'.htmlspecialchars($article->author->email.' ('.($article->author->name).')').'</author>';
            }

            if ($article->published_at) {
                $xml .= '<pubDate>'.htmlspecialchars($article->published_at->toRfc2822String()).'</pubDate>';
            }

            // Add categories/tags if available
            if ($article->category) {
                $xml .= '<category>'.htmlspecialchars($article->category).'</category>';
            }

            $xml .= '</item>';
        }

        $xml .= '</channel>';
        $xml .= '</rss>';

        return response($xml, 200)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
    }

    /**
     * Generate RSS feed for tournament updates
     */
    public function tournaments(): Response
    {
        $tournaments = \App\Models\Tournament::with('winnerTeam')
            ->whereIn('status', ['registration_open', 'in_progress', 'completed'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $siteName = site_setting('site_name', config('app.name'));
        $siteUrl = config('app.url');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>'.htmlspecialchars($siteName.' - Tournaments').'</title>';
        $xml .= '<link>'.htmlspecialchars($siteUrl.'/tournaments').'</link>';
        $xml .= '<description>Latest tournament updates and results</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<atom:link href="'.htmlspecialchars(route('rss.tournaments')).'" rel="self" type="application/rss+xml" />';

        if ($tournaments->isNotEmpty()) {
            $xml .= '<lastBuildDate>'.htmlspecialchars($tournaments->first()->created_at->toRfc2822String()).'</lastBuildDate>';
        }

        foreach ($tournaments as $tournament) {
            $xml .= '<item>';
            $xml .= '<title>'.htmlspecialchars($tournament->name).'</title>';
            $xml .= '<link>'.htmlspecialchars(route('tournaments.show', $tournament)).'</link>';
            $xml .= '<guid isPermaLink="true">'.htmlspecialchars(route('tournaments.show', $tournament)).'</guid>';

            $description = "Status: {$tournament->status}";
            if ($tournament->status === 'completed' && $tournament->winnerTeam) {
                $description .= " | Winner: {$tournament->winnerTeam->name}";
            }
            $description .= " | Format: ".ucfirst(str_replace('_', ' ', $tournament->format));
            if ($tournament->prize_pool) {
                $description .= " | Prize: {$tournament->prize_pool}";
            }

            $xml .= '<description>'.htmlspecialchars($description).'</description>';
            $xml .= '<category>'.htmlspecialchars(ucfirst(str_replace('_', ' ', $tournament->format))).'</category>';

            if ($tournament->starts_at) {
                $xml .= '<pubDate>'.htmlspecialchars($tournament->starts_at->toRfc2822String()).'</pubDate>';
            }

            $xml .= '</item>';
        }

        $xml .= '</channel>';
        $xml .= '</rss>';

        return response($xml, 200)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=1800'); // Cache for 30 minutes
    }
}
