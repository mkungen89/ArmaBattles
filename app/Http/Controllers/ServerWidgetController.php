<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;

class ServerWidgetController extends Controller
{
    /**
     * Render the embeddable server status widget.
     */
    public function widget(Server $server, Request $request)
    {
        $theme = $request->get('theme', 'dark');
        $accent = $request->get('accent', '#22c55e');
        $compact = $request->boolean('compact', false);

        return view('widgets.server-status', compact('server', 'theme', 'accent', 'compact'));
    }

    /**
     * Public JSON API endpoint for server status.
     */
    public function api(Server $server)
    {
        return response()->json([
            'id' => $server->id,
            'name' => $server->name,
            'status' => $server->status ?? 'unknown',
            'players' => $server->players ?? 0,
            'max_players' => $server->max_players ?? 0,
            'map' => $server->map,
            'scenario' => $server->scenario_display_name,
            'ip' => $server->ip,
            'port' => $server->port,
            'game_version' => $server->game_version,
            'updated_at' => $server->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Show embed code generator page.
     */
    public function embed(Server $server)
    {
        return view('widgets.embed-code', compact('server'));
    }
}
