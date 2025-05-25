<?php

namespace App\Http\Controllers\Admin;

use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TaxRate::latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $taxRates = $query->paginate(15)->withQueryString();
        return view('admin.tax-rates.index', compact('taxRates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $taxRate = new TaxRate(['is_active' => true]); // Default new rate to active
        return view('admin.tax-rates.create', compact('taxRate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tax_rates,name',
            // Validate rate as numeric, non-negative, store as decimal
            'rate_percent' => 'required|numeric|min:0|max:100', // Input as percentage
            'priority' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
            // 'apply_to_shipping' => 'sometimes|boolean',
        ]);

        // Convert percentage input to decimal for storage
        $validated['rate'] = $validated['rate_percent'] / 100;
        $validated['is_active'] = $request->boolean('is_active');
        // $validated['apply_to_shipping'] = $request->boolean('apply_to_shipping');

        TaxRate::create($validated);

        return redirect()->route('admin.tax-rates.index')
                         ->with('success', 'Tax rate created successfully.');$validated = $request->validate([
                            'name' => 'required|string|max:255|unique:tax_rates,name',
                            // Validate rate as numeric, non-negative, store as decimal
                            'rate_percent' => 'required|numeric|min:0|max:100', // Input as percentage
                            'priority' => 'required|integer|min:1',
                            'is_active' => 'sometimes|boolean',
                            // 'apply_to_shipping' => 'sometimes|boolean',
                        ]);
                
                        // Convert percentage input to decimal for storage
                        $validated['rate'] = $validated['rate_percent'] / 100;
                        $validated['is_active'] = $request->boolean('is_active');
                        // $validated['apply_to_shipping'] = $request->boolean('apply_to_shipping');
                
                        TaxRate::create($validated);
                
                        return redirect()->route('admin.tax-rates.index')
                                         ->with('success', 'Tax rate created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TaxRate $taxRate)
    {
        return redirect()->route('admin.tax-rates.edit', $taxRate);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaxRate $taxRate)
    {
        return view('admin.tax-rates.edit', compact('taxRate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255', Rule::unique('tax_rates')->ignore($taxRate->id)],
            'rate_percent' => 'required|numeric|min:0|max:100', // Input as percentage
            'priority' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
            // 'apply_to_shipping' => 'sometimes|boolean',
        ]);

        $validated['rate'] = $validated['rate_percent'] / 100; // Convert percentage
        $validated['is_active'] = $request->boolean('is_active');
        // $validated['apply_to_shipping'] = $request->boolean('apply_to_shipping');

        $taxRate->update($validated);

        return redirect()->route('admin.tax-rates.index')
                         ->with('success', 'Tax rate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaxRate $taxRate)
    {
        try {
            // Add checks here later if tax rates are linked to orders etc.
            $taxRate->delete();
            return redirect()->route('admin.tax-rates.index')
                             ->with('success', 'Tax rate deleted successfully.');
        } catch (\Exception $e) {
             // Log::error("Error deleting tax rate {$taxRate->id}: " . $e->getMessage());
             return back()->with('error', 'Failed to delete tax rate. It might be in use.');
        }
    }
}