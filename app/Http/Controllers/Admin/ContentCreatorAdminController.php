<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentCreator;
use Illuminate\Http\Request;

class ContentCreatorAdminController extends Controller
{
    use \App\Traits\LogsAdminActions;

    public function index(Request $request)
    {
        $query = ContentCreator::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('channel_name', 'ilike', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"));
            });
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status === 'unverified') {
                $query->where('is_verified', false);
            } elseif ($request->status === 'live') {
                $query->where('is_live', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status === 'featured') {
                $query->where('is_featured', true);
            }
        }

        $creators = $query->orderByDesc('is_live')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate(25);

        $stats = [
            'total' => ContentCreator::count(),
            'pending' => ContentCreator::where('is_approved', false)->count(),
            'verified' => ContentCreator::where('is_verified', true)->count(),
            'unverified' => ContentCreator::where('is_verified', false)->count(),
            'live' => ContentCreator::where('is_live', true)->count(),
            'featured' => ContentCreator::where('is_featured', true)->count(),
        ];

        return view('admin.creators.index', compact('creators', 'stats'));
    }

    public function verify(ContentCreator $creator)
    {
        $creator->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        $this->logAction('creator.verified', 'ContentCreator', $creator->id, [
            'channel_name' => $creator->channel_name,
            'platform' => $creator->platform,
        ]);

        return back()->with('success', 'Creator has been verified.');
    }

    public function unverify(ContentCreator $creator)
    {
        $creator->update([
            'is_verified' => false,
            'verified_at' => null,
        ]);

        $this->logAction('creator.unverified', 'ContentCreator', $creator->id, [
            'channel_name' => $creator->channel_name,
            'platform' => $creator->platform,
        ]);

        return back()->with('success', 'Creator verification removed.');
    }

    public function destroy(ContentCreator $creator)
    {
        $creatorName = $creator->channel_name;
        $creatorId = $creator->id;

        $creator->delete();

        $this->logAction('creator.deleted', 'ContentCreator', $creatorId, [
            'channel_name' => $creatorName,
            'platform' => $creator->platform,
        ]);

        return redirect()->route('admin.creators.index')->with('success', 'Creator deleted.');
    }

    public function show(ContentCreator $creator)
    {
        $creator->load('user');
        return view('admin.creators.show', compact('creator'));
    }

    public function approve(ContentCreator $creator)
    {
        $creator->update(['is_approved' => true]);

        $this->logAction('creator.approved', 'ContentCreator', $creator->id, [
            'channel_name' => $creator->channel_name,
            'platform' => $creator->platform,
        ]);

        return back()->with('success', 'Creator approved successfully.');
    }

    public function reject(ContentCreator $creator)
    {
        $this->logAction('creator.rejected', 'ContentCreator', $creator->id, [
            'channel_name' => $creator->channel_name,
            'platform' => $creator->platform,
        ]);

        $creator->delete();

        return redirect()->route('admin.creators.index')->with('success', 'Creator rejected and deleted.');
    }

    public function toggleFeatured(ContentCreator $creator)
    {
        $creator->update(['is_featured' => !$creator->is_featured]);

        $action = $creator->is_featured ? 'featured' : 'unfeatured';
        $this->logAction("creator.{$action}", 'ContentCreator', $creator->id, [
            'channel_name' => $creator->channel_name,
        ]);

        return back()->with('success', $creator->is_featured ? 'Creator featured.' : 'Creator unfeatured.');
    }

    public function checkLiveStatus(ContentCreator $creator)
    {
        \Artisan::call('creators:check-live');
        $creator->refresh();

        return back()->with('success', 'Live status updated. ' . ($creator->is_live ? 'Creator is LIVE!' : 'Creator is offline.'));
    }

    public function checkAllLiveStatuses()
    {
        \Artisan::call('creators:check-live');

        $this->logAction('creator.live-check-all', 'ContentCreator', null, [
            'timestamp' => now(),
        ]);

        return back()->with('success', 'All live statuses updated.');
    }

    public function edit(ContentCreator $creator)
    {
        return view('admin.creators.edit', compact('creator'));
    }

    public function update(Request $request, ContentCreator $creator)
    {
        $validated = $request->validate([
            'channel_name' => 'required|string|max:255',
            'channel_url' => 'required|url',
            'bio' => 'nullable|string|max:500',
            'follower_count' => 'nullable|integer|min:0',
            'platform' => 'required|in:twitch,youtube,tiktok,kick',
        ]);

        $creator->update($validated);

        $this->logAction('creator.updated', 'ContentCreator', $creator->id, [
            'channel_name' => $creator->channel_name,
            'changes' => $validated,
        ]);

        return redirect()->route('admin.creators.show', $creator)->with('success', 'Creator updated successfully.');
    }
}
