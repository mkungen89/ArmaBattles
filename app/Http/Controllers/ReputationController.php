<?php

namespace App\Http\Controllers;

use App\Models\PlayerReputation;
use App\Models\ReputationVote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReputationController extends Controller
{
    /**
     * Show reputation leaderboard
     */
    public function index()
    {
        $topPlayers = PlayerReputation::with('user')
            ->where('total_score', '>', 0)
            ->orderBy('total_score', 'desc')
            ->limit(50)
            ->get();

        $trustedPlayers = PlayerReputation::with('user')
            ->where('total_score', '>=', 100)
            ->orderBy('total_score', 'desc')
            ->get();

        $flaggedPlayers = PlayerReputation::with('user')
            ->where('total_score', '<=', -50)
            ->orderBy('total_score', 'asc')
            ->get();

        return view('reputation.index', compact('topPlayers', 'trustedPlayers', 'flaggedPlayers'));
    }

    /**
     * Submit a reputation vote
     */
    public function vote(Request $request, User $user)
    {
        $validated = $request->validate([
            'vote_type' => 'required|in:positive,negative',
            'category' => 'required|in:teamwork,leadership,sportsmanship,general',
            'comment' => 'nullable|string|max:500',
        ]);

        // Cannot vote for yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot vote for yourself.');
        }

        // Check if user already voted
        $existingVote = ReputationVote::where('voter_id', auth()->id())
            ->where('target_id', $user->id)
            ->first();

        if ($existingVote) {
            if (! $existingVote->canBeChanged()) {
                $cooldownHours = (int) site_setting('reputation_vote_cooldown_hours', 24);
                return back()->with('error', "You can only change your vote within {$cooldownHours} hours.");
            }

            // Update existing vote
            DB::transaction(function () use ($existingVote, $validated, $user) {
                // Revert old vote
                $this->updateReputationScore($user, $existingVote->vote_type, $existingVote->category, -1);

                // Apply new vote
                $existingVote->update($validated);
                $this->updateReputationScore($user, $validated['vote_type'], $validated['category'], 1);
            });

            return back()->with('success', 'Your vote has been updated!');
        }

        // Check daily vote limit (only for new votes)
        $maxVotesPerDay = (int) site_setting('reputation_max_votes_per_day', 0);
        if ($maxVotesPerDay > 0) {
            $votesToday = ReputationVote::where('voter_id', auth()->id())
                ->where('created_at', '>=', now()->startOfDay())
                ->count();

            if ($votesToday >= $maxVotesPerDay) {
                return back()->with('error', "You have reached your daily voting limit ({$maxVotesPerDay} votes per day).");
            }
        }

        // Create new vote
        DB::transaction(function () use ($user, $validated) {
            ReputationVote::create([
                'voter_id' => auth()->id(),
                'target_id' => $user->id,
                'vote_type' => $validated['vote_type'],
                'category' => $validated['category'],
                'comment' => $validated['comment'],
            ]);

            $this->updateReputationScore($user, $validated['vote_type'], $validated['category'], 1);
        });

        return back()->with('success', 'Your vote has been submitted!');
    }

    /**
     * Submit a reputation vote by target_user_id
     */
    public function voteById(Request $request)
    {
        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'vote_type' => 'required|in:positive,negative',
            'category' => 'required|in:teamwork,leadership,sportsmanship,general',
            'comment' => 'nullable|string|max:500',
        ]);

        // Cannot vote for yourself
        if ($validated['target_user_id'] == auth()->id()) {
            return back()->withErrors(['target_user_id' => 'You cannot vote for yourself.']);
        }

        $targetUser = User::findOrFail($validated['target_user_id']);

        // Check if user already voted
        $existingVote = ReputationVote::where('voter_id', auth()->id())
            ->where('target_id', $targetUser->id)
            ->first();

        if ($existingVote) {
            if (! $existingVote->canBeChanged()) {
                $cooldownHours = (int) site_setting('reputation_vote_cooldown_hours', 24);
                return back()->with('error', "You can only change your vote within {$cooldownHours} hours.");
            }

            // Update existing vote
            DB::transaction(function () use ($existingVote, $validated, $targetUser) {
                // Revert old vote
                $this->updateReputationScore($targetUser, $existingVote->vote_type, $existingVote->category, -1);

                // Apply new vote
                $existingVote->update([
                    'vote_type' => $validated['vote_type'],
                    'category' => $validated['category'],
                    'comment' => $validated['comment'] ?? $existingVote->comment,
                ]);

                $this->updateReputationScore($targetUser, $validated['vote_type'], $validated['category'], 1);
            });

            return back()->with('success', 'Your vote has been updated!');
        }

        // Check daily vote limit (only for new votes)
        $maxVotesPerDay = (int) site_setting('reputation_max_votes_per_day', 0);
        if ($maxVotesPerDay > 0) {
            $votesToday = ReputationVote::where('voter_id', auth()->id())
                ->where('created_at', '>=', now()->startOfDay())
                ->count();

            if ($votesToday >= $maxVotesPerDay) {
                return back()->with('error', "You have reached your daily voting limit ({$maxVotesPerDay} votes per day).");
            }
        }

        // Create new vote
        DB::transaction(function () use ($targetUser, $validated) {
            ReputationVote::create([
                'voter_id' => auth()->id(),
                'target_id' => $targetUser->id,
                'vote_type' => $validated['vote_type'],
                'category' => $validated['category'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $this->updateReputationScore($targetUser, $validated['vote_type'], $validated['category'], 1);
        });

        return back()->with('success', 'Your vote has been submitted!');
    }

    /**
     * Update an existing vote
     */
    public function updateVote(Request $request, ReputationVote $vote)
    {
        $validated = $request->validate([
            'vote_type' => 'required|in:positive,negative',
            'category' => 'nullable|in:teamwork,leadership,sportsmanship,general',
            'comment' => 'nullable|string|max:500',
        ]);

        // Check if user owns this vote
        if ($vote->voter_id !== auth()->id()) {
            return response('You cannot update this vote.', 403);
        }

        // Check if vote can be changed (within 24h)
        if ($vote->created_at->diffInHours(now()) >= 24) {
            return response('You can only change your vote within 24 hours.', 403);
        }

        DB::transaction(function () use ($vote, $validated) {
            $targetUser = $vote->target;

            $newCategory = $validated['category'] ?? $vote->category;
            $newComment = $validated['comment'] ?? $vote->comment;

            // Revert old vote
            $this->updateReputationScore($targetUser, $vote->vote_type, $vote->category, -1);

            // Apply new vote
            $vote->update([
                'vote_type' => $validated['vote_type'],
                'category' => $newCategory,
                'comment' => $newComment,
            ]);

            $this->updateReputationScore($targetUser, $validated['vote_type'], $newCategory, 1);
        });

        return back()->with('success', 'Your vote has been updated!');
    }

    /**
     * Remove a vote
     */
    public function removeVote(User $user)
    {
        $vote = ReputationVote::where('voter_id', auth()->id())
            ->where('target_id', $user->id)
            ->first();

        if (! $vote) {
            return back()->with('error', 'No vote found.');
        }

        if (! $vote->canBeChanged()) {
            return back()->with('error', 'You can only change your vote within 24 hours.');
        }

        DB::transaction(function () use ($vote, $user) {
            $this->updateReputationScore($user, $vote->vote_type, $vote->category, -1);
            $vote->delete();
        });

        return back()->with('success', 'Your vote has been removed.');
    }

    /**
     * Update reputation score
     */
    protected function updateReputationScore(User $user, string $voteType, string $category, int $multiplier)
    {
        $reputation = PlayerReputation::firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_score' => 0,
                'positive_votes' => 0,
                'negative_votes' => 0,
                'teamwork_count' => 0,
                'leadership_count' => 0,
                'sportsmanship_count' => 0,
            ]
        );

        $scoreChange = ($voteType === 'positive' ? 1 : -1) * $multiplier;

        $reputation->increment('total_score', $scoreChange);

        if ($voteType === 'positive') {
            $reputation->increment('positive_votes', $multiplier);
        } else {
            $reputation->increment('negative_votes', $multiplier);
        }

        // Update category count
        if ($category !== 'general') {
            $column = $category.'_count';
            $reputation->increment($column, $multiplier);
        }
    }

    /**
     * Show player's reputation details
     */
    public function show(User $user)
    {
        $reputation = $user->reputation()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'total_score' => 0,
            'positive_votes' => 0,
            'negative_votes' => 0,
            'teamwork_count' => 0,
            'leadership_count' => 0,
            'sportsmanship_count' => 0,
        ]);

        $recentVotes = $user->receivedVotes()
            ->with('voter')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $myVote = null;
        if (auth()->check()) {
            $myVote = ReputationVote::where('voter_id', auth()->id())
                ->where('target_id', $user->id)
                ->first();
        }

        return view('reputation.show', compact('user', 'reputation', 'recentVotes', 'myVote'));
    }
}
