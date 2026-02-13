<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Traits\LogsAdminActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleAdminController extends Controller
{
    use LogsAdminActions;
    public function index(Request $request)
    {
        $query = Vehicle::query();

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

        $vehicles = $query->orderBy('name')->paginate(25);

        $totalVehicles = Vehicle::count();
        $withImages = Vehicle::whereNotNull('image_path')->count();
        $withoutImages = $totalVehicles - $withImages;

        return view('admin.vehicles.index', compact('vehicles', 'totalVehicles', 'withImages', 'withoutImages'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vehicles,name',
            'display_name' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|in:car,truck,apc,ifv,tank,helicopter,boat,other',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
        ]);

        $vehicle = new Vehicle;
        $vehicle->name = $validated['name'];
        $vehicle->display_name = $validated['display_name'] ?? $validated['name'];
        $vehicle->vehicle_type = $validated['vehicle_type'] ?? null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('vehicles', 's3');
            $vehicle->image_path = $path;
        }

        $vehicle->save();

        $this->logAction('vehicle.created', 'Vehicle', $vehicle->id, [
            'name' => $vehicle->name,
            'vehicle_type' => $vehicle->vehicle_type,
        ]);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle created successfully.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('admin.vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vehicles,name,'.$vehicle->id,
            'display_name' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|in:car,truck,apc,ifv,tank,helicopter,boat,other',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
        ]);

        $vehicle->name = $validated['name'];
        $vehicle->display_name = $validated['display_name'] ?? $validated['name'];
        $vehicle->vehicle_type = $validated['vehicle_type'] ?? null;

        if ($request->hasFile('image')) {
            if ($vehicle->image_path) {
                Storage::disk('s3')->delete($vehicle->image_path);
            }
            $path = $request->file('image')->store('vehicles', 's3');
            $vehicle->image_path = $path;
        }

        $vehicle->save();

        $this->logAction('vehicle.updated', 'Vehicle', $vehicle->id, [
            'name' => $vehicle->name,
            'vehicle_type' => $vehicle->vehicle_type,
        ]);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicleName = $vehicle->name;
        $vehicleId = $vehicle->id;

        if ($vehicle->image_path) {
            Storage::disk('s3')->delete($vehicle->image_path);
        }

        $vehicle->delete();

        $this->logAction('vehicle.deleted', 'Vehicle', $vehicleId, [
            'name' => $vehicleName,
        ]);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }

    public function deleteImage(Vehicle $vehicle)
    {
        if ($vehicle->image_path) {
            Storage::disk('s3')->delete($vehicle->image_path);
            $vehicle->image_path = null;
            $vehicle->save();

            $this->logAction('vehicle.image-deleted', 'Vehicle', $vehicle->id, [
                'name' => $vehicle->name,
            ]);
        }

        return redirect()->route('admin.vehicles.edit', $vehicle)
            ->with('success', 'Image removed successfully.');
    }

    /**
     * Sync vehicles from player_distance JSON data
     */
    public function syncFromDistanceData()
    {
        $rows = DB::table('player_distance')
            ->whereNotNull('vehicles')
            ->whereRaw("vehicles::text != '[]'")
            ->select('vehicles')
            ->get();

        $vehicleNames = collect();
        foreach ($rows as $row) {
            $vehicles = json_decode($row->vehicles, true);
            if (is_array($vehicles)) {
                foreach ($vehicles as $v) {
                    $name = $v['vehicle'] ?? $v['name'] ?? null;
                    if ($name) {
                        $vehicleNames->push($name);
                    }
                }
            }
        }

        $uniqueNames = $vehicleNames->unique()->values();

        $created = 0;
        foreach ($uniqueNames as $name) {
            $exists = Vehicle::where('name', $name)->exists();
            if (! $exists) {
                Vehicle::create([
                    'name' => $name,
                    'display_name' => $name,
                ]);
                $created++;
            }
        }

        $this->logAction('vehicle.synced', null, null, [
            'vehicles_created' => $created,
            'total_unique' => $uniqueNames->count(),
        ]);

        return redirect()->route('admin.vehicles.index')
            ->with('success', "Synced vehicles from distance data. Created {$created} new vehicles.");
    }
}
