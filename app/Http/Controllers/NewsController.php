<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\NewsComment;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $articles = NewsArticle::published()
            ->pinnedFirst()
            ->orderByDesc('published_at')
            ->with('author')
            ->withCount(['comments', 'hoorahs'])
            ->paginate(12);

        return view('news.index', compact('articles'));
    }

    public function show(NewsArticle $article)
    {
        // Drafts only visible to GMs/admins
        if ($article->status !== 'published') {
            if (!auth()->check() || !auth()->user()->isGM()) {
                abort(404);
            }
        }

        $article->load(['author', 'comments.user']);
        $article->loadCount('hoorahs');

        $hasHoorahed = $article->isHoorahedBy(auth()->user());

        return view('news.show', compact('article', 'hasHoorahed'));
    }

    public function storeComment(Request $request, NewsArticle $article)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $article->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        return back()->with('success', 'Comment posted.');
    }

    public function destroyComment(NewsComment $comment)
    {
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }

    public function toggleHoorah(Request $request, NewsArticle $article)
    {
        $user = auth()->user();
        $existing = $article->hoorahs()->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $hoorahed = false;
        } else {
            $article->hoorahs()->create(['user_id' => $user->id]);
            $hoorahed = true;
        }

        $count = $article->hoorahs()->count();

        if ($request->wantsJson()) {
            return response()->json(['hoorahed' => $hoorahed, 'count' => $count]);
        }

        return back();
    }
}
