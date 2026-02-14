<?php

namespace App\Http\Controllers;

use App\Models\PlayerRole;
use App\Models\RecruitmentListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecruitmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Show recruitment board
     */
    public function index(Request $request)
    {
        $query = RecruitmentListing::with(['user', 'team'])->active();

        // Filter by type
        if ($request->has('type')) {
            match ($request->type) {
                'players' => $query->playersLookingForTeam(),
                'teams' => $query->teamsLookingForPlayers(),
                default => null,
            };
        }

        // Filter by region
        if ($request->filled('region')) {
            $query->region($request->region);
        }

        // Filter by playstyle
        if ($request->filled('playstyle')) {
            $query->playstyle($request->playstyle);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereJsonContains('preferred_roles', [(int)$request->role]);
        }

        // Featured first, then recent
        $listings = $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $roles = PlayerRole::active()->orderBy('category')->orderBy('display_name')->get();
        $featuredPlayers = RecruitmentListing::with('user')
            ->active()
            ->featured()
            ->playersLookingForTeam()
            ->limit(5)
            ->get();

        return view('recruitment.index', [
            'listings' => $listings,
            'roles' => $roles,
            'featuredPlayers' => $featuredPlayers,
        ]);
    }

    /**
     * Show create listing form
     */
    public function create()
    {
        $user = Auth::user();

        if ($user->hasActiveRecruitmentListing()) {
            return redirect()->route('recruitment.my-listing')
                ->with('info', 'You already have an active recruitment listing.');
        }

        $roles = PlayerRole::active()->orderBy('category')->orderBy('display_name')->get();

        return view('recruitment.create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a new listing
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->hasActiveRecruitmentListing()) {
            return redirect()->route('recruitment.my-listing')
                ->with('error', 'You already have an active recruitment listing.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:50|max:1000',
            'preferred_roles' => 'nullable|array',
            'preferred_roles.*' => 'exists:player_roles,id',
            'playstyle' => 'required|in:casual,competitive,milsim',
            'region' => 'required|in:NA,EU,APAC,SA,OCE',
            'availability' => 'required|in:weekdays,weekends,both',
        ]);

        $listing = RecruitmentListing::create([
            'user_id' => $user->id,
            'listing_type' => 'player_looking_for_team',
            'message' => $validated['message'],
            'preferred_roles' => $validated['preferred_roles'] ?? null,
            'playstyle' => $validated['playstyle'],
            'region' => $validated['region'],
            'availability' => $validated['availability'],
            'expires_at' => now()->addDays(30),
        ]);

        // Update user profile
        $user->update([
            'looking_for_team' => true,
            'preferred_roles' => $validated['preferred_roles'] ?? null,
            'playstyle' => $validated['playstyle'],
            'region' => $validated['region'],
            'availability' => $validated['availability'],
        ]);

        return redirect()->route('recruitment.index')
            ->with('success', 'Your recruitment listing has been created!');
    }

    /**
     * Show user's own listing
     */
    public function myListing()
    {
        $listing = Auth::user()->activeRecruitmentListing();

        if (!$listing) {
            return redirect()->route('recruitment.create')
                ->with('info', 'You don\'t have an active recruitment listing.');
        }

        return view('recruitment.show', ['listing' => $listing]);
    }

    /**
     * Deactivate user's listing
     */
    public function deactivate()
    {
        $listing = Auth::user()->activeRecruitmentListing();

        if ($listing) {
            $listing->update(['is_active' => false]);
            Auth::user()->update(['looking_for_team' => false]);
        }

        return redirect()->route('profile.show')
            ->with('success', 'Your recruitment listing has been deactivated.');
    }
}
