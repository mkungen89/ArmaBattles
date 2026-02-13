<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RankLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RankLogoController extends Controller
{
    use \App\Traits\LogsAdminActions;

    /**
     * Display all ranks grouped by era
     */
    public function index()
    {
        $ranks = RankLogo::orderBy('rank')->get()->groupBy('era');

        $eraNames = [
            1 => 'ERA I: Enlisted Ranks',
            2 => 'ERA II: Non-Commissioned Officers',
            3 => 'ERA III: Senior NCOs',
            4 => 'ERA IV: Commissioned Officers',
            5 => 'ERA V: Senior Officers',
            6 => 'ERA VI: Marshals',
            7 => 'ERA VII: War Commanders',
            8 => 'ERA VIII: War Chiefs',
            9 => 'ERA IX: War Masters',
            10 => 'ERA X: War Sovereigns',
        ];

        return view('admin.ranks.index', compact('ranks', 'eraNames'));
    }

    /**
     * Show form to edit a specific rank
     */
    public function edit(RankLogo $rank)
    {
        return view('admin.ranks.edit', compact('rank'));
    }

    /**
     * Update rank details (name, color, logo)
     */
    public function update(Request $request, RankLogo $rank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $rank->name = $validated['name'];
        $rank->color = $validated['color'];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($rank->logo_path) {
                Storage::disk('s3')->delete($rank->logo_path);
            }

            $path = $request->file('logo')->store('rank_logos', 's3');
            $rank->logo_path = $path;
        }

        $rank->save();

        $this->logAction('rank.updated', 'RankLogo', $rank->id, [
            'name' => $rank->name,
            'color' => $rank->color,
            'era' => $rank->era,
        ]);

        return redirect()->route('admin.ranks.index')->with('success', 'Rank updated successfully!');
    }

    /**
     * Delete rank logo
     */
    public function deleteLogo(RankLogo $rank)
    {
        if ($rank->logo_path) {
            Storage::disk('s3')->delete($rank->logo_path);
            $rank->logo_path = null;
            $rank->save();

            $this->logAction('rank.logo-deleted', 'RankLogo', $rank->id, [
                'name' => $rank->name,
            ]);
        }

        return back()->with('success', 'Rank logo deleted successfully!');
    }
}
