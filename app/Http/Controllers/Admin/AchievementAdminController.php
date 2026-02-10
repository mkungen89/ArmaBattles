<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AchievementAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Achievement::withCount('players');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $achievements = $query->orderBy('sort_order')->orderBy('name')->paginate(25);

        $stats = [
            'total' => Achievement::count(),
            'categories' => Achievement::distinct('category')->count('category'),
            'total_unlocks' => \DB::table('player_achievements')->count(),
        ];

        $categories = Achievement::distinct()->pluck('category')->filter()->sort()->values();

        return view('admin.achievements.index', compact('achievements', 'stats', 'categories'));
    }

    public function create()
    {
        $categories = Achievement::distinct()->pluck('category')->filter()->sort()->values();

        return view('admin.achievements.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:achievements,slug',
            'description' => 'required|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'category' => 'required|string|max:100',
            'stat_field' => 'nullable|string|max:100',
            'threshold' => 'required|integer|min:1',
            'points' => 'required|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'badge' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        if ($request->hasFile('badge')) {
            $validated['badge_path'] = $request->file('badge')->store('achievements', 'public');
        }

        unset($validated['badge']);

        Achievement::create($validated);

        return redirect()->route('admin.achievements.index')->with('success', 'Achievement created.');
    }

    public function edit(Achievement $achievement)
    {
        $achievement->loadCount('players');
        $categories = Achievement::distinct()->pluck('category')->filter()->sort()->values();

        return view('admin.achievements.edit', compact('achievement', 'categories'));
    }

    public function update(Request $request, Achievement $achievement)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:achievements,slug,'.$achievement->id,
            'description' => 'required|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'category' => 'required|string|max:100',
            'stat_field' => 'nullable|string|max:100',
            'threshold' => 'required|integer|min:1',
            'points' => 'required|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'badge' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        if ($request->hasFile('badge')) {
            if ($achievement->badge_path) {
                Storage::disk('public')->delete($achievement->badge_path);
            }
            $validated['badge_path'] = $request->file('badge')->store('achievements', 'public');
        }

        unset($validated['badge']);

        $achievement->update($validated);

        return redirect()->route('admin.achievements.index')->with('success', 'Achievement updated.');
    }

    public function deleteBadge(Achievement $achievement)
    {
        if ($achievement->badge_path) {
            Storage::disk('public')->delete($achievement->badge_path);
            $achievement->update(['badge_path' => null]);
        }

        return redirect()->route('admin.achievements.edit', $achievement)->with('success', 'Badge image removed.');
    }

    public function destroy(Achievement $achievement)
    {
        if ($achievement->badge_path) {
            Storage::disk('public')->delete($achievement->badge_path);
        }

        $achievement->delete();

        return redirect()->route('admin.achievements.index')->with('success', 'Achievement deleted.');
    }
}
