<?php

namespace App\Http\Controllers;

use App\Models\BanAppeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanAppealController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the ban appeal form
     */
    public function create()
    {
        $user = Auth::user();

        // Check if user is banned
        if (!$user->is_banned) {
            return redirect()->route('profile.show')
                ->with('error', 'You are not currently banned.');
        }

        // Check if user already has a pending appeal
        if ($user->hasPendingBanAppeal()) {
            return redirect()->route('ban-appeals.show', $user->pendingBanAppeal())
                ->with('info', 'You already have a pending ban appeal.');
        }

        return view('ban-appeals.create', [
            'user' => $user,
        ]);
    }

    /**
     * Store a new ban appeal
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user is banned
        if (!$user->is_banned) {
            return redirect()->route('profile.show')
                ->with('error', 'You are not currently banned.');
        }

        // Check if user already has a pending appeal
        if ($user->hasPendingBanAppeal()) {
            return redirect()->route('ban-appeals.show', $user->pendingBanAppeal())
                ->with('error', 'You already have a pending ban appeal.');
        }

        $validated = $request->validate([
            'appeal_message' => 'required|string|min:50|max:2000',
        ]);

        $appeal = BanAppeal::create([
            'user_id' => $user->id,
            'reason' => $user->ban_reason ?? 'No reason provided',
            'appeal_message' => $validated['appeal_message'],
            'status' => 'pending',
        ]);

        return redirect()->route('ban-appeals.show', $appeal)
            ->with('success', 'Your ban appeal has been submitted and will be reviewed by an administrator.');
    }

    /**
     * Show a ban appeal
     */
    public function show(BanAppeal $appeal)
    {
        $user = Auth::user();

        // Only allow user to view their own appeals
        if ($appeal->user_id !== $user->id) {
            abort(403, 'You are not authorized to view this ban appeal.');
        }

        return view('ban-appeals.show', [
            'appeal' => $appeal,
        ]);
    }

    /**
     * Show all ban appeals for the current user
     */
    public function index()
    {
        $user = Auth::user();

        $appeals = $user->banAppeals()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('ban-appeals.index', [
            'appeals' => $appeals,
        ]);
    }
}
