<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Traits\LogsAdminActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsAdminController extends Controller
{
    use LogsAdminActions;

    public function index(Request $request)
    {
        $query = NewsArticle::with('author')->withCount(['comments', 'hoorahs']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $articles = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.news.index', compact('articles'));
    }

    public function create()
    {
        return view('admin.news.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
            'status' => 'required|in:draft,published',
            'is_pinned' => 'boolean',
        ]);

        $article = new NewsArticle;
        $article->title = $validated['title'];
        $article->content = $validated['content'];
        $article->excerpt = $validated['excerpt'] ?? null;
        $article->status = $validated['status'];
        $article->is_pinned = $request->boolean('is_pinned');
        $article->author_id = auth()->id();

        if ($validated['status'] === 'published') {
            $article->published_at = now();
        }

        if ($request->hasFile('featured_image')) {
            $article->featured_image = $request->file('featured_image')->store('news', 's3');
        }

        $article->save();

        $this->logAction('news.created', 'NewsArticle', $article->id, ['title' => $article->title]);

        return redirect()->route('admin.news.index')->with('success', 'Article created successfully.');
    }

    public function edit(NewsArticle $article)
    {
        $this->authorizeArticle($article);

        return view('admin.news.edit', compact('article'));
    }

    public function update(Request $request, NewsArticle $article)
    {
        $this->authorizeArticle($article);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
            'status' => 'required|in:draft,published',
            'is_pinned' => 'boolean',
        ]);

        $article->title = $validated['title'];
        $article->content = $validated['content'];
        $article->excerpt = $validated['excerpt'] ?? null;
        $article->status = $validated['status'];
        $article->is_pinned = $request->boolean('is_pinned');

        if ($validated['status'] === 'published' && ! $article->published_at) {
            $article->published_at = now();
        }

        if ($request->hasFile('featured_image')) {
            if ($article->featured_image) {
                Storage::disk('s3')->delete($article->featured_image);
            }
            $article->featured_image = $request->file('featured_image')->store('news', 's3');
        }

        $article->save();

        $this->logAction('news.updated', 'NewsArticle', $article->id, ['title' => $article->title]);

        return redirect()->route('admin.news.index')->with('success', 'Article updated successfully.');
    }

    public function togglePin(NewsArticle $article)
    {
        $article->update(['is_pinned' => ! $article->is_pinned]);

        $this->logAction('news.pin-toggled', 'NewsArticle', $article->id, ['is_pinned' => $article->is_pinned]);

        return back()->with('success', $article->is_pinned ? 'Article pinned.' : 'Article unpinned.');
    }

    public function destroy(NewsArticle $article)
    {
        $this->authorizeArticle($article);

        if ($article->featured_image) {
            Storage::disk('s3')->delete($article->featured_image);
        }

        $this->logAction('news.deleted', 'NewsArticle', $article->id, ['title' => $article->title]);

        $article->delete();

        return redirect()->route('admin.news.index')->with('success', 'Article deleted.');
    }

    public function deleteImage(NewsArticle $article)
    {
        $this->authorizeArticle($article);

        if ($article->featured_image) {
            Storage::disk('s3')->delete($article->featured_image);
            $article->update(['featured_image' => null]);
        }

        return back()->with('success', 'Image removed.');
    }

    private function authorizeArticle(NewsArticle $article): void
    {
        if (! auth()->user()->isAdmin() && $article->author_id !== auth()->id()) {
            abort(403, 'You can only manage your own articles.');
        }
    }
}
