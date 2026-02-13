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
            }
        }

        $creators = $query->orderByDesc('created_at')->paginate(25);

        $stats = [
            'total' => ContentCreator::count(),
            'verified' => ContentCreator::where('is_verified', true)->count(),
            'unverified' => ContentCreator::where('is_verified', false)->count(),
            'live' => ContentCreator::where('is_live', true)->count(),
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
}
