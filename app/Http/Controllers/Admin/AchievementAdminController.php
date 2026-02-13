<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AchievementAdminController extends Controller
{
    use \App\Traits\LogsAdminActions;

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
        $presetBadges = $this->getPresetBadges();

        return view('admin.achievements.create', compact('categories', 'presetBadges'));
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
            'badge_svg_url' => 'nullable|url|max:500',
            'preset_badge' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        if ($request->hasFile('badge')) {
            $validated['badge_path'] = $request->file('badge')->store('achievements', 's3');
        }

        unset($validated['badge']);

        $achievement = Achievement::create($validated);

        $this->logAction('achievement.created', 'Achievement', $achievement->id, [
            'name' => $achievement->name,
            'category' => $achievement->category,
            'points' => $achievement->points,
        ]);

        return redirect()->route('admin.achievements.index')->with('success', 'Achievement created.');
    }

    public function edit(Achievement $achievement)
    {
        $achievement->loadCount('players');
        $categories = Achievement::distinct()->pluck('category')->filter()->sort()->values();
        $presetBadges = $this->getPresetBadges();

        return view('admin.achievements.edit', compact('achievement', 'categories', 'presetBadges'));
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
            'badge_svg_url' => 'nullable|url|max:500',
            'preset_badge' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        if ($request->hasFile('badge')) {
            if ($achievement->badge_path) {
                Storage::disk('s3')->delete($achievement->badge_path);
            }
            $validated['badge_path'] = $request->file('badge')->store('achievements', 's3');
        }

        unset($validated['badge']);

        $achievement->update($validated);

        $this->logAction('achievement.updated', 'Achievement', $achievement->id, [
            'name' => $achievement->name,
            'category' => $achievement->category,
            'points' => $achievement->points,
        ]);

        return redirect()->route('admin.achievements.index')->with('success', 'Achievement updated.');
    }

    public function deleteBadge(Achievement $achievement)
    {
        if ($achievement->badge_path) {
            Storage::disk('s3')->delete($achievement->badge_path);
            $achievement->update(['badge_path' => null]);

            $this->logAction('achievement.badge-deleted', 'Achievement', $achievement->id, [
                'name' => $achievement->name,
            ]);
        }

        return redirect()->route('admin.achievements.edit', $achievement)->with('success', 'Badge image removed.');
    }

    public function destroy(Achievement $achievement)
    {
        if ($achievement->badge_path) {
            Storage::disk('s3')->delete($achievement->badge_path);
        }

        $achievementName = $achievement->name;
        $achievementId = $achievement->id;

        $achievement->delete();

        $this->logAction('achievement.deleted', 'Achievement', $achievementId, [
            'name' => $achievementName,
            'category' => $achievement->category,
        ]);

        return redirect()->route('admin.achievements.index')->with('success', 'Achievement deleted.');
    }

    /**
     * Get list of preset badge files from public/images/Achivements
     */
    private function getPresetBadges(): array
    {
        $path = public_path('images/Achivements');

        if (!is_dir($path)) {
            return [];
        }

        $files = array_diff(scandir($path), ['.', '..']);

        return array_values(array_filter($files, function ($file) use ($path) {
            $filePath = $path.'/'.$file;
            return is_file($filePath) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['svg', 'png', 'jpg', 'jpeg', 'gif', 'webp']);
        }));
    }
}
