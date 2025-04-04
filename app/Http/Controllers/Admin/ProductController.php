<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       
        // Validate product data
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:products',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'compare_at_price' => 'nullable|numeric|min:0',
        'cost_price' => 'nullable|numeric|min:0',
        'sku' => 'nullable|string|max:100|unique:products',
        'quantity' => 'required|integer|min:0',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string',
        'weight' => 'nullable|numeric|min:0',
        'weight_unit' => 'nullable|string|max:10',
        'dimensions' => 'nullable|string|max:50',
        // Image validation
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        // Video validation
        'videos.*' => 'nullable|mimes:mp4,mov,avi|max:100000',
        'video_titles.*' => 'nullable|string|max:255',
        'video_descriptions.*' => 'nullable|string',
    ]);

    // Create the product
    $product = Product::create($validatedData);

    // Handle images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('product-images', 'public');
            
            $product->images()->create([
                'path' => $path,
                'alt' => $product->name . ' image ' . ($index + 1),
                'position' => $index,
            ]);
        }
    }

    // Handle videos
    if ($request->hasFile('videos')) {
        foreach ($request->file('videos') as $index => $video) {
            $path = $video->store('product-videos', 'public');
            
            // Optional: generate thumbnail here
            $thumbnailPath = null;
            
            $product->videos()->create([
                'path' => $path,
                'title' => $request->video_titles[$index] ?? null,
                'description' => $request->video_descriptions[$index] ?? null,
                'thumbnail_path' => $thumbnailPath,
                'position' => $index,
                'is_featured' => $index === 0, // First video is featured by default
            ]);
        }
    }

    // Handle categories if needed
    if ($request->has('categories')) {
        $product->categories()->sync($request->categories);
    }

    return redirect()->route('admin.products.index')
        ->with('success', 'Product created successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}