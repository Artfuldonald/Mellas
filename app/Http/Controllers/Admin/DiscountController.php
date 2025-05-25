<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Discount::latest();

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $discounts = $query->paginate(15)->withQueryString();
        return view('admin.discounts.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $discount = new Discount([
            'is_active' => true, // Default to active
            'type' => Discount::TYPE_FIXED, // Default type
        ]);
        return view('admin.discounts.create', compact('discount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:discounts,code',
            'description' => 'nullable|string|max:1000',
            'type' => ['required', Rule::in([Discount::TYPE_FIXED, Discount::TYPE_PERCENTAGE])],
            'value' => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($request) {
                if ($request->input('type') === Discount::TYPE_PERCENTAGE && $value > 100) {
                    $fail('The percentage value cannot exceed 100.');
                }
            }],
            'min_spend' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        // Ensure nullable integer fields are stored as null if empty
        $validated['max_uses'] = $validated['max_uses'] ?: null;
        $validated['max_uses_per_user'] = $validated['max_uses_per_user'] ?: null;

        Discount::create($validated);

        return redirect()->route('admin.discounts.index')
                         ->with('success', 'Discount created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discount $discount)
    {
        return view('admin.discounts.edit', compact('discount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'code' => ['required','string','max:255', Rule::unique('discounts')->ignore($discount->id)],
            'description' => 'nullable|string|max:1000',
            'type' => ['required', Rule::in([Discount::TYPE_FIXED, Discount::TYPE_PERCENTAGE])],
            'value' => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($request) {
                if ($request->input('type') === Discount::TYPE_PERCENTAGE && $value > 100) {
                    $fail('The percentage value cannot exceed 100.');
                }
            }],
            'min_spend' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['max_uses'] = $validated['max_uses'] ?: null;
        $validated['max_uses_per_user'] = $validated['max_uses_per_user'] ?: null;

        // Don't allow admin to directly edit times_used
        unset($validated['times_used']);

        $discount->update($validated);

        return redirect()->route('admin.discounts.index')
                         ->with('success', 'Discount updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        try {
            // Consider if you need checks before deleting (e.g., if used in past orders)
            $discount->delete();
            return redirect()->route('admin.discounts.index')
                             ->with('success', 'Discount deleted successfully.');
        } catch (\Exception $e) {
             // Log::error("Error deleting discount {$discount->id}: " . $e->getMessage());
             return back()->with('error', 'Failed to delete discount.');
        }
    }
}