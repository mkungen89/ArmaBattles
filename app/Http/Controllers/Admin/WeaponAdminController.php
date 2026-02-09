<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Weapon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WeaponAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Weapon::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('has_image')) {
            if ($request->has_image === 'yes') {
                $query->whereNotNull('image_path');
            } else {
                $query->whereNull('image_path');
            }
        }

        $weapons = $query->orderBy('name')->paginate(25);

        // Get counts for stats
        $totalWeapons = Weapon::count();
        $withImages = Weapon::whereNotNull('image_path')->count();
        $withoutImages = $totalWeapons - $withImages;

        return view('admin.weapons.index', compact('weapons', 'totalWeapons', 'withImages', 'withoutImages'));
    }

    public function create()
    {
        return view('admin.weapons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:weapons,name',
            'display_name' => 'nullable|string|max:255',
            'weapon_type' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
        ]);

        $weapon = new Weapon();
        $weapon->name = $validated['name'];
        $weapon->display_name = $validated['display_name'] ?? $validated['name'];
        $weapon->weapon_type = $validated['weapon_type'] ?? null;
        $weapon->category = $validated['category'] ?? null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('weapons', 's3');
            $weapon->image_path = $path;
        }

        $weapon->save();

        return redirect()->route('admin.weapons.index')
            ->with('success', 'Weapon created successfully.');
    }

    public function edit(Weapon $weapon)
    {
        return view('admin.weapons.edit', compact('weapon'));
    }

    public function update(Request $request, Weapon $weapon)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:weapons,name,' . $weapon->id,
            'display_name' => 'nullable|string|max:255',
            'weapon_type' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
        ]);

        $weapon->name = $validated['name'];
        $weapon->display_name = $validated['display_name'] ?? $validated['name'];
        $weapon->weapon_type = $validated['weapon_type'] ?? null;
        $weapon->category = $validated['category'] ?? null;

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($weapon->image_path) {
                Storage::disk('s3')->delete($weapon->image_path);
            }
            $path = $request->file('image')->store('weapons', 's3');
            $weapon->image_path = $path;
        }

        $weapon->save();

        return redirect()->route('admin.weapons.index')
            ->with('success', 'Weapon updated successfully.');
    }

    public function destroy(Weapon $weapon)
    {
        // Delete image if exists
        if ($weapon->image_path) {
            Storage::disk('s3')->delete($weapon->image_path);
        }

        $weapon->delete();

        return redirect()->route('admin.weapons.index')
            ->with('success', 'Weapon deleted successfully.');
    }

    public function deleteImage(Weapon $weapon)
    {
        if ($weapon->image_path) {
            Storage::disk('s3')->delete($weapon->image_path);
            $weapon->image_path = null;
            $weapon->save();
        }

        return redirect()->route('admin.weapons.edit', $weapon)
            ->with('success', 'Image removed successfully.');
    }

    /**
     * Sync weapons from kill events - creates entries for weapons that don't exist
     */
    public function syncFromKills()
    {
        $weaponNames = DB::table('player_kills')
            ->select('weapon_name')
            ->whereNotNull('weapon_name')
            ->where('weapon_name', '!=', '')
            ->distinct()
            ->pluck('weapon_name');

        $created = 0;
        foreach ($weaponNames as $name) {
            $exists = Weapon::where('name', $name)->exists();
            if (!$exists) {
                Weapon::create([
                    'name' => $name,
                    'display_name' => $name,
                ]);
                $created++;
            }
        }

        return redirect()->route('admin.weapons.index')
            ->with('success', "Synced weapons from kills. Created {$created} new weapons.");
    }
}
