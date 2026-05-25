<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::with('service')
            ->where('is_active', true)
            ->orderByRaw("CASE tier WHEN 'basic' THEN 1 WHEN 'standard' THEN 2 WHEN 'express' THEN 3 ELSE 4 END")
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Packages loaded successfully',
            'data' => $packages,
        ]);
    }

    public function show($id)
    {
        $package = Package::with('service')
            ->where('is_active', true)
            ->find($id);

        if (! $package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Package loaded successfully',
            'data' => $package,
        ]);
    }

    public function packagesByService($serviceId)
    {
        $packages = Package::where('service_id', $serviceId)
            ->where('is_active', true)
            ->orderByRaw("CASE tier WHEN 'basic' THEN 1 WHEN 'standard' THEN 2 WHEN 'express' THEN 3 ELSE 4 END")
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Service packages loaded successfully',
            'data' => $packages,
        ]);
    }

    public function adminIndex(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can view all packages',
            ], 403);
        }

        $packages = Package::with('service')
            ->orderBy('service_id')
            ->orderByRaw("CASE tier WHEN 'basic' THEN 1 WHEN 'standard' THEN 2 WHEN 'express' THEN 3 ELSE 4 END")
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'All packages loaded successfully',
            'data' => $packages,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can create package',
            ], 403);
        }

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'tier' => 'required|string|in:basic,standard,express',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:packages,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'turnaround_hours' => 'required|integer|min:1',
            'delivery_days' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = $validated['slug'] ?? Str::slug(
            $validated['name'] . '-' . $validated['tier'] . '-' . $validated['service_id']
        );

        $package = Package::create([
            'service_id' => $validated['service_id'],
            'tier' => $validated['tier'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'turnaround_hours' => $validated['turnaround_hours'],
            'delivery_days' => $validated['delivery_days'] ?? max(1, (int) ceil($validated['turnaround_hours'] / 24)),
            'features' => $validated['features'] ?? [],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Package created successfully',
            'data' => $package->load('service'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can update package',
            ], 403);
        }

        $package = Package::find($id);

        if (! $package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found',
            ], 404);
        }

        $validated = $request->validate([
            'service_id' => 'sometimes|required|exists:services,id',
            'tier' => 'sometimes|required|string|in:basic,standard,express',
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:packages,slug,' . $package->id,
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'turnaround_hours' => 'nullable|integer|min:1',
            'delivery_days' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['name']) && empty($validated['slug'])) {
            $tier = $validated['tier'] ?? $package->tier;
            $serviceId = $validated['service_id'] ?? $package->service_id;

            $validated['slug'] = Str::slug($validated['name'] . '-' . $tier . '-' . $serviceId);
        }

        if (isset($validated['turnaround_hours']) && ! isset($validated['delivery_days'])) {
            $validated['delivery_days'] = max(1, (int) ceil($validated['turnaround_hours'] / 24));
        }

        $package->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Package updated successfully',
            'data' => $package->fresh('service'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can delete package',
            ], 403);
        }

        $package = Package::find($id);

        if (! $package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found',
            ], 404);
        }

        $package->delete();

        return response()->json([
            'status' => true,
            'message' => 'Package deleted successfully',
        ]);
    }
}