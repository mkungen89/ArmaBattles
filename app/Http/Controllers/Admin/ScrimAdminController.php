<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScrimMatch;
use App\Traits\LogsAdminActions;
use Illuminate\Http\Request;

class ScrimAdminController extends Controller
{
    use LogsAdminActions;
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

        $this->logAction('scrim.cancelled', 'ScrimMatch', $scrim->id, [
            'team1_id' => $scrim->team1_id,
            'team2_id' => $scrim->team2_id,
            'old_status' => $scrim->getOriginal('status'),
        ]);

        return back()->with('success', 'Scrim has been cancelled.');
    }

    public function destroy(ScrimMatch $scrim)
    {
        $scrimId = $scrim->id;
        $team1Id = $scrim->team1_id;
        $team2Id = $scrim->team2_id;

        $scrim->delete();

        $this->logAction('scrim.deleted', 'ScrimMatch', $scrimId, [
            'team1_id' => $team1Id,
            'team2_id' => $team2Id,
        ]);

        return redirect()->route('admin.scrims.index')->with('success', 'Scrim deleted.');
    }
}
