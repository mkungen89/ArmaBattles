<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->notifications();

        if ($category = $request->get('category')) {
            $typeMap = [
                'team' => ['team_invitation', 'team_application', 'application_result'],
                'match' => ['match_scheduled', 'match_reminder'],
                'achievement' => ['achievement_unlocked', 'achievement'],
            ];

            if (isset($typeMap[$category])) {
                $types = $typeMap[$category];
                $query->where(function ($q) use ($types) {
                    foreach ($types as $type) {
                        $q->orWhereJsonContains('data->type', $type);
                    }
                });
            } elseif ($category === 'general') {
                $allSpecific = array_merge(...array_values($typeMap));
                $query->where(function ($q) use ($allSpecific) {
                    $q->whereNull('data->type');
                    foreach ($allSpecific as $type) {
                        $q->whereJsonDoesntContain('data->type', $type);
                    }
                });
            }
        }

        $notifications = $query
            ->take(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'general',
                    'message' => $notification->data['message'] ?? '',
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json($notifications);
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
