<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileDataController extends Controller
{
    /**
     * Export all user data (GDPR Article 15 - Right to data portability)
     */
    public function exportData(Request $request)
    {
        $user = auth()->user();

        // Collect all user data
        $data = [
            'export_date' => now()->toIso8601String(),
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'steam_id' => $user->steam_id,
                'google_id' => $user->google_id,
                'discord_id' => $user->discord_id,
                'discord_username' => $user->discord_username,
                'role' => $user->role,
                'player_uuid' => $user->player_uuid,
                'profile_visibility' => $user->profile_visibility,
                'created_at' => $user->created_at?->toIso8601String(),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
                'last_seen_at' => $user->last_seen_at?->toIso8601String(),
                'social_links' => $user->social_links,
                'custom_avatar' => $user->custom_avatar,
            ],
            'game_stats' => $user->gameStats()?->toArray() ?? [],
            'teams' => $user->teams()->get()->map(function ($team) {
                return [
                    'name' => $team->name,
                    'tag' => $team->tag,
                    'role' => $team->pivot->role ?? 'member',
                    'joined_at' => $team->pivot->created_at ?? null,
                ];
            }),
            'tournament_registrations' => $user->teams()->with('registrations.tournament')->get()->flatMap(function ($team) {
                return $team->registrations->map(function ($reg) use ($team) {
                    return [
                        'tournament' => $reg->tournament->name ?? 'Unknown',
                        'team' => $team->name,
                        'status' => $reg->status,
                        'registered_at' => $reg->created_at?->toIso8601String(),
                    ];
                });
            }),
            'notifications' => $user->notifications()->latest()->take(100)->get()->map(function ($notification) {
                return [
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->toIso8601String(),
                ];
            }),
            'favorites' => $user->favorites()->with('favoritable')->get()->map(function ($fav) {
                return [
                    'type' => class_basename($fav->favoritable_type),
                    'item' => $fav->favoritable?->name ?? $fav->favoritable?->display_name ?? 'Unknown',
                    'created_at' => $fav->created_at?->toIso8601String(),
                ];
            }),
            'player_rating' => $user->playerRating()?->toArray() ?? null,
            'reputation' => $user->reputation()?->toArray() ?? null,
            'achievements' => $user->achievements()->with('achievement')->get()->map(function ($progress) {
                return [
                    'achievement' => $progress->achievement->name ?? 'Unknown',
                    'progress' => $progress->progress,
                    'completed' => $progress->completed,
                    'unlocked_at' => $progress->unlocked_at?->toIso8601String(),
                ];
            }),
            'content_creator' => $user->contentCreator()?->toArray() ?? null,
        ];

        // Generate filename
        $filename = 'user-data-export-' . $user->id . '-' . now()->format('Y-m-d-His') . '.json';

        // Return as download
        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show account deletion confirmation page
     */
    public function showDeleteAccount()
    {
        return view('profile.delete-account');
    }

    /**
     * Delete user account (GDPR Article 17 - Right to erasure)
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'confirmation' => 'required|in:DELETE MY ACCOUNT',
            'password' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!Hash::check($value, auth()->user()->password)) {
                    $fail('The password is incorrect.');
                }
            }],
        ]);

        $user = auth()->user();

        // Anonymize or delete data
        $this->anonymizeUserData($user);

        // Logout
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been deleted. All personal data has been removed.');
    }

    /**
     * Anonymize user data instead of hard delete (preserve stats integrity)
     */
    protected function anonymizeUserData(User $user)
    {
        // Delete personal information
        $user->update([
            'name' => 'Deleted User #' . $user->id,
            'email' => 'deleted-' . $user->id . '@deleted.local',
            'steam_id' => null,
            'google_id' => null,
            'google_email' => null,
            'discord_id' => null,
            'discord_username' => null,
            'avatar' => null,
            'avatar_full' => null,
            'profile_url' => null,
            'custom_avatar' => null,
            'password' => bcrypt(str()->random(64)),
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'social_links' => null,
            'notification_preferences' => null,
            'is_banned' => true, // Prevent login
            'ban_reason' => 'Account deleted by user',
        ]);

        // Delete sensitive relations
        $user->notifications()->delete();
        $user->favorites()->delete();
        $user->tokens()->delete(); // API tokens

        // Keep game stats but anonymize (preserve leaderboards)
        // Team memberships kept for historical records
        // Tournament registrations kept for bracket integrity
    }
}
