<?php

namespace App\Http\Controllers\Admin; // Note the Admin namespace

use App\Http\Controllers\Controller; // Base controller
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // For file handling
use Illuminate\Validation\Rule; // For unique validation on update

class AdminBrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $brands = Brand::orderBy('name')->withCount('products')->paginate(15);     
         return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024', // Max 1MB
            'is_active' => 'nullable|boolean',
        ]);

        $brand = new Brand();
        $brand->name = $validated['name'];
        $brand->slug = Str::slug($validated['name']);
        $brand->description = $validated['description'];
        $brand->is_active = $request->has('is_active'); // Or $validated['is_active'] ?? false;

        if ($request->hasFile('logo')) {
            $brand->logo_path = $request->file('logo')->store('brands/logos', 'public');
        }

        $brand->save();

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        // Typically not needed for simple CRUD, redirect to edit or index
        return redirect()->route('admin.brands.edit', $brand);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255', Rule::unique('brands')->ignore($brand->id)],
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
            'is_active' => 'nullable|boolean',
            'remove_logo' => 'nullable|boolean',
        ]);

        $brand->name = $validated['name'];
        $brand->slug = Str::slug($validated['name']); // Update slug if name changes
        $brand->description = $validated['description'];
        $brand->is_active = $request->has('is_active');

        if ($request->boolean('remove_logo') && $brand->logo_path) {
            Storage::disk('public')->delete($brand->logo_path);
            $brand->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
            }
            $brand->logo_path = $request->file('logo')->store('brands/logos', 'public');
        }

        $brand->save();

        return redirect()->route('admin.brands.index')->with('success', 'Brand "' . $brand->name . '" updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        // Optional: Check if brand has associated products and prevent deletion or reassign products
        if ($brand->products()->count() > 0) {
            return back()->with('error', 'Cannot delete brand. It has associated products. Please reassign products first.');
        }

        if ($brand->logo_path) {
            Storage::disk('public')->delete($brand->logo_path);
        }
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }
}