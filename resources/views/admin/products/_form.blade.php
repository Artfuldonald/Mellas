{{-- resources/views/admin/products/_form.blade.php --}}
@csrf {{-- CSRF Token --}}

@php
    $selectedCategories = old('categories', isset($product) ? $product->categories->pluck('id')->toArray() : []);
    // Determine if the product should start in variant mode (simple check for now)
    $hasVariants = old('has_variants', isset($product) && $product->variants()->exists() );
@endphp

<div  x-data="{ hasVariants: {{ $hasVariants ? 'true' : 'false' }} }" 
      class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- Main Column (Left) --}}
    <div class="md:col-span-2 space-y-6">

        {{-- Basic Info Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Product Information</h3>

                {{-- Product Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Slug (Optional Manual Override on Edit) --}}
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('slug') border-red-500 @enderror"
                           aria-describedby="slug-description">
                     <p class="mt-2 text-sm text-gray-500" id="slug-description">Leave blank to keep current slug. Slugs should contain only letters, numbers, and hyphens.</p>
                    @error('slug') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Product Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="5"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-500 @enderror"
                    >{{ old('description', $product->description ?? '') }}</textarea>
                    @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    {{-- Consider adding a WYSIWYG editor here --}}
                </div>
            </div>
        </div>

        {{-- Pricing Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                 <h3 class="text-lg font-medium leading-6 text-gray-900">Pricing</h3>
                 <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Price --}}
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price <span class="text-red-500">*</span></label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"> <span class="text-gray-500 sm:text-sm">$</span> </div>
                            <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price', $product->price ?? '') }}" required
                                   class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('price') border-red-500 @enderror" placeholder="0.00">
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"> <span class="text-gray-500 sm:text-sm"> USD </span> </div>
                        </div>
                         @error('price') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    {{-- Compare At Price --}}
                     <div>
                        <label for="compare_at_price" class="block text-sm font-medium text-gray-700">Compare-at Price</label>
                         <div class="relative mt-1 rounded-md shadow-sm">
                             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"> <span class="text-gray-500 sm:text-sm">$</span> </div>
                            <input type="number" name="compare_at_price" id="compare_at_price" step="0.01" min="0" value="{{ old('compare_at_price', $product->compare_at_price ?? '') }}"
                                    class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('compare_at_price') border-red-500 @enderror" placeholder="0.00">
                             <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"> <span class="text-gray-500 sm:text-sm"> USD </span> </div>
                         </div>
                        @error('compare_at_price') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    {{-- Cost Price --}}
                     <div>
                        <label for="cost_price" class="block text-sm font-medium text-gray-700">Cost Price</label>
                         <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"> <span class="text-gray-500 sm:text-sm">$</span> </div>
                            <input type="number" name="cost_price" id="cost_price" step="0.01" min="0" value="{{ old('cost_price', $product->cost_price ?? '') }}"
                                    class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('cost_price') border-red-500 @enderror" placeholder="0.00">
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"> <span class="text-gray-500 sm:text-sm"> USD </span> </div>
                         </div>
                        @error('cost_price') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Optional: For profit calculation.</p>
                    </div>
                </div>
            </div>
        </div>

         {{-- Inventory Card - Now CONDITIONAL --}}
        <div class="bg-white shadow sm:rounded-lg" x-show="!hasVariants" x-transition.opacity>
             <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Inventory (Simple Product)</h3>
                <p class="text-sm text-gray-500 -mt-4">Enter SKU and quantity if this product does not have variants.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- SKU (Simple Product) --}}
                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('sku') border-red-500 @enderror"
                               :required="!hasVariants" {{-- Required only if simple --}}
                               :disabled="hasVariants"> {{-- Disable if variants enabled --}}
                        @error('sku') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    {{-- Quantity (Simple Product) --}}
                     <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" id="quantity" min="0" step="1" value="{{ old('quantity', $product->quantity ?? 0) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('quantity') border-red-500 @enderror"
                               :required="!hasVariants" {{-- Required only if simple --}}
                               :disabled="hasVariants"> {{-- Disable if variants enabled --}}
                        @error('quantity') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Placeholder for Variant Management Card (Initially Hidden) --}}
        <div class="bg-white shadow sm:rounded-lg" x-show="hasVariants" x-transition.opacity style="display: none;"> {{-- Add style="display: none;" --}}
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Product Variants</h3>
                 <p class="text-sm text-gray-500 -mt-4">Define attributes and generate variants with unique SKU, price, and quantity.</p>
                 {{-- Attribute/Value/Variant UI will go here in next steps --}}
                 <div class="border border-dashed border-gray-300 rounded-md p-6 text-center text-gray-500">
                    Variant configuration controls will appear here.
                 </div>
            </div>
        </div>

         {{-- Shipping Card --}}
        <div class="bg-white shadow sm:rounded-lg">
             <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Shipping</h3>
                 <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                     {{-- Weight --}}
                     <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700">Weight</label>
                        <input type="number" name="weight" id="weight" step="any" min="0" value="{{ old('weight', $product->weight ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('weight') border-red-500 @enderror">
                        @error('weight') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    {{-- Weight Unit --}}
                     <div>
                        <label for="weight_unit" class="block text-sm font-medium text-gray-700">Weight Unit</label>
                         <select id="weight_unit" name="weight_unit" class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm @error('weight_unit') border-red-500 @enderror">
                            <option value="" {{ old('weight_unit', $product->weight_unit ?? '') == '' ? 'selected' : '' }}>Select Unit</option>
                            <option value="kg" {{ old('weight_unit', $product->weight_unit ?? '') == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="g" {{ old('weight_unit', $product->weight_unit ?? '') == 'g' ? 'selected' : '' }}>g</option>
                            <option value="lb" {{ old('weight_unit', $product->weight_unit ?? '') == 'lb' ? 'selected' : '' }}>lb</option>
                            <option value="oz" {{ old('weight_unit', $product->weight_unit ?? '') == 'oz' ? 'selected' : '' }}>oz</option>
                        </select>
                        @error('weight_unit') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    {{-- Dimensions --}}
                     <div>
                        <label for="dimensions" class="block text-sm font-medium text-gray-700">Dimensions (LxWxH)</label>
                        <input type="text" name="dimensions" id="dimensions" value="{{ old('dimensions', $product->dimensions ?? '') }}" placeholder="e.g., 10x5x2 cm"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('dimensions') border-red-500 @enderror">
                        @error('dimensions') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                 </div>
            </div>
        </div>

        {{-- SEO Card --}}
        <div class="bg-white shadow sm:rounded-lg">
             <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Search Engine Listing Preview</h3>
                 <p class="mt-1 text-sm text-gray-500">Add a title and description to see how this product might appear on search engines.</p>
                <div class="mt-4 space-y-4">
                    {{-- Meta Title --}}
                     <div>
                        <label for="meta_title" class="block text-sm font-medium text-gray-700">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $product->meta_title ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('meta_title') border-red-500 @enderror">
                        @error('meta_title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    {{-- Meta Description --}}
                     <div>
                        <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
                         <textarea id="meta_description" name="meta_description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('meta_description') border-red-500 @enderror"
                        >{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                        @error('meta_description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>


    </div> {{-- End Main Column --}}


    {{-- Sidebar Column (Right) --}}
    <div class="md:col-span-1 space-y-6">

         {{-- Status Card --}}
        <div class="bg-white shadow sm:rounded-lg">
             <div class="px-4 py-5 sm:p-6 space-y-4">
                 <h3 class="text-lg font-medium leading-6 text-gray-900">Status</h3>
                 {{-- Active Checkbox --}}
                 <div class="relative flex items-start">
                    <div class="flex h-5 items-center">
                        <input id="is_active" name="is_active" type="checkbox" value="1"
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ old('is_active', $product->is_active ?? false) ? 'checked' : '' }}
                               >
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-gray-700">Active</label>
                        <p class="text-gray-500">Product is visible and purchasable.</p>
                    </div>
                </div>
                {{-- Featured Checkbox --}}
                 <div class="relative flex items-start">
                    <div class="flex h-5 items-center">
                        <input id="is_featured" name="is_featured" type="checkbox" value="1"
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}
                               >
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_featured" class="font-medium text-gray-700">Featured</label>
                        <p class="text-gray-500">Highlight this product.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Organization Card (Add Toggle Here) --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Organization</h3>

                {{-- Variants Toggle --}}
                <div class="relative flex items-start border-b pb-4 mb-4 border-dashed border-gray-200">
                   <div class="flex h-5 items-center">
                       <input id="has_variants" name="has_variants" type="checkbox" value="1"
                              x-model="hasVariants" {{-- Bind to Alpine state --}}
                              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                   </div>
                   <div class="ml-3 text-sm">
                       <label for="has_variants" class="font-medium text-gray-900">This product has variants</label>
                       <p class="text-gray-500">Check if this product comes in options like size or color.</p>
                   </div>
                </div>
                
        {{-- Categories Card --}}
         <div class="bg-white shadow sm:rounded-lg">
             <div class="px-4 py-5 sm:p-6 space-y-4">
                 <h3 class="text-lg font-medium leading-6 text-gray-900">Organization</h3>
                 {{-- Category Selection --}}
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Categories</label>
                      <div class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-2">
                        @forelse($categories as $category)
                             <div class="relative flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="category_{{ $category->id }}" name="categories[]" type="checkbox" value="{{ $category->id }}"
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                           {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}
                                           >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="category_{{ $category->id }}" class="font-medium text-gray-700">{{ $category->name }}</label>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No categories exist. <a href="{{ route('admin.categories.create') }}" class="text-indigo-600 hover:underline" target="_blank">Create one</a>.</p>
                        @endforelse
                     </div>
                     @error('categories') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                     @error('categories.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                     <a href="{{ route('admin.categories.create') }}" target="_blank" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">Add new category</a>
                 </div>

                {{-- Add Tags input here if needed later --}}

            </div>
        </div>


        {{-- Images Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Images</h3>

                {{-- Display Existing Images --}}
                @if(isset($product) && $product->images->isNotEmpty())
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Current Images</label>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach($product->images->sortBy('sort_order') as $image)
                            <div class="relative group">
                                <img src="{{ Storage::url($image->path) }}" alt="{{ $image->alt_text ?? 'Product Image' }}" class="block w-full h-auto object-cover rounded-md border border-gray-200">
                                <div class="absolute top-1 right-1">
                                    <label for="delete_image_{{ $image->id }}" class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 cursor-pointer">
                                        <input type="checkbox" name="delete_images[]" id="delete_image_{{ $image->id }}" value="{{ $image->id }}" class="hidden">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </label>
                                </div>
                                 {{-- Add fields for Alt Text or Sort Order per image if needed --}}
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500">Check the trash icon to mark an image for deletion on update.</p>
                     @error('delete_images.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                @endif

                {{-- Upload New Images --}}
                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700">
                         {{ isset($product) && $product->images->isNotEmpty() ? 'Upload Additional Images' : 'Upload Images' }}
                    </label>
                    <div class="mt-1">
                        <input type="file" name="images[]" id="images" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 @error('images.*') border-red-500 @enderror" multiple accept="image/*">
                        @error('images.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <p class="mt-1 text-sm text-gray-500">You can select multiple images (jpg, jpeg, png, gif, webp)</p>
                </div>
            </div>
        </div>


         {{-- Videos Card (Integrating your structure) --}}
        <div class="bg-white shadow sm:rounded-lg">
             <div class="px-4 py-5 sm:p-6 space-y-4">
                 <h3 class="text-lg font-medium leading-6 text-gray-900">Videos</h3>

                 {{-- Display Existing Videos --}}
                 @if(isset($product) && $product->videos->isNotEmpty())
                 <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700">Current Videos</label>
                    <div class="space-y-4">
                         @foreach($product->videos->sortBy('sort_order') as $video)
                            <div class="relative p-3 border border-gray-200 rounded-md bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        {{-- Placeholder for video thumbnail/player --}}
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $video->title ?? basename($video->path) }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ $video->description ?? 'No description' }}</p>
                                        {{-- Maybe show Storage::url($video->path) --}}
                                    </div>
                                     <div class="ml-4 flex-shrink-0">
                                         <label for="delete_video_{{ $video->id }}" class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 cursor-pointer">
                                            <input type="checkbox" name="delete_videos[]" id="delete_video_{{ $video->id }}" value="{{ $video->id }}" class="hidden">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                         </label>
                                     </div>
                                 </div>
                             </div>
                         @endforeach
                    </div>
                     <p class="text-xs text-gray-500">Check the trash icon to mark a video for deletion on update.</p>
                     @error('delete_videos.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                 </div>
                 @endif


                {{-- Upload New Videos Section --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                         {{ isset($product) && $product->videos->isNotEmpty() ? 'Upload Additional Videos' : 'Upload Videos' }}
                    </label>
                    <div id="video-container" class="space-y-6">
                        {{-- Initial video upload fields will be added by JS if none exist, or you can add one block here manually --}}
                        {{-- If you always want at least one upload block visible: --}}
                        {{--
                         <div class="video-entry space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 sr-only">Upload Video File</label>
                                <input type="file" name="videos[]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 @error('videos.*') border-red-500 @enderror" accept="video/mp4,video/mov,video/avi,video/webm">
                                @error('videos.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Accepted formats: MP4, MOV, AVI, WEBM (max 100MB)</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 sr-only">Video Title</label>
                                <input type="text" name="video_titles[]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Video Title (Optional)">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 sr-only">Video Description</label>
                                <textarea name="video_descriptions[]" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Video Description (Optional)"></textarea>
                            </div>
                        </div>
                         --}}
                    </div>
                    <button type="button" id="add-video" class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Video Upload Fields
                    </button>
                </div>
            </div>
        </div>


    </div> {{-- End Sidebar Column --}}

</div> {{-- End Grid --}}


{{-- Action Buttons --}}
<div class="mt-8 pt-5 border-t border-gray-200">
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.products.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Cancel
        </a>
        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ isset($product) && $product->exists ? 'Update Product' : 'Create Product' }} {{-- Dynamic Button Text --}}
        </button>
    </div>
</div>


{{-- JavaScript for dynamic elements --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Slug Generation Helper ---
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    if (nameInput && slugInput) {
        nameInput.addEventListener('blur', function() {
            // Only fill slug if it's currently empty (don't overwrite manual edits)
            if (slugInput.value === '') {
                const slug = nameInput.value
                    .toLowerCase()
                    .trim()
                    .replace(/\s+/g, '-')           // Replace spaces with -
                    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars except -
                    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                    .replace(/^-+/, '')             // Trim - from start of text
                    .replace(/-+$/, '');            // Trim - from end of text
                slugInput.value = slug;
            }
        });
    }

    // --- Dynamic Video Fields ---
    const addVideoBtn = document.getElementById('add-video');
    const videoContainer = document.getElementById('video-container');

    // Function to create a new video entry block
    function createVideoEntry() {
        const index = Date.now(); // Use timestamp for unique-ish IDs if needed, though not essential for names[]
        const videoEntry = document.createElement('div');
        videoEntry.className = 'video-entry space-y-4 pt-6 border-t border-gray-200 mt-6 relative'; // Added relative for positioning remove button
        videoEntry.innerHTML = `
            <button type="button" class="absolute -top-3 right-0 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 remove-video" title="Remove this video entry">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
            <div>
                <label class="block text-sm font-medium text-gray-700 sr-only">Upload Video File</label>
                <input type="file" name="videos[]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="video/mp4,video/mov,video/avi,video/webm">
                 <p class="mt-1 text-sm text-gray-500">Accepted formats: MP4, MOV, AVI, WEBM (max 100MB)</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 sr-only">Video Title</label>
                <input type="text" name="video_titles[]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Video Title (Optional)">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 sr-only">Video Description</label>
                <textarea name="video_descriptions[]" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Video Description (Optional)"></textarea>
            </div>
        `;
        videoContainer.appendChild(videoEntry);

        // Add event listener to the new remove button
        const removeBtn = videoEntry.querySelector('.remove-video');
        removeBtn.addEventListener('click', function() {
            videoContainer.removeChild(videoEntry);
        });
    }

     // Add initial block if container is empty (optional)
     if (videoContainer && videoContainer.children.length === 0) {
        // createVideoEntry(); // Uncomment if you want one block always present initially
     }


    // Event listener for the main "Add Video" button
    if (addVideoBtn) {
        addVideoBtn.addEventListener('click', createVideoEntry);
    }

    // Add event listeners to any existing remove buttons (if you render some server-side initially)
    const existingRemoveBtns = videoContainer.querySelectorAll('.remove-video');
    existingRemoveBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Find the parent '.video-entry' and remove it
             const entryToRemove = btn.closest('.video-entry');
             if (entryToRemove) {
                 videoContainer.removeChild(entryToRemove);
             }
        });
    });

});
</script>
@endpush