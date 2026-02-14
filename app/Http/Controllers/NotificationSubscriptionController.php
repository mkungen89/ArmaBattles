<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a push notification subscription
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $user = Auth::user();

        // Store subscription in user preferences
        $preferences = $user->notification_preferences ?? [];
        $preferences['push_subscription'] = $validated;
        $preferences['push_enabled'] = true;

        $user->update([
            'notification_preferences' => $preferences,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Push notifications enabled successfully',
        ]);
    }

    /**
     * Delete a push notification subscription
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        $preferences = $user->notification_preferences ?? [];
        unset($preferences['push_subscription']);
        $preferences['push_enabled'] = false;

        $user->update([
            'notification_preferences' => $preferences,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Push notifications disabled successfully',
        ]);
    }

    /**
     * Get subscription status
     */
    public function status()
    {
        $user = Auth::user();
        $preferences = $user->notification_preferences ?? [];

        return response()->json([
            'enabled' => $preferences['push_enabled'] ?? false,
            'subscribed' => isset($preferences['push_subscription']),
        ]);
    }
}
