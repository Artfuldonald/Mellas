<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Js;
use App\Models\Attribute;  
use Illuminate\Http\Request;
use App\Models\AttributeValue;
use App\Models\ProductVariant;
use App\Models\StockAdjustment; 
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;   
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Str;          
use Illuminate\Support\Facades\Storage; 

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with([
            'categories',
            'images' => fn($q) => $q->orderBy('position')->limit(1),
            // We don't need all variants here if we calculate total stock with a subquery
        ])
        // Calculate total stock:
        // For simple products, use products.quantity
        // For products with variants, sum product_variants.quantity
        ->select('products.*') // Select all columns from products table
        ->selectSub(function ($query) {
            // Subquery to sum variant quantities
            $query->selectRaw('SUM(pv.quantity)')
                  ->from('product_variants as pv')
                  ->whereColumn('pv.product_id', 'products.id');
        }, 'variants_total_quantity')
        ->withCount('variants') // Still useful for knowing if it HAS variants
        ->latest();

        // filtering logic
        if ($request->filled('search')) {
             $searchTerm = $request->input('search');
             $query->where(function ($q) use ($searchTerm) {
                 $q->where('products.name', 'like', "%{$searchTerm}%") // Qualify column name
                   ->orWhere('products.sku', 'like', "%{$searchTerm}%")
                   ->orWhereHas('variants', function ($vq) use ($searchTerm) {
                       $vq->where('sku', 'like', "%{$searchTerm}%");
                   });
             });
        }
        if ($request->filled('category_id')) {
             $categoryId = $request->input('category_id');
             $query->whereHas('categories', function ($q) use ($categoryId) {
                 $q->where('categories.id', $categoryId);
             });
        }

        $products = $query->paginate(15)->withQueryString();

        // Add a 'display_stock' accessor to each product for easier view logic
        $products->each(function ($product) {
            if ($product->variants_count > 0) {
                // If variants_total_quantity is null (no variants found by subquery, though variants_count > 0 is odd)
                // it means something is off, or no variants have quantity. Default to 0.
                $product->display_stock = $product->variants_total_quantity ?? 0;
            } else {
                $product->display_stock = $product->quantity;
            }
        });

        $categories = Category::orderBy('name')->get();
        return view('admin.products.index', compact('products', 'categories'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
    $product = new Product();
    $allAttributes = Attribute::with('values')->orderBy('name')->get();
    $brands = Brand::where('is_active', true)->orderBy('name')->get(); 

    return view('admin.products.create', compact(
        'categories',
        'product',
        'allAttributes',
        'brands' // 
    ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // --- 1. Check raw request data BEFORE validation ---
     //($request->all()); // Uncomment this FIRST, submit, and check output

    // Determine if variants are enabled *from the request*
    $hasVariants = $request->boolean('has_variants'); // Use boolean() helper

    $baseValidationRules = [
        'name' => 'required|string|max:255|unique:products,name',
        'slug' => 'nullable|string|max:255|unique:products,slug',
        'description' => 'nullable|string',
        'brand_id' => 'nullable|integer|exists:brands,id',         
        'short_description' => 'nullable|string|max:500', 
        'price' => 'required|numeric|min:0',
        'compare_at_price' => 'nullable|numeric|min:0|gt:price',
        'cost_price' => 'nullable|numeric|min:0',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string',
        'weight' => 'nullable|numeric|min:0',

        // **** CHANGE VALIDATION RULE HERE ****
        'weight_unit' => [
            'required_with:weight', // Require 'weight_unit' only if 'weight' has a value
            'nullable',             // Allow it to be null/empty if 'weight' is also empty
            'string',
            Rule::in(['kg', 'g', 'lb', 'oz']), // Ensure it's one of the allowed units if provided
        ],
        // **** END CHANGE ****

        'dimensions' => 'nullable|string|max:100',
        'categories' => 'nullable|array',
        'categories.*' => 'exists:categories,id',
        'images' => 'nullable|array',
        'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        'videos' => 'nullable|array',
        'videos.*' => 'mimes:mp4,mov,avi,webm|max:100000',
        'video_titles.*' => 'nullable|string|max:255',
        'video_descriptions.*' => 'nullable|string',
        'has_variants' => 'sometimes|boolean',
    ];

    // --- Conditional Validation ---
    if (!$hasVariants) {
        $baseValidationRules['sku'] = 'required|string|max:100|unique:products,sku';
        $baseValidationRules['quantity'] = 'required|integer|min:0';
    } else {
        $baseValidationRules['product_attributes'] = 'required|array|min:1';
        $baseValidationRules['product_attributes.*'] = 'required|integer|exists:attributes,id';
        $baseValidationRules['variants'] = 'required|array|min:1';
        $baseValidationRules['variants.*.sku'] = ['required','string','max:100','distinct', Rule::unique('product_variants', 'sku')];
        $baseValidationRules['variants.*.price'] = 'required|numeric|min:0';
        $baseValidationRules['variants.*.quantity'] = 'required|integer|min:0';
        $baseValidationRules['variants.*.attribute_value_ids'] = 'required|array|min:1';
        $baseValidationRules['variants.*.attribute_value_ids.*'] = 'required|integer|exists:attribute_values,id';
        $baseValidationRules['attribute_values'] = 'required|array|min:1';
        $baseValidationRules['attribute_values.*'] = 'required|array|min:1';
        $baseValidationRules['attribute_values.*.*'] = 'required|integer|exists:attribute_values,id';
    }

    if ($request->has('specifications')) {
        $baseValidationRules['specifications'] = 'nullable|array';        
    }

    // --- Attempt Validation ---
    try {
        $validatedData = $request->validate($baseValidationRules, [           
            'variants.*.sku.required' => 'The SKU for each variant is required.',            
            'weight_unit.required_with' => 'The weight unit is required when a weight is provided.', 
            'weight_unit.in' => 'Please select a valid weight unit (kg, g, lb, oz).',
            'brand_id.exists' => 'The selected brand is invalid.',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation Failed:', $e->errors());
        return back()->withErrors($e->validator)->withInput(); 
    }
   
    try {
        DB::beginTransaction();

        // --- Prepare Base Product Data ---
        $dataToCreate = collect($validatedData)->except([
            'categories', 'images', 'videos', 'video_titles', 'video_descriptions',
            'product_attributes', 'attribute_values', 'variants', 'has_variants',
            'sku', 'quantity'
        ])->toArray();

        // --- Auto-generate Slug ---
        if (empty($validatedData['slug']) && !empty($validatedData['name'])) {
            $dataToCreate['slug'] = Str::slug($validatedData['name']);
            $count = Product::where('slug', $dataToCreate['slug'])->count();
            if ($count > 0) {
                $dataToCreate['slug'] .= '-' . ($count + 1);
            }
        } else if (!empty($validatedData['slug'])) {
             $dataToCreate['slug'] = $validatedData['slug'];
        }

        // Handle Booleans
        $dataToCreate['is_active'] = $request->boolean('is_active');
        $dataToCreate['is_featured'] = $request->boolean('is_featured');

        // Add simple SKU/Quantity only if NOT hasVariants
        if (!$hasVariants) {
            $dataToCreate['sku'] = $validatedData['sku'] ?? null;
            $dataToCreate['quantity'] = $validatedData['quantity'] ?? 0;
        } else {
            $dataToCreate['sku'] = null;
            $dataToCreate['quantity'] = 0;
        }
        //brand and shortdescription if provided 
        $dataToCreate['brand_id'] = $validatedData['brand_id'] ?? null;
        $dataToCreate['short_description'] = $validatedData['short_description'] ?? null;

        ///specifications process
        $processedSpecifications = [];
        if ($request->filled('spec_keys') && $request->filled('spec_values')) {
            $specKeys = $request->input('spec_keys');
            $specValues = $request->input('spec_values');
            foreach ($specKeys as $index => $key) {
                if (!empty($key) && isset($specValues[$index]) && !empty($specValues[$index])) {
                    // For simple key-value JSON:
                    // $processedSpecifications[Str::slug($key, '_')] = $specValues[$index]; // Store with slugified key
                    // For array of objects JSON:
                    $processedSpecifications[] = ['key' => trim($key), 'value' => trim($specValues[$index])];
                }
            }
        }
        $dataToCreate['specifications'] = !empty($processedSpecifications) ? $processedSpecifications : null;

    // **** ADD LOGIC TO UNSET weight_unit IF EMPTY ****
            // This should be placed *after* validation and *before* Product::create()
            if (array_key_exists('weight_unit', $dataToCreate) && empty($dataToCreate['weight_unit'])) {
                // If weight_unit exists in the validated data (meaning it passed validation,
                // possibly because 'weight' was also empty) but its value is empty/null,
                // remove it from the array we pass to create().
                // This allows the database default ('kg') to be used.
                unset($dataToCreate['weight_unit']);
                Log::debug('Weight unit was empty/null, removing from create data to use DB default.');
            }
            // **** END LOGIC ****

            // --- 3. Check data just before creating the Product model ---
             Log::debug('Data for Product::create:', $dataToCreate);
            // dd($dataToCreate);

            // --- Create Product ---
            $product = Product::create($dataToCreate);
            Log::info("Product base created successfully: ID {$product->id}");

            // --- Handle Relationships & Files ---
            if (!empty($validatedData['categories'])) { // Check if not empty
                $product->categories()->sync($validatedData['categories']);
                Log::info("Synced categories for Product ID: {$product->id}", ['categories' => $validatedData['categories']]);
            } else {
                $product->categories()->detach(); // Detach if empty array submitted
                Log::info("No categories submitted or empty array, detached existing for Product ID: {$product->id}");
            }

            // Handle Images 
            if ($request->hasFile('images')) {
                 Log::info("Processing images for Product ID: {$product->id}");
                 foreach ($request->file('images') as $index => $image) {
                     try {
                         $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                         $path = $image->storeAs('product-images', $filename, 'public');
                         Log::info("Attempting to create image record for Product {$product->id}", ['path' => $path]);
                         $product->images()->create([
                             'path' => $path,
                             'alt' => $product->name . ' image ' . ($index + 1),
                             'position' => $index,
                         ]);
                         Log::info("Successfully created image record for Product {$product->id}");
                     } catch (\Exception $e) {
                         Log::error("Failed to upload/save image record for product {$product->id} at index {$index}: " . $e->getMessage());
                         // Consider throwing $e to rollback transaction? Or log and continue?
                         // throw $e; // If image upload failure should stop the whole process
                     }
                 }
            } else {
                Log::info("No images found in request for Product ID: {$product->id}");
            }

             // Handle Videos
            if ($request->hasFile('videos')) {
                 Log::info("Processing videos for Product ID: {$product->id}");
                 foreach ($request->file('videos') as $index => $video) {
                     try {
                         $filename = Str::uuid() . '.' . $video->getClientOriginalExtension();
                         $path = $video->storeAs('product-videos', $filename, 'public');
                         $thumbnailPath = null;
                         Log::info("Attempting to create video record for Product {$product->id}", ['path' => $path]);
                         $product->videos()->create([
                             'path' => $path,
                             'title' => $request->input("video_titles.{$index}", null),
                             'description' => $request->input("video_descriptions.{$index}", null),
                             'thumbnail_path' => $thumbnailPath,
                             'position' => $index,
                         ]);
                         Log::info("Successfully created video record for Product {$product->id}");
                    } catch (\Exception $e) {
                        Log::error("Failed to upload/save video record for product {$product->id} at index {$index}: " . $e->getMessage());
                        // Consider throwing $e to rollback transaction?
                        // throw $e;
                    }
                }
            } else {
                Log::info("No videos found in request for Product ID: {$product->id}");
            }


            // --- **** Handle Variants **** ---            
            if ($hasVariants && isset($validatedData['variants'])) {
                Log::info("Processing variants for Product ID: {$product->id}");

                // 1. Sync Product Attributes
                if(isset($validatedData['product_attributes'])) {
                    $product->attributes()->sync($validatedData['product_attributes']);
                    Log::info("Synced product attributes", ['attributes' => $validatedData['product_attributes']]);
                }

                // 2. Create Product Variants
                $variantData = $validatedData['variants'];
                // Pre-load values for efficiency
                $allValueIds = collect($variantData)->pluck('attribute_value_ids')->flatten()->unique()->toArray();
                $attributeValues = AttributeValue::whereIn('id', $allValueIds)->get()->keyBy('id');
                Log::debug("Pre-loaded attribute values for variants", ['ids' => $allValueIds]);

                foreach ($variantData as $index => $variantInput) {
                     Log::debug("Processing Variant Index {$index}", $variantInput);

                     if (!isset($variantInput['sku'], $variantInput['price'], $variantInput['quantity'], $variantInput['attribute_value_ids']) || !is_array($variantInput['attribute_value_ids'])) {
                         Log::error("Missing required data or invalid attribute_value_ids for variant at index {$index}", $variantInput);
                         continue;
                     }

                     // *** Generate Variant Name ***
                     $variantNameParts = [];
                     $sortedValueIds = collect($variantInput['attribute_value_ids'])->sort()->values()->all();
                     foreach ($sortedValueIds as $valueId) {
                         if ($value = $attributeValues->get($valueId)) {
                             $variantNameParts[] = $value->value;
                         } else {
                             Log::warning("Could not find AttributeValue model for ID {$valueId} when generating name for variant index {$index}");
                             $variantNameParts[] = '?';
                         }
                     }
                     $variantName = implode(' / ', $variantNameParts);
                     Log::debug("Generated variant name for index {$index}: {$variantName}");
                     // *** END  ***

                     try {
                        $newVariant = $product->variants()->create([
                            'name' => $variantName, // <-- Add generated name
                            'sku' => $variantInput['sku'],
                            'price' => $variantInput['price'],
                            'quantity' => $variantInput['quantity'],
                            'is_active' => true,
                        ]);
                        Log::info("Created Variant ID: {$newVariant->id} with Name: '{$variantName}'");

                        $newVariant->attributeValues()->sync($sortedValueIds);
                        Log::info("Synced attribute values for Variant ID: {$newVariant->id}", ['values' => $sortedValueIds]);

                    } catch (\Exception $e) {
                         Log::error("Error creating/syncing variant for product {$product->id} at index {$index}: " . $e->getMessage(), ['variant_data' => $variantInput, 'generated_name' => $variantName]);
                         throw $e; // Throw to trigger rollback
                    }
                }
            } else {
                 Log::info("No variants to process or hasVariants is false for Product ID: {$product->id}");
            }

            // --- Commit Transaction ---
            DB::commit();
            Log::info("Transaction committed for Product ID: {$product->id}");

            Log::info("Redirecting after successful product creation attempt for Product ID: {$product->id}");
            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            // --- Rollback Transaction ---
            DB::rollBack();
            Log::error("Transaction rolled back due to error during product creation: " . $e->getMessage(), ['exception' => $e]);

            // Check if it was a validation exception (should be caught earlier, but good practice)
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                 return back()->withErrors($e->validator)->withInput();
            }

            // Handle other exceptions (DB errors, etc.)
            return back()->with('error', 'Failed to create product. An unexpected error occurred. Please check logs.')->withInput();
        }
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
        $product->load([
            'categories',
            'brand', 
            'images' => fn($q) => $q->orderBy('position'),
            'videos' => fn($q) => $q->orderBy('position'),
            'attributes',
            'variants.attributeValues.attribute'
        ]);

        $allAttributes = Attribute::with('values')->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'allAttributes',
            'brands' 
        ));
    }
/**
     * Update the form for the specified resource.
     */
    public function update(Request $request, Product $product)
    {
    // --- Determine if variants are enabled *from the request* ---
        // Note: This is tricky for updates. If a product *had* variants, you usually can't easily switch back
        // without deleting existing variants. You might need more complex logic here.
        // For now, assume we mostly update data, not the simple/variant structure itself.
        $hasVariants = $request->boolean('has_variants', $product->variants()->exists()); // Default to existing state

        // --- Base Product Validation ---
        $baseValidationRules = [
             'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->id),
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
            ],
            'brand_id' => 'nullable|integer|exists:brands,id',       
            'short_description' => 'nullable|string|max:500',  
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0|gt:price',
            'cost_price' => 'nullable|numeric|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',

            // **** SAME CHANGE FOR UPDATE VALIDATION ****
            'weight_unit' => [
                'required_with:weight',
                'nullable',
                'string',
                Rule::in(['kg', 'g', 'lb', 'oz']),
            ],
             // **** END CHANGE ****

            'dimensions' => 'nullable|string|max:100',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'videos' => 'nullable|array',
            'videos.*' => 'mimes:mp4,mov,avi,webm|max:100000',
            'video_titles.*' => 'nullable|string|max:255',
            'video_descriptions.*' => 'nullable|string',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:product_images,id',
            'delete_videos' => 'nullable|array',
            'delete_videos.*' => 'integer|exists:product_videos,id',
             'has_variants' => 'sometimes|boolean',
        ];

        // --- Conditional Validation (Update) ---
         if (!$hasVariants) {
             // Only validate SKU/Quantity if it's intended to be a simple product
             $baseValidationRules['sku'] = [
                 'required', // Required if simple
                 'string',
                 'max:100',
                 Rule::unique('products', 'sku')->ignore($product->id),
             ];
             $baseValidationRules['quantity'] = 'required|integer|min:0';
         } else {
            // Variant-specific validation for update (more complex)
            // You'll need rules for existing variants and potentially new ones
             $baseValidationRules['product_attributes'] = 'required|array|min:1';
             $baseValidationRules['product_attributes.*'] = 'required|integer|exists:attributes,id';
             $baseValidationRules['variants'] = 'sometimes|array'; // Allow not sending variants if only updating base product
             // Need careful validation for *each* submitted variant, ignoring others
            // Remove the 'integer' rule. 'sometimes' ensures it only runs if an ID is submitted.
// 'exists' ensures that IF an ID is submitted, it's valid and belongs to this product.
// Null or empty string values (new variants) won't trigger 'exists' to fail validation incorrectly.
$baseValidationRules['variants.*.id'] = [
    'sometimes',
    'nullable', // Explicitly allow null
    Rule::exists('product_variants', 'id')->where(function ($query) use ($product) {
        $query->where('product_id', $product->id);
    }),
];
             $baseValidationRules['variants.*.sku'] = [
                 'required',
                 'string',
                 'max:100',
                 'distinct',
                 // Unique check needs to ignore the *current* variant being validated if it has an ID
                 function ($attribute, $value, $fail) use ($request, $product) {
                     $index = explode('.', $attribute)[1]; // Get the index from 'variants.0.sku'
                     $variantId = $request->input("variants.{$index}.id");
                     $query = DB::table('product_variants')->where('sku', $value)->where('product_id', '!=', $product->id); // Check other products
                     $query->orWhere(function($q) use ($value, $product, $variantId) {
                         $q->where('sku', $value)->where('product_id', $product->id);
                         if ($variantId) {
                             $q->where('id', '!=', $variantId); // Ignore self if updating
                         }
                     });
                     if ($query->exists()) {
                         $fail("The SKU '{$value}' is already taken by another variant or product.");
                     }
                 },
             ];
             $baseValidationRules['variants.*.price'] = 'required|numeric|min:0';
             $baseValidationRules['variants.*.quantity'] = 'required|integer|min:0';
             $baseValidationRules['variants.*.attribute_value_ids'] = 'required|array|min:1';
             $baseValidationRules['variants.*.attribute_value_ids.*'] = 'required|integer|exists:attribute_values,id';
            // Add validation for attribute_values array if needed
             $baseValidationRules['attribute_values'] = 'sometimes|array';
             $baseValidationRules['attribute_values.*'] = 'sometimes|array';
             $baseValidationRules['attribute_values.*.*'] = 'sometimes|integer|exists:attribute_values,id';
         }

            // Validate 'specifications' if submitted for update
            if ($request->has('specifications')) {
                $baseValidationRules['specifications'] = 'nullable|array';                
            }

        // --- Attempt Validation ---
        try {
             $validatedData = $request->validate($baseValidationRules, [
                 // Add relevant custom messages for update if needed
                 'variants.*.sku.required' => 'The SKU for each variant is required.',
                 'variants.*.sku.distinct' => 'Variant SKUs must be unique within this submission.',
                 'brand_id.exists' => 'The selected brand is invalid.',
                 // Other messages...
                 'weight_unit.required_with' => 'The weight unit is required when a weight is provided.',
                 'weight_unit.in' => 'Please select a valid weight unit (kg, g, lb, oz).',
             ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Update Validation Failed:', $e->errors());
            return back()->withErrors($e->validator)->withInput();
        }
        

         // --- Use Database Transaction ---
         try {
            DB::beginTransaction();

            // --- Prepare Base Product Data ---
            $dataToUpdate = collect($validatedData)->except([
                'categories', 'images', 'videos', 'video_titles', 'video_descriptions',
                'delete_images', 'delete_videos',
                'product_attributes', 'attribute_values', 'variants', 'has_variants',
                'sku', 'quantity' // Also exclude these initially
            ])->toArray();


            // Handle Slug
            if (empty($dataToUpdate['slug'])) {
                unset($dataToUpdate['slug']); // Don't update slug if empty
            } else {
                // Use the validated slug provided by the user
                $dataToUpdate['slug'] = $validatedData['slug'];
            }

            // Handle Booleans
            $dataToUpdate['is_active'] = $request->boolean('is_active');
            $dataToUpdate['is_featured'] = $request->boolean('is_featured');

            // Add simple SKU/Quantity only if NOT hasVariants
             if (!$hasVariants) {
                 $dataToUpdate['sku'] = $validatedData['sku'] ?? $product->sku; // Keep old SKU if somehow not validated/submitted
                 $dataToUpdate['quantity'] = $validatedData['quantity'] ?? $product->quantity; // Keep old quantity
             } else {
                 // For products with variants, base SKU/Quantity should be null/0
                 $dataToUpdate['sku'] = null;
                 $dataToUpdate['quantity'] = 0;
             }

             $dataToUpdate['brand_id'] = $validatedData['brand_id'] ?? null; // Allow unsetting brand
             $dataToUpdate['short_description'] = $validatedData['short_description'] ?? null;

            // Handle specifications for update
            $processedSpecifications = [];
            if ($request->filled('spec_keys') && $request->filled('spec_values')) {
                $specKeys = $request->input('spec_keys');
                $specValues = $request->input('spec_values');
                foreach ($specKeys as $index => $key) {
                    if (!empty($key) && isset($specValues[$index]) && !empty($specValues[$index])) {
                        $processedSpecifications[] = ['key' => trim($key), 'value' => trim($specValues[$index])];
                    }
                }
                $dataToUpdate['specifications'] = !empty($processedSpecifications) ? $processedSpecifications : null;
            } elseif ($request->has('specifications') && empty($request->input('specifications')) && empty($request->input('spec_keys'))) {
                // If 'specifications' was explicitly sent as empty (e.g., all fields cleared by JS)
                // and no spec_keys were sent, set to null to clear them.
                $dataToUpdate['specifications'] = null;
            }
            // If 'spec_keys' and 'spec_values' are not present in the request, 'specifications' won't be in $dataToUpdate,
            // so the existing specifications will be preserved.
            // === END ADDED/UPDATED FIELDS ===


            //LOGIC TO UNSET weight_unit IF EMPTY
             if (array_key_exists('weight_unit', $dataToUpdate) && empty($dataToUpdate['weight_unit'])) {
                 
                 unset($dataToUpdate['weight_unit']);
                 Log::debug('Weight unit was empty/null during update, removing from update data.');
             } else if (array_key_exists('weight_unit', $dataToUpdate) && !empty($dataToUpdate['weight_unit'])) {
                 
             } 
             // **** END LOGIC ****
           


            // --- Update Product ---
            Log::debug("Data for Product->update():", $dataToUpdate);
            $product->update($dataToUpdate);
            Log::info("Product base updated successfully: ID {$product->id}");

            // --- Handle Relationships and Files ---

            // Sync Categories
            // Pass empty array if 'categories' key exists but is empty/null
            // If 'categories' key doesn't exist, don't sync (means user didn't touch category section)
            if ($request->has('categories')) {
                $product->categories()->sync($request->input('categories', []));
                 Log::info("Synced categories during update for Product ID: {$product->id}");
            }


            // --- Handle File Deletions ---
            if ($request->filled('delete_images')) {
                $imagesToDelete = $product->images()->whereIn('id', $request->input('delete_images'))->get();
                 Log::info("Attempting to delete images for Product ID: {$product->id}", ['ids' => $request->input('delete_images')]);
                foreach ($imagesToDelete as $image) {
                    try {
                        Storage::disk('public')->delete($image->path);
                        $image->delete();
                         Log::info("Deleted image: ID {$image->id}, Path: {$image->path}");
                    } catch (\Exception $e) {
                         Log::error("Failed to delete image {$image->id} for product {$product->id}: " . $e->getMessage());
                        // Decide if this should cause rollback: throw $e;
                    }
                }
            }
             if ($request->filled('delete_videos')) {
                $videosToDelete = $product->videos()->whereIn('id', $request->input('delete_videos'))->get();
                 Log::info("Attempting to delete videos for Product ID: {$product->id}", ['ids' => $request->input('delete_videos')]);
                foreach ($videosToDelete as $video) {
                    try {
                        Storage::disk('public')->delete($video->path);
                        // Optionally delete thumbnail too
                        if ($video->thumbnail_path) {
                             Storage::disk('public')->delete($video->thumbnail_path);
                        }
                        $video->delete();
                        Log::info("Deleted video: ID {$video->id}, Path: {$video->path}");
                    } catch (\Exception $e) {
                        Log::error("Failed to delete video {$video->id} for product {$product->id}: " . $e->getMessage());
                        // Decide if this should cause rollback: throw $e;
                    }
                }
            }

            // --- Handle NEW File Uploads ---
            if ($request->hasFile('images')) {
                 Log::info("Processing NEW image uploads for Product ID: {$product->id}");
                 // Reload images relation to get current max position accurately after deletions
                 $product->load('images');
                 $maxPosition = $product->images()->max('position') ?? -1;
                foreach ($request->file('images') as $index => $image) {
                    try {
                        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                        $path = $image->storeAs('product-images', $filename, 'public');
                        $product->images()->create([
                            'path' => $path,
                            'alt' => $product->name . ' new image ' . ($index + 1),
                            'position' => $maxPosition + 1 + $index,
                        ]);
                         Log::info("Uploaded NEW image: Path {$path}");
                    } catch (\Exception $e) {
                        Log::error("Failed to upload NEW image for product {$product->id}: " . $e->getMessage());
                         // Decide if this should cause rollback: throw $e;
                    }
                }
            }
            // Add similar logic for handling NEW video uploads if needed
            if ($request->hasFile('videos')) {
                 Log::info("Processing NEW video uploads for Product ID: {$product->id}");
                  // Reload videos relation to get current max position accurately after deletions
                 $product->load('videos');
                 $maxPosition = $product->videos()->max('position') ?? -1;
                 foreach ($request->file('videos') as $index => $video) {
                     try {
                         $filename = Str::uuid() . '.' . $video->getClientOriginalExtension();
                         $path = $video->storeAs('product-videos', $filename, 'public');
                         $thumbnailPath = null; // Placeholder
                         $product->videos()->create([
                             'path' => $path,
                             'title' => $request->input("video_titles.{$index}", null),
                             'description' => $request->input("video_descriptions.{$index}", null),
                             'thumbnail_path' => $thumbnailPath,
                             'position' => $maxPosition + 1 + $index,
                         ]);
                         Log::info("Uploaded NEW video: Path {$path}");
                     } catch (\Exception $e) {
                         Log::error("Failed to upload NEW video for product {$product->id} at index {$index}: " . $e->getMessage());
                         // Decide if this should cause rollback: throw $e;
                     }
                 }
             }


            // --- **** Handle Variant Updates **** ---
             if ($hasVariants && $request->has('variants')) {
                 Log::info("Processing variant updates for Product ID: {$product->id}");

                 // 1. Sync Product Attributes
                 if($request->has('product_attributes')) {
                     $product->attributes()->sync($validatedData['product_attributes']);
                     Log::info("Synced product attributes during update", ['attributes' => $validatedData['product_attributes']]);
                 }

                 // 2. Update/Create/Delete Variants - More complex logic needed here
                 $submittedVariants = $validatedData['variants'] ?? [];
                 $submittedVariantIds = collect($submittedVariants)->pluck('id')->filter()->all(); // IDs of variants submitted
                 $existingVariantIds = $product->variants()->pluck('id')->all();

                 // Pre-load submitted values
                 $allValueIds = collect($submittedVariants)->pluck('attribute_value_ids')->flatten()->unique()->toArray();
                 $attributeValues = AttributeValue::whereIn('id', $allValueIds)->get()->keyBy('id');

                 // Variants to Delete: Those existing but not in the submission
                 $variantsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
                 if (!empty($variantsToDelete)) {
                     Log::info("Deleting variants not present in submission", ['ids' => $variantsToDelete]);
                     // Detach relationships first (attribute values) then delete variant
                     DB::table('attribute_value_product_variant')
                         ->whereIn('product_variant_id', $variantsToDelete)
                         ->delete();
                     ProductVariant::whereIn('id', $variantsToDelete)->delete();
                 }

                 // Update or Create Variants
                 foreach ($submittedVariants as $index => $variantInput) {
                     $variantId = $variantInput['id'] ?? null;

                     // Generate Name (same logic as store)
                     $variantNameParts = [];
                     $sortedValueIds = collect($variantInput['attribute_value_ids'])->sort()->values()->all();
                     foreach ($sortedValueIds as $valueId) {
                         $variantNameParts[] = $attributeValues->get($valueId)?->value ?? '?';
                     }
                     $variantName = implode(' / ', $variantNameParts);

                     $variantData = [
                         'name' => $variantName,
                         'sku' => $variantInput['sku'],
                         'price' => $variantInput['price'],
                         'quantity' => $variantInput['quantity'],
                         'is_active' => true, // Or handle is_active per variant if needed
                     ];

                     // Update existing or create new
                     $variant = $product->variants()->updateOrCreate(
                         ['id' => $variantId], // Find by ID if present
                         $variantData         // Data to update or create with
                     );

                     // Sync attribute values for the variant
                     $variant->attributeValues()->sync($sortedValueIds);
                     Log::info(($variantId ? "Updated" : "Created") . " variant ID: {$variant->id}", ['data' => $variantData, 'values' => $sortedValueIds]);
                 }
             } elseif (!$hasVariants && $product->variants()->exists()) {
                 // If switched from variant to simple, delete existing variants
                 Log::warning("Product {$product->id} switched to simple, deleting existing variants.");
                 $product->variants()->each(function($variant) {
                     $variant->attributeValues()->detach();
                     $variant->delete();
                 });
                 $product->attributes()->detach(); // Also detach linked attributes
             }


            // --- Commit Transaction ---
            DB::commit();
            Log::info("Transaction committed for Product update: ID {$product->id}");

            return redirect()->route('admin.products.index')
                   ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            // --- Rollback Transaction ---
            DB::rollBack();
            Log::error("Transaction rolled back due to error during product update for ID {$product->id}: " . $e->getMessage(), ['exception' => $e]);

             if ($e instanceof \Illuminate\Validation\ValidationException) {
                 return back()->withErrors($e->validator)->withInput();
            }

            return back()->with('error', 'Failed to update product. An unexpected error occurred. Please check logs.')->withInput();
        }
    }


    public function destroy(Product $product)
    {
         // --- Use Database Transaction for Deletion ---
         try {
            DB::beginTransaction();

            // --- Delete Associated Files FIRST ---
            Log::info("Attempting to delete files for Product ID: {$product->id}");
            // Delete Images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            // Delete Videos
             foreach ($product->videos as $video) {
                Storage::disk('public')->delete($video->path);
                if ($video->thumbnail_path) {
                    Storage::disk('public')->delete($video->thumbnail_path);
                }
            }
             Log::info("Files deleted for Product ID: {$product->id}");

            // --- Delete Product Record ---
            // Relationships (categories, attributes, variants, images, videos) should be deleted via cascade
            // configured in migrations or handled by model observers if cascade isn't set.
            // Explicitly detaching/deleting here is safer if unsure about cascade settings.
             $product->categories()->detach();
             $product->attributes()->detach();
             // Variants and their values will be deleted by cascade from product_id constraint if set up correctly.
             // Images/Videos will be deleted by cascade if set up correctly.

             Log::info("Attempting to delete Product record: ID {$product->id}");
            $product->delete();
             Log::info("Product record deleted successfully: ID {$product->id}");

            // --- Commit Transaction ---
            DB::commit();
             Log::info("Transaction committed for product deletion: ID {$product->id}");

            return redirect()->route('admin.products.index')
                   ->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
             // --- Rollback Transaction ---
            DB::rollBack();
            Log::error("Error deleting product {$product->id}, transaction rolled back: " . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Failed to delete product. It might be associated with other records or an error occurred.');
        }
    }
    
    /**
     * Show the form for adjusting stock for a product or variant.
     */
    public function showStockAdjustmentForm(Product $product, ?ProductVariant $variant = null) // <-- Added ?
    {
        // Ensure variant actually belongs to product if provided (optional but good)
        if ($variant && $variant->product_id !== $product->id) {
            abort(404, 'Variant does not belong to this product.');
        }

        $adjustable = $variant ?: $product;
        $adjustableName = $variant ? ($product->name . ' - ' . $variant->name) : $product->name;
        // Ensure quantity is accessed correctly (it's already loaded if $variant/$product exists)
        $currentStock = $adjustable->quantity;

        return view('admin.products.stock.adjust', compact('product', 'variant', 'adjustable', 'adjustableName', 'currentStock'));
    }

    /**
     * Process the stock adjustment.
     * FIX: Add ? before ProductVariant type hint
     */
    public function adjustStock(Request $request, Product $product, ?ProductVariant $variant = null) // <-- Added ?
    {
         // Ensure variant actually belongs to product if provided
         if ($variant && $variant->product_id !== $product->id) {
             abort(404, 'Variant does not belong to this product.');
         }

        $request->validate([
            'quantity_change' => 'required|integer', // Cannot be zero if required? Maybe allow zero? Needs decision.
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $adjustable = $variant ?: $product;
        $quantityChange = (int) $request->input('quantity_change');

        // Prevent adjusting by zero? Optional check.
         if ($quantityChange === 0) {
             return back()->with('info', 'No change in quantity was specified.');
         }

        // Reload the adjustable model instance within the transaction for locking
        $adjustableModelClass = $variant ? ProductVariant::class : Product::class;

        DB::beginTransaction();
        try {
            // Lock the record for update
            $adjustableLocked = $adjustableModelClass::lockForUpdate()->findOrFail($adjustable->id);

            $quantityBefore = $adjustableLocked->quantity;
            // Ensure stock doesn't go below zero if that's a business rule
            // if (($quantityBefore + $quantityChange) < 0) {
            //    throw new \Exception("Stock cannot be adjusted below zero.");
            // }
            $adjustableLocked->quantity = $quantityBefore + $quantityChange;
            $adjustableLocked->save();
            $quantityAfter = $adjustableLocked->quantity; // Get quantity after save

            // Log the adjustment using the relationship from the LOCKED instance
            $adjustableLocked->stockAdjustments()->create([
                'user_id' => Auth::id(),
                'quantity_change' => $quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reason' => $request->input('reason'),
                'notes' => $request->input('notes'),
            ]);

            DB::commit();
            Log::info("Stock adjusted for " . get_class($adjustableLocked) . " ID: {$adjustableLocked->id} by {$quantityChange}. Reason: {$request->input('reason')}. Admin: " . Auth::id());

            // Redirect back to product edit page
            return redirect()->route('admin.products.edit', $product)->with('success', 'Stock adjusted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Stock adjustment failed for " . get_class($adjustable) . " ID: {$adjustable->id}. Error: " . $e->getMessage());
            // Add validation errors back if it was a validation exception within the try block? Unlikely here.
            return back()->with('error', 'Stock adjustment failed: ' . $e->getMessage())->withInput();
        }
    }
}