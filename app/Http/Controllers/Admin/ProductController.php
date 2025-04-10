<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;          
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Storage; 
use Illuminate\Validation\Rule;   
use App\Models\Attribute;  
use Illuminate\Support\Js;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start query, eager load categories for display/filtering efficiency
        $query = Product::with('categories')->latest(); // Default sort: newest first

        // --- Filtering Example (Adapt as needed) ---
        if ($request->filled('search')) {
             $searchTerm = $request->input('search');
             $query->where(function ($q) use ($searchTerm) {
                 $q->where('name', 'like', "%{$searchTerm}%")
                   ->orWhere('sku', 'like', "%{$searchTerm}%");
             });
        }
        if ($request->filled('category_id')) {
             $categoryId = $request->input('category_id');
             $query->whereHas('categories', function ($q) use ($categoryId) {
                 $q->where('categories.id', $categoryId);
             });
        }
        // --- End Filtering ---

        $products = $query->paginate(15)->withQueryString(); // Paginate and preserve filter parameters
        $categories = Category::orderBy('name')->get(); // For filter dropdown

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $product = new Product();
        // Fetch all attributes with their values for the form selector
        $allAttributes = Attribute::with('values')->orderBy('name')->get(); // <-- ADD THIS LINE
        // Pass the new variable to the view
        return view('admin.products.create', compact('categories', 'product', 'allAttributes')); // <-- ADD 'allAttributes'
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // --- Validation ---
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:products,name', // Name must be unique for auto-slug
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0|gt:price', // Optional: ensure compare > price
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku', // SKU should be unique if provided
            'quantity' => 'required|integer|min:0',
            // Slugs are auto-generated, no need to validate input here
            // Booleans are handled below
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|max:10',
            'dimensions' => 'nullable|string|max:100',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048', // Allow webp, check max size
            'videos' => 'nullable|array',
            'videos.*' => 'mimes:mp4,mov,avi,webm|max:100000', // Check max size (100MB)
             // Assuming these are indexed arrays corresponding to the videos array
            'video_titles.*' => 'nullable|string|max:255',
            'video_descriptions.*' => 'nullable|string',
        ]);

        // --- Prepare Data for Creation ---
        $dataToCreate = $validatedData;

        // Auto-generate Slug
        $dataToCreate['slug'] = Str::slug($validatedData['name']);

        // Handle Boolean Checkboxes (Set to true if present in request, false otherwise)
        $dataToCreate['is_active'] = $request->has('is_active');
        $dataToCreate['is_featured'] = $request->has('is_featured');

        // Remove fields handled separately
        unset($dataToCreate['categories'], $dataToCreate['images'], $dataToCreate['videos']);
        unset($dataToCreate['video_titles'], $dataToCreate['video_descriptions']);

        // --- Create Product ---
        try {
            $product = Product::create($dataToCreate);
        } catch (\Exception $e) {
            Log::error("Error creating product: " . $e->getMessage());
            return back()->with('error', 'Failed to create product. Please check the logs.')->withInput();
        }


        // --- Handle Relationships and Files ---

        // Sync Categories
        if ($request->filled('categories')) {
            $product->categories()->sync($request->input('categories'));
        }

        // Handle Images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                try {
                    $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                    // Store in 'public/product-images' - ensure storage:link is run
                    $path = $image->storeAs('product-images', $filename, 'public');

                    $product->images()->create([
                        'path' => $path,
                        'alt_text' => $product->name . ' image ' . ($index + 1), // Basic alt text
                        'sort_order' => $index,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to upload image for product {$product->id}: " . $e->getMessage());
                    // Optionally notify user, but continue processing other images/videos
                }
            }
        }

        // Handle Videos
        if ($request->hasFile('videos')) {
             foreach ($request->file('videos') as $index => $video) {
                 try {
                     $filename = Str::uuid() . '.' . $video->getClientOriginalExtension();
                     // Store in 'public/product-videos' - ensure storage:link is run
                     $path = $video->storeAs('product-videos', $filename, 'public');
                     $thumbnailPath = null; // Placeholder - Thumbnail generation needs separate logic

                     $product->videos()->create([
                         'path' => $path,
                         'title' => $request->input("video_titles.{$index}", null),
                         'description' => $request->input("video_descriptions.{$index}", null),
                         'thumbnail_path' => $thumbnailPath,
                         'sort_order' => $index,
                     ]);
                } catch (\Exception $e) {
                    Log::error("Failed to upload video for product {$product->id}: " . $e->getMessage());
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }


    /**
     * Display the specified resource. (Not usually needed for admin)
     */
    public function show(Product $product)
    {
        return redirect()->route('admin.products.edit', $product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        // Ensure attributes and variants (with their values) are loaded
        $product->load('categories', 'images', 'videos', 'attributes', 'variants.attributeValues'); // <-- ADD 'attributes', 'variants.attributeValues'
        // Fetch all attributes for the selector
        $allAttributes = Attribute::with('values')->orderBy('name')->get(); // <-- ADD THIS LINE
        // Pass the new variable to the view
        return view('admin.products.edit', compact('product', 'categories', 'allAttributes')); // <-- ADD 'allAttributes'
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // --- Validation ---
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->id), // Check uniqueness excluding current product
            ],
            // Allow optional manual slug update, validate if provided
            'slug' => [
                'nullable', // Allow it to be empty
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->id), // Must be unique if provided
                 // Basic slug format validation (no spaces, lowercase, etc.)
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0|gt:price',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                 Rule::unique('products')->ignore($product->id), // Must be unique if provided
            ],
            'quantity' => 'required|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|max:10',
            'dimensions' => 'nullable|string|max:100',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
             // File handling during update is more complex (delete old, add new)
             // Validation here might check for *new* uploads if your form supports adding more
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'videos' => 'nullable|array',
            'videos.*' => 'mimes:mp4,mov,avi,webm|max:100000',
            'video_titles.*' => 'nullable|string|max:255',
            'video_descriptions.*' => 'nullable|string',
             // Add input fields in the form to mark existing images/videos for deletion
             'delete_images' => 'nullable|array',
             'delete_images.*' => 'integer|exists:product_images,id', // Validate IDs to delete
             'delete_videos' => 'nullable|array',
             'delete_videos.*' => 'integer|exists:product_videos,id',
        ]);

        // --- Prepare Data for Update ---
        $dataToUpdate = $validatedData;

        // Handle Slug: Use provided slug if valid, otherwise keep the old one
        // (We don't auto-regenerate on update by default to prevent breaking existing URLs)
        if (empty($dataToUpdate['slug'])) {
             // If slug input is empty, keep the existing slug
             // OR optionally: regenerate if name changed significantly? Less common.
            // if ($product->name !== $dataToUpdate['name']) {
            //     $dataToUpdate['slug'] = Str::slug($dataToUpdate['name']);
            // } else {
                unset($dataToUpdate['slug']); // Remove from update array if not provided
            // }
        } else {
            // Use the validated slug provided by the user
            $dataToUpdate['slug'] = $validatedData['slug'];
        }


        // Handle Boolean Checkboxes
        $dataToUpdate['is_active'] = $request->has('is_active');
        $dataToUpdate['is_featured'] = $request->has('is_featured');

        // Remove fields handled separately
        unset($dataToUpdate['categories'], $dataToUpdate['images'], $dataToUpdate['videos']);
        unset($dataToUpdate['video_titles'], $dataToUpdate['video_descriptions']);
        unset($dataToUpdate['delete_images'], $dataToUpdate['delete_videos']);


        // --- Update Product ---
        try {
            $product->update($dataToUpdate);
        } catch (\Exception $e) {
             Log::error("Error updating product {$product->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to update product. Please check the logs.')->withInput();
        }


        // --- Handle Relationships and Files ---

        // Sync Categories (pass empty array if none selected to remove all)
        $product->categories()->sync($request->input('categories', []));

        // --- Handle File Deletions (Based on 'delete_images'/'delete_videos' input) ---
        if ($request->filled('delete_images')) {
            $imagesToDelete = $product->images()->whereIn('id', $request->input('delete_images'))->get();
            foreach ($imagesToDelete as $image) {
                try {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                } catch (\Exception $e) {
                     Log::error("Failed to delete image {$image->id} for product {$product->id}: " . $e->getMessage());
                }
            }
        }
         if ($request->filled('delete_videos')) {
            $videosToDelete = $product->videos()->whereIn('id', $request->input('delete_videos'))->get();
            foreach ($videosToDelete as $video) {
                try {
                    Storage::disk('public')->delete($video->path);
                    // Optionally delete thumbnail too: Storage::disk('public')->delete($video->thumbnail_path);
                    $video->delete();
                } catch (\Exception $e) {
                    Log::error("Failed to delete video {$video->id} for product {$product->id}: " . $e->getMessage());
                }
            }
        }

        // --- Handle NEW File Uploads (Similar to store method) ---
        // (Assuming your edit form has inputs for NEW images/videos)
        if ($request->hasFile('images')) {
             // Get max sort_order for appending new images correctly
             $maxSortOrder = $product->images()->max('sort_order') ?? -1;
            foreach ($request->file('images') as $index => $image) {
                try {
                    $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('product-images', $filename, 'public');
                    $product->images()->create([
                        'path' => $path,
                        'alt_text' => $product->name . ' new image ' . ($index + 1),
                        'sort_order' => $maxSortOrder + 1 + $index, // Append after existing
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to upload NEW image for product {$product->id}: " . $e->getMessage());
                }
            }
        }
        // Add similar logic for handling NEW video uploads if needed


        return redirect()->route('admin.products.index')
               ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            // --- Delete Associated Files FIRST ---
            // Delete Images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
                // $image->delete(); // This will happen via cascade or observer below
            }
            // Delete Videos
             foreach ($product->videos as $video) {
                Storage::disk('public')->delete($video->path);
                if ($video->thumbnail_path) {
                    Storage::disk('public')->delete($video->thumbnail_path);
                }
                // $video->delete(); // This will happen via cascade or observer below
            }

            // --- Delete Product Record ---
            // This should trigger deletion of related images/videos/category links
            // IF you have cascade deletes set up in migrations OR use Model Observers.
            // If not, you'd manually delete relationships here BEFORE deleting the product.
            // E.g., $product->categories()->detach(); $product->images()->delete(); etc.
            $product->delete();

            return redirect()->route('admin.products.index')
                   ->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
            Log::error("Error deleting product {$product->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to delete product. It might be associated with other records.');
        }
    }
}