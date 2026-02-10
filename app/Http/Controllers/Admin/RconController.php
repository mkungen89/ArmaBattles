<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RconController extends Controller
{
    private string $rconApiUrl;

    private string $apiKey;

    public function __construct()
    {
        $this->rconApiUrl = config('services.rcon.api_url');
        $this->apiKey = config('services.rcon.api_key');
    }

    /**
     * Show the RCON panel
     */
    public function index()
    {
        return view('admin.rcon.index');
    }

    /**
     * Get RCON status
     */
    public function status()
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->get($this->rconApiUrl.'/rcon/status');

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get online players
     */
    public function players()
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->get($this->rconApiUrl.'/rcon/players');

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get ban list
     */
    public function bans()
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->get($this->rconApiUrl.'/rcon/bans');

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send a command
     */
    public function command(Request $request)
    {
        $request->validate([
            'command' => 'required|string|max:500',
        ]);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->post($this->rconApiUrl.'/rcon/command', [
                    'command' => $request->command,
                ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Kick a player
     */
    public function kick(Request $request)
    {
        $request->validate([
            'player_id' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->post($this->rconApiUrl.'/rcon/kick', [
                    'playerId' => $request->player_id,
                    'reason' => $request->reason ?? 'Kicked by admin',
                ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Ban a player
     */
    public function ban(Request $request)
    {
        $request->validate([
            'player_id' => 'required|integer',
            'minutes' => 'nullable|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->post($this->rconApiUrl.'/rcon/ban', [
                    'playerId' => $request->player_id,
                    'minutes' => $request->minutes ?? 0,
                    'reason' => $request->reason ?? 'Banned by admin',
                ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Unban a player
     */
    public function unban(Request $request)
    {
        $request->validate([
            'ban_index' => 'required|integer',
        ]);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->post($this->rconApiUrl.'/rcon/unban', [
                    'banIndex' => $request->ban_index,
                ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send a message (broadcast or to specific player)
     */
    public function say(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'player_id' => 'nullable|integer',
        ]);

        try {
            $data = ['message' => $request->message];
            if ($request->has('player_id')) {
                $data['playerId'] = $request->player_id;
            }

            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->post($this->rconApiUrl.'/rcon/say', $data);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
