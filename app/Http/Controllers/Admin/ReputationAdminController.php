<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlayerReputation;
use Illuminate\Http\Request;

class ReputationAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PlayerReputation::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn ($q) => $q->where('name', 'ilike', "%{$search}%"));
        }

        if ($request->filled('tier')) {
            $query->where(function ($q) use ($request) {
                match ($request->tier) {
                    'trusted' => $q->where('total_score', '>=', 100),
                    'good' => $q->where('total_score', '>=', 50)->where('total_score', '<', 100),
                    'neutral' => $q->where('total_score', '>=', 0)->where('total_score', '<', 50),
                    'poor' => $q->where('total_score', '>=', -50)->where('total_score', '<', 0),
                    'flagged' => $q->where('total_score', '<', -50),
                    default => null,
                };
            });
        }

        $reputations = $query->orderBy('total_score', 'desc')->paginate(25);

        $stats = [
            'total' => PlayerReputation::count(),
            'trusted' => PlayerReputation::where('total_score', '>=', 100)->count(),
            'flagged' => PlayerReputation::where('total_score', '<', -50)->count(),
        ];

        return view('admin.reputation.index', compact('reputations', 'stats'));
    }

    public function resetReputation(PlayerReputation $reputation)
    {
        $reputation->votes()->delete();

        $reputation->update([
            'total_score' => 0,
            'positive_votes' => 0,
            'negative_votes' => 0,
            'teamwork_count' => 0,
            'leadership_count' => 0,
            'sportsmanship_count' => 0,
        ]);

        return back()->with('success', 'Reputation reset to zero.');
    }

    public function destroy(PlayerReputation $reputation)
    {
        $reputation->votes()->delete();
        $reputation->delete();

        return redirect()->route('admin.reputation.index')->with('success', 'Reputation record deleted.');
    }
}
