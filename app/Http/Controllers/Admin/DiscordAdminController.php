<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordRichPresence;
use Illuminate\Http\Request;

class DiscordAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = DiscordRichPresence::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('discord_user_id', 'ilike', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"));
            });
        }

        if ($request->filled('activity')) {
            $query->where('current_activity', $request->activity);
        }

        if ($request->filled('enabled')) {
            $query->where('enabled', $request->enabled === 'yes');
        }

        $presences = $query->orderByDesc('last_updated_at')->paginate(25);

        $stats = [
            'total' => DiscordRichPresence::count(),
            'enabled' => DiscordRichPresence::where('enabled', true)->count(),
            'active' => DiscordRichPresence::where('enabled', true)->whereNotNull('current_activity')->count(),
        ];

        return view('admin.discord.index', compact('presences', 'stats'));
    }

    public function disable(DiscordRichPresence $presence)
    {
        $presence->update(['enabled' => false]);

        return back()->with('success', 'Discord presence disabled.');
    }

    public function destroy(DiscordRichPresence $presence)
    {
        $presence->delete();

        return redirect()->route('admin.discord.index')->with('success', 'Presence record deleted.');
    }
}
