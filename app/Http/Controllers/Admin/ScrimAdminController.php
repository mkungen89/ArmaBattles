<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScrimMatch;
use Illuminate\Http\Request;

class ScrimAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ScrimMatch::with(['team1', 'team2', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('team1', fn ($t) => $t->where('name', 'ilike', "%{$search}%"))
                    ->orWhereHas('team2', fn ($t) => $t->where('name', 'ilike', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $scrims = $query->orderByDesc('created_at')->paginate(25);

        $stats = [
            'total' => ScrimMatch::count(),
            'pending' => ScrimMatch::where('status', 'pending')->count(),
            'scheduled' => ScrimMatch::where('status', 'scheduled')->count(),
            'completed' => ScrimMatch::where('status', 'completed')->count(),
        ];

        return view('admin.scrims.index', compact('scrims', 'stats'));
    }

    public function cancel(ScrimMatch $scrim)
    {
        if ($scrim->isCompleted()) {
            return back()->with('error', 'Cannot cancel a completed scrim.');
        }

        $scrim->update(['status' => 'cancelled']);

        return back()->with('success', 'Scrim has been cancelled.');
    }

    public function destroy(ScrimMatch $scrim)
    {
        $scrim->delete();

        return redirect()->route('admin.scrims.index')->with('success', 'Scrim deleted.');
    }
}
