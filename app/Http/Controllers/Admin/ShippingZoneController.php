<?php

namespace App\Http\Controllers\Admin;

use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class ShippingZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Eager load the count of associated rates for display
        $query = ShippingZone::withCount('shippingRates')->latest();

        // Basic search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $shippingZones = $query->paginate(15)->withQueryString();
        // Pass the data to the index view (we'll create this next)
        return view('admin.shipping-zones.index', compact('shippingZones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $shippingZone = new ShippingZone();
        
        return view('admin.shipping-zones.create', compact('shippingZone'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shipping_zones,name',
            'is_active' => 'sometimes|boolean', // Checkbox might not be sent if unchecked
        ]);

        // Ensure boolean value is correctly stored (0 or 1)
        $validated['is_active'] = $request->boolean('is_active');

        ShippingZone::create($validated);

        return redirect()->route('admin.shipping-zones.index')
                         ->with('success', 'Shipping zone created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ShippingZone $shippingZone)
    {
        // Eager load the rates for this specific zone
        $shippingZone->load(['shippingRates' => function ($query) {
            $query->orderBy('cost'); // Order rates by cost, for example
        }]);
        // Pass the zone (with its loaded rates) to the show view
        return view('admin.shipping-zones.show', compact('shippingZone'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShippingZone $shippingZone)
    {
        return view('admin.shipping-zones.edit', compact('shippingZone'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShippingZone $shippingZone)
    {
        $validated = $request->validate([
            // Ensure name is unique, ignoring the current zone's ID
            'name' => ['required','string','max:255', Rule::unique('shipping_zones')->ignore($shippingZone->id)],
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $shippingZone->update($validated);

        // Redirect back to the zone's show page after updating
        return redirect()->route('admin.shipping-zones.show', $shippingZone)
                         ->with('success', 'Shipping zone updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShippingZone $shippingZone)
    {
        try {
            // Database cascade constraint should handle deleting associated rates
            $shippingZone->delete();
            return redirect()->route('admin.shipping-zones.index')
                             ->with('success', 'Shipping zone deleted successfully.');
        } catch (\Exception $e) {
             // Log::error("Error deleting shipping zone {$shippingZone->id}: " . $e->getMessage());
             // Provide feedback if deletion fails
             return back()->with('error', 'Failed to delete shipping zone. Please check logs or ensure it\'s not restricted.');
        }
    }

    /**
     * Show the form for creating a new shipping rate for the given zone.
     */
    public function createRate(ShippingZone $shippingZone)
    {
        // Pass the parent zone to the rate creation view
        return view('admin.shipping-rates.create', compact('shippingZone'));
    }

    /**
     * Store a newly created shipping rate associated with the given zone.
     */
    public function storeRate(Request $request, ShippingZone $shippingZone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0', // Ensure cost is non-negative
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string|max:1000',
            // Add validation for other criteria if you implement them (e.g., min_order_subtotal)
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Use the relationship to create the rate, automatically setting shipping_zone_id
        $shippingZone->shippingRates()->create($validated);

        // Redirect back to the parent zone's show page
        return redirect()->route('admin.shipping-zones.show', $shippingZone)
                         ->with('success', 'Shipping rate added successfully.');
    }

     /**
     * Show the form for editing an existing shipping rate within the given zone.
     */
    public function editRate(ShippingZone $shippingZone, ShippingRate $shippingRate)
    {
        // Ensure the rate actually belongs to the zone passed in the URL for security/consistency
        if ($shippingRate->shipping_zone_id !== $shippingZone->id) {
            abort(404); // Or redirect with an error
        }
        // Pass both the zone and the specific rate to the edit view
        return view('admin.shipping-rates.edit', compact('shippingZone', 'shippingRate'));
    }

    /**
     * Update the specified shipping rate.
     */
    public function updateRate(Request $request, ShippingZone $shippingZone, ShippingRate $shippingRate)
    {
        // Verify rate belongs to zone
        if ($shippingRate->shipping_zone_id !== $shippingZone->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string|max:1000',
            // Validate other criteria if needed
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $shippingRate->update($validated);

        return redirect()->route('admin.shipping-zones.show', $shippingZone)
                         ->with('success', 'Shipping rate updated successfully.');
    }

    /**
     * Remove the specified shipping rate from storage.
     */
    public function destroyRate(ShippingZone $shippingZone, ShippingRate $shippingRate)
    {
        // Verify rate belongs to zone
        if ($shippingRate->shipping_zone_id !== $shippingZone->id) {
             abort(404);
         }

        try {
            $shippingRate->delete();
            return redirect()->route('admin.shipping-zones.show', $shippingZone)
                             ->with('success', 'Shipping rate deleted successfully.');
        } catch (\Exception $e) {
            // Log::error("Error deleting shipping rate {$shippingRate->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to delete shipping rate.');
        }
    }
}