{{-- resources/views/admin/products/_form.blade.php --}}
@csrf

@if ($errors->any())
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4">
        <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
        <ul class="mt-2 list-inside list-disc text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $product = $product ?? new \App\Models\Product();
    $selectedCategories = old('categories', $product->exists ? $product->categories->pluck('id')->toArray() : []);
    $hasVariants = old('has_variants', $product->exists && $product->attributes()->exists());
    $selectedAttributes = old('product_attributes', $product->exists ? $product->attributes->pluck('id')->map(fn($id) => (string)$id)->toArray() : []);
    $initialVariants = $product->exists ? $product->variants()->with('media', 'attributeValues')->get() : collect();
    $allAttributesForAlpine = $allAttributes ?? \App\Models\Attribute::with('values')->orderBy('name')->get();
    $brandsForSelect = $brandsForSelect ?? \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
    
    $formSpecifications = [];
    if (!empty(old('spec_keys'))) {
        foreach (old('spec_keys') as $index => $key) {
            if (!empty($key)) $formSpecifications[] = ['key' => $key, 'value' => old('spec_values')[$index] ?? ''];
        }
    } elseif ($product->exists && is_array($product->specifications)) {
        $formSpecifications = $product->specifications;
    }
@endphp

<div x-data="productForm({
        hasVariants: {{ $hasVariants ? 'true' : 'false' }},
        allAttributes: {{ Js::from($allAttributesForAlpine) }},
        initialSelectedAttributeIds: {{ Js::from($selectedAttributes) }},
        initialVariants: {{ Js::from($initialVariants) }},
        initialSpecifications: {{ Js::from($formSpecifications) }},
        basePrice: '{{ old('price', $product->price ?? 0) }}'
     })"
    class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- Main Column (Left) --}}
    <div class="md:col-span-2 space-y-6">
        {{-- Basic Info Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Product Information</h3>
                
                <div>
                    <x-input-label for="name" :value="__('Product Name')" /> <span class="text-red-500">*</span>
                    <x-text-input type="text" name="name" id="name" class="mt-1 block w-full" :value="old('name', $product->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="slug" :value="__('Slug')" />
                    <x-text-input type="text" name="slug" id="slug" class="mt-1 block w-full" :value="old('slug', $product->slug)" aria-describedby="slug-description"/>
                     <p class="mt-2 text-sm text-gray-500" id="slug-description">Leave blank to auto-generate. Slugs should contain only letters, numbers, and hyphens.</p>
                    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                </div>

                {{-- Short Description --}}
                <div>
                    <x-input-label for="short_description" :value="__('Short Description')" />
                    <textarea id="short_description" name="short_description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm @error('short_description') border-red-500 @enderror"
                    >{{ old('short_description', $product->short_description) }}</textarea>
                    <x-input-error :messages="$errors->get('short_description')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">A brief summary, often shown on product listings or quick views (max 500 chars).</p>
                </div>

                <div>
                    <x-input-label for="description" :value="__('Full Description')" />
                    <textarea id="description" name="description" rows="5"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm @error('description') border-red-500 @enderror"
                    >{{ old('description', $product->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- Specifications Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium">Specifications</h3>
                    <button type="button" @click="addSpecification" class="inline-flex items-center rounded-md bg-pink-50 px-2.5 py-1.5 text-xs font-semibold text-pink-700 shadow-sm hover:bg-pink-100">
                        <x-heroicon-s-plus class="-ml-0.5 mr-1 h-4 w-4"/> Add Specification
                    </button>
                </div>
                
                <div id="specifications-container" class="space-y-4">
                    <template x-for="(spec, index) in specifications.filter(s => !s.markedForDeletion)" :key="spec.clientId">
                        <div class="grid grid-cols-11 gap-x-3 items-end">
                            <div class="col-span-5">
                                <input type="text" :name="`spec_keys[]`" x-model="spec.key" placeholder="e.g., Material" class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="col-span-5">
                                <input type="text" :name="`spec_values[]`" x-model="spec.value" placeholder="e.g., Cotton" class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="col-span-1">
                                <button type="button" @click="deleteSpecification(spec.clientId)" class="p-1.5 text-red-500 hover:bg-red-50 rounded-md">
                                    <x-heroicon-o-trash class="w-4 h-4"/>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div x-show="deletedSpecifications.length > 0" x-cloak class="text-sm text-yellow-700 bg-yellow-50 p-3 rounded-md">
                    <span x-text="deletedSpecifications.length"></span> item(s) will be deleted on save.
                    <button type="button" @click="undoSpecificationDeletion()" class="ml-2 font-semibold hover:underline">Undo</button>
                </div>
                
                <p x-show="specifications.filter(s => !s.markedForDeletion).length === 0" class="text-sm text-gray-500 italic">No specifications added.</p>
                <input type="hidden" name="specifications_submitted_flag" value="1">
            </div>
        </div>

        {{-- Pricing Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                 <h3 class="text-lg font-medium leading-6 text-gray-900">Pricing<span class="text-red-500">*</span></h3>
                 
                 <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="price" :value="__('Price')" />
                         <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <x-text-input type="number" name="price" id="price" step="0.01" min="0" :value="old('price', $product->price ?? '')" required class="block w-full pl-7 pr-12" placeholder="0.00"/>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 sm:text-sm">USD</span>
                            </div>
                        </div>
                         <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>
                     
                    <div>
                        <x-input-label for="compare_at_price" :value="__('Compare-at Price')" />
                         <div class="relative mt-1 rounded-md shadow-sm">
                             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <x-text-input type="number" name="compare_at_price" id="compare_at_price" step="0.01" min="0" :value="old('compare_at_price', $product->compare_at_price ?? '')" class="block w-full pl-7 pr-12" placeholder="0.00"/>
                             <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 sm:text-sm">USD</span>
                            </div>
                         </div>
                        <x-input-error :messages="$errors->get('compare_at_price')" class="mt-2" />
                    </div>
                     
                    <div>
                        <x-input-label for="cost_price" :value="__('Cost Price')" />
                         <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <x-text-input type="number" name="cost_price" id="cost_price" step="0.01" min="0" :value="old('cost_price', $product->cost_price ?? '')" class="block w-full pl-7 pr-12" placeholder="0.00"/>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 sm:text-sm">USD</span>
                            </div>
                         </div>
                        <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
                        <p class="mt-1 text-xs text-gray-500">Optional: For profit calculation.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Inventory Card (Simple Product) --}}
        <div class="bg-white shadow sm:rounded-lg" x-show="!hasVariants" x-transition.opacity>
             <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Inventory (Simple Product)</h3>
                <p class="text-sm text-gray-500 -mt-4">Enter SKU and quantity if this product does not have variants.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <x-input-label for="sku" :value="__('SKU')" />
                        <x-text-input type="text" name="sku" id="sku" :value="old('sku', $product->sku ?? '')"
                               class="mt-1 block w-full"
                               ::required="!hasVariants"
                               ::disabled="hasVariants" />
                        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                    </div>
                     
                    <div>
                        <x-input-label for="quantity" :value="__('Quantity')" />
                        <x-text-input type="number" name="quantity" id="quantity" min="0" step="1" :value="old('quantity', $product->quantity ?? 0)"
                               class="mt-1 block w-full"
                               ::required="!hasVariants"
                               ::disabled="hasVariants" />
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Variant Management Card --}}
        <div class="bg-white shadow sm:rounded-lg" x-show="hasVariants" x-transition.opacity x-cloak>
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Product Variants</h3>
                                
                <!-- Attribute Selection -->
                <div>
                    <x-input-label for="product_attributes" :value="__('Variant Attributes')" />
                    <select name="product_attributes[]" id="product_attributes" multiple x-model="selectedAttributeIds" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach($allAttributes as $attribute)
                            <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Attribute Value Selection -->
                <div class="space-y-4" x-show="selectedAttributeIds.length > 0" x-transition>
                     <h4 class="text-md font-medium text-gray-800 border-b pb-1">Configure Values</h4>
                     
                     <template x-for="attributeId in selectedAttributeIds.map(id => parseInt(id))" :key="attributeId">
                        <div class="p-3 border rounded-md bg-gray-50/50">
                             <label x-text="getAttributeName(attributeId)" class="block text-sm font-medium text-gray-700 mb-2"></label>
                             
                             <div class="flex flex-wrap gap-2">
                                <template x-for="value in getAttributeValues(attributeId)" :key="value.id">
                                     <label class="inline-flex items-center px-3 py-1 bg-white border border-gray-300 rounded-full text-sm cursor-pointer hover:bg-gray-100 has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300">
                                         <input type="checkbox" :name="`attribute_values[${attributeId}][]`" :value="value.id" x-model="selectedValues[attributeId]" class="form-checkbox h-4 w-4 mr-2 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                         <span x-text="value.value"></span>
                                     </label>
                                 </template>
                             </div>
                         </div>
                     </template>
                </div>
                
                <!-- Generate Variants Button -->
                <div x-show="canGenerateVariants()" x-transition>
                     <button type="button" @click.prevent="generateVariants" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Generate Variants</button>
                </div>
                
                <!-- Variants Table -->
                <div class="mt-6" x-show="variants.length > 0 || deletedVariants.length > 0" x-transition>
                    <h4 class="text-md font-medium text-gray-800 mb-2">Generated Variants</h4>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs uppercase">Image</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">Variant</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">Price *</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">SKU *</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase">Quantity *</th>
                                    <th class="px-4 py-3 text-center text-xs uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(variant, index) in variants" :key="variant.clientId">
                                    <tr>
                                        <input type="hidden" :name="`variants[${index}][id]`" :value="variant.id">
                                        <template x-for="valueId in variant.attributeValueIds">
                                            <input type="hidden" :name="`variants[${index}][attribute_value_ids][]`" :value="valueId">
                                        </template>
                                        
                                        <td class="px-4 py-3 align-top">
                                            <div class="w-24 space-y-1">
                                                <img :src="variant.image_url || '{{ asset('images/placeholder.png') }}'" class="w-16 h-16 object-cover rounded border">
                                                <input type="file" :name="`variants[${index}][image]`" class="block w-full text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                                                <label x-show="variant.id && variant.image_url" class="flex items-center text-red-600 cursor-pointer text-xs">
                                                    <input type="checkbox" :name="`variants[${index}][delete_image]`" value="1" class="h-3 w-3 mr-1"> Del Img
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm font-medium text-gray-900" x-text="getVariantName(variant.attributeValueIds)"></td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="number" :name="`variants[${index}][price]`" x-model.number="variant.price" step="0.01" required class="w-24 rounded-md border-gray-300 shadow-sm sm:text-sm">
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="text" :name="`variants[${index}][sku]`" x-model="variant.sku" required class="w-36 rounded-md border-gray-300 shadow-sm sm:text-sm">
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="number" :name="`variants[${index}][quantity]`" x-model.number="variant.quantity" required class="w-20 rounded-md border-gray-300 shadow-sm sm:text-sm">
                                        </td>
                                        <td class="px-4 py-3 align-top text-center">
                                            <button type="button" @click="deleteVariant(variant.clientId)" class="p-1.5 text-red-500 hover:bg-red-50 rounded-md">
                                                <x-heroicon-o-trash class="w-4 h-4"/>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                     
                    <div x-show="deletedVariants.length > 0" x-cloak class="mt-4 text-sm text-yellow-700 bg-yellow-50 p-3 rounded-md">
                        <span x-text="deletedVariants.length"></span> variant(s) will be deleted on save.
                        <button type="button" @click="undoVariantDeletion()" class="ml-2 font-semibold hover:underline">Undo</button>
                    </div>
                </div>
            </div>
        </div>
             
        {{-- Shipping Card --}}
        <div class="bg-white shadow sm:rounded-lg">
             <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Shipping</h3>
                 
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                     <div>
                        <x-input-label for="weight" :value="__('Weight')" />
                        <x-text-input type="number" name="weight" id="weight" step="any" min="0" :value="old('weight', $product->weight ?? '')" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('weight')" class="mt-2" />
                    </div>
                     
                    <div>
                        <x-input-label for="weight_unit" :value="__('Weight Unit')" />
                         <select id="weight_unit" name="weight_unit" class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            <option value="" {{ old('weight_unit', $product->weight_unit ?? '') == '' ? 'selected' : '' }}>Select Unit</option>
                            <option value="kg" {{ old('weight_unit', $product->weight_unit ?? 'kg') == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="g" {{ old('weight_unit', $product->weight_unit ?? '') == 'g' ? 'selected' : '' }}>g</option>
                            <option value="lb" {{ old('weight_unit', $product->weight_unit ?? '') == 'lb' ? 'selected' : '' }}>lb</option>
                            <option value="oz" {{ old('weight_unit', $product->weight_unit ?? '') == 'oz' ? 'selected' : '' }}>oz</option>
                        </select>
                        <x-input-error :messages="$errors->get('weight_unit')" class="mt-2" />
                    </div>
                     
                    <div>
                        <x-input-label for="dimensions" :value="__('Dimensions (LxWxH)')" />
                        <x-text-input type="text" name="dimensions" id="dimensions" :value="old('dimensions', $product->dimensions ?? '')" placeholder="e.g., 10x5x2 cm" class="mt-1 block w-full"/>
                        <x-input-error :messages="$errors->get('dimensions')" class="mt-2" />
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
                     <div>
                        <x-input-label for="meta_title" :value="__('Meta Title')" />
                        <x-text-input type="text" name="meta_title" id="meta_title" :value="old('meta_title', $product->meta_title ?? '')" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('meta_title')" class="mt-2" />
                    </div>
                     
                    <div>
                        <x-input-label for="meta_description" :value="__('Meta Description')" />
                         <textarea id="meta_description" name="meta_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('meta_description')" class="mt-2" />
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
                 
                <div class="relative flex items-start">
                    <div class="flex h-6 items-center">
                        <input id="is_active" name="is_active" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} >
                    </div>
                    <div class="ml-3 text-sm leading-6">
                        <label for="is_active" class="font-medium text-gray-900">Active</label>
                        <p class="text-gray-500">Product is visible and purchasable.</p>
                    </div>
                </div>
                 
                <div class="relative flex items-start">
                    <div class="flex h-6 items-center">
                        <input id="is_featured" name="is_featured" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }} >
                    </div>
                    <div class="ml-3 text-sm leading-6">
                        <label for="is_featured" class="font-medium text-gray-900">Featured</label>
                        <p class="text-gray-500">Highlight this product (e.g., on homepage).</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Organization Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Organization</h3>
                
                {{-- Variants Toggle --}}
                <div class="relative flex items-start border-b pb-4 mb-4 border-dashed border-gray-200">
                   <div class="flex h-6 items-center">
                       <input id="has_variants" name="has_variants" type="checkbox" value="1"
                              x-model="hasVariants"
                              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                   </div>
                   <div class="ml-3 text-sm leading-6">
                       <label for="has_variants" class="font-medium text-gray-900">This product has variants</label>
                       <p class="text-gray-500">Check if this product comes in options like size or color.</p>
                   </div>
                </div>

                {{-- Brand Selection --}}
                <div class="pt-4 border-t border-dashed border-gray-200">
                    <x-input-label for="brand_id" :value="__('Brand')" />
                    <select id="brand_id" name="brand_id"
                            class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-pink-500 focus:outline-none focus:ring-pink-500 sm:text-sm @error('brand_id') border-red-500 @enderror">
                        <option value="">-- Select Brand (Optional) --</option>
                        @foreach($brandsForSelect as $brandOption)
                            <option value="{{ $brandOption->id }}" {{ old('brand_id', $product->brand_id) == $brandOption->id ? 'selected' : '' }}>
                                {{ $brandOption->name }}
                            </option>
                        @endforeach
                        </select>
                    <x-input-error :messages="$errors->get('brand_id')" class="mt-2" />
                    <a href="{{ route('admin.brands.create') }}" target="_blank" class="mt-2 inline-block text-sm text-pink-600 hover:underline">Add new brand</a>
                </div>
                 
                {{-- Category Selection --}}
                 <div>
                     <x-input-label :value="__('Categories')" />
                      
                    <div class="mt-1 space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-3 bg-gray-50/50">
                        @forelse($categories as $category)
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="category_{{ $category->id }}" name="categories[]" type="checkbox" value="{{ $category->id }}"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }} >
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="category_{{ $category->id }}" class="font-medium text-gray-700">{{ $category->name }}</label>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No categories exist. <a href="{{ route('admin.categories.create') }}" class="text-indigo-600 hover:underline" target="_blank">Create one</a>.</p>
                        @endforelse
                     </div>
                     <x-input-error :messages="$errors->get('categories')" class="mt-2" />
                     <x-input-error :messages="$errors->get('categories.*')" class="mt-2" />
                     <a href="{{ route('admin.categories.create') }}" target="_blank" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">Add new category</a>
                 </div>
            </div>
        </div>

        {{-- Images Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Images</h3>
                
                <div x-data="{
                                 images: {{ Js::from($product->getMedia('default')->map(fn ($media) => ['id' => $media->id, 'url' => $media->getUrl('cart_thumbnail'), 'name' => $media->name])) }},
                deletedImageIds: [],
                                
                toggleDelete(imageId) {
                    const index = this.deletedImageIds.indexOf(imageId);
                    if (index > -1) {
                        this.deletedImageIds.splice(index, 1);
                    } else {
                        this.deletedImageIds.push(imageId);
                    }
                },
                isDeleted(imageId) {
                    return this.deletedImageIds.includes(imageId);
                }
            }">
            
            {{-- Display Existing Images --}}
            <div x-show="images.length > 0" class="space-y-3">
                <x-input-label :value="__('Current Images')" />
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                    <template x-for="image in images" :key="image.id">
                        <div class="relative group">
                            <img :src="image.url" :alt="image.name"
                                  :class="{ 'opacity-30 grayscale': isDeleted(image.id) }"
                                 class="block w-full aspect-square object-cover rounded-md border transition-all">
                            
                            <input type="checkbox" :name="`delete_images[]`" :value="image.id" :checked="isDeleted(image.id)" class="hidden">
                            
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity flex items-center justify-center">
                                <button type="button" @click="toggleDelete(image.id)"
                                        class="hidden group-hover:inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white transition-colors"
                                        :class="isDeleted(image.id) ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">
                                                                        
                                    <template x-if="!isDeleted(image.id)">
                                        <x-heroicon-o-trash class="w-4 h-4 mr-1"/>
                                    </template>
                                    <span x-text="isDeleted(image.id) ? 'Undo' : 'Delete'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <p class="text-xs text-gray-500">Click an image to mark/unmark for deletion. Changes are saved on "Update Product".</p>
            </div>

            {{-- Upload New Images --}}
            <div>
                 <x-input-label for="images" :value="$product->exists && $product->hasMedia('default') ? 'Upload Additional Images' : 'Upload Images'" />
                <div class="mt-1">
                                              
                    <input type="file" name="images[]" id="images" multiple accept="image/*"
                        class="block w-full text-sm text-gray-500 border border-gray-300 rounded-md cursor-pointer
                                file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0
                                file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" >
                    <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
                </div>
                <p class="mt-1 text-sm text-gray-500">You can select multiple images (jpg, jpeg, png, gif, webp).</p>
            </div>
            </div>
        </div>
         
        {{-- Videos Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Videos</h3>
                
                {{-- Display Existing Videos --}}
                @if(isset($product) && $product->videos->isNotEmpty())
                <div class="space-y-4">
                   <x-input-label :value="__('Current Videos')" />
                   <div class="space-y-4">
                        @foreach($product->videos->sortBy('position') as $video)
                           <div class="relative p-3 border border-gray-200 rounded-md bg-gray-50">
                               <div class="flex items-center justify-between">
                                   <div class="flex-1 min-w-0">
                                       <p class="text-sm font-medium text-gray-900 truncate">{{ $video->title ?: basename($video->path) }}</p>
                                       <p class="text-sm text-gray-500 truncate">{{ $video->description ?: 'No description' }}</p>
                                   </div>
                                    
                                   <div class="ml-4 flex-shrink-0">
                                        <label for="delete_video_{{ $video->id }}" class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 cursor-pointer">
                                           <input type="checkbox" name="delete_videos[]" id="delete_video_{{ $video->id }}" value="{{ $video->id }}" class="sr-only">
                                           <x-heroicon-o-trash class="w-4 h-4" />
                                            <span class="sr-only">Mark for deletion</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                   </div>
                    <p class="text-xs text-gray-500">Check the trash icon to mark a video for deletion upon saving.</p>
                    <x-input-error :messages="$errors->get('delete_videos.*')" class="mt-2" />
                </div>
                @endif
               
               {{-- Upload New Videos Section --}}
               <div>
                   <x-input-label for="video-upload-input" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ isset($product) && $product->videos->isNotEmpty() ? 'Upload Additional Videos' : 'Upload Videos' }}
                   </x-input-label>
                   
                   <div id="video-container" class="space-y-6">
                       <div class="video-entry space-y-4 p-4 border border-dashed rounded-md">
                           <div>
                               <input type="file" name="videos[]" id="video-upload-input"
                                      class="block w-full text-sm text-gray-500 border border-gray-300 rounded-md cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                      accept="video/mp4,video/mov,video/avi,video/webm">
                               <x-input-error :messages="$errors->get('videos.*')" class="mt-1" />
                               <p class="mt-1 text-sm text-gray-500">Accepted: MP4, MOV, AVI, WEBM.</p>
                           </div>
                           <div>
                               <x-text-input type="text" name="video_titles[]" class="mt-1 block w-full" placeholder="Video Title (Optional)" />
                               <x-input-error :messages="$errors->get('video_titles.*')" class="mt-1" />
                           </div>
                           <div>
                               <textarea name="video_descriptions[]" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Video Description (Optional)"></textarea>
                               <x-input-error :messages="$errors->get('video_descriptions.*')" class="mt-1" />
                           </div>
                       </div>
                   </div>
                   <button type="button" id="add-video" class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                       <x-heroicon-o-plus class="-ml-1 mr-2 h-5 w-5 text-gray-400"/>
                       Add Another Video Field
                   </button>
               </div>
           </div>
       </div>
    </div>
</div>

{{-- Action Buttons --}}
<div class="mt-8 pt-5 border-t border-gray-200">
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.products.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Cancel
        </a>
        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ isset($product) && $product->exists ? 'Update Product' : 'Create Product' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
    function cartesian(...args) {
        let r = [], max = args.length - 1;
        function helper(arr, i) {
            let currentArg = Array.isArray(args[i]) ? args[i] : [];
            for (let j = 0, l = currentArg.length; j < l; j++) {
                let a = arr.slice(0); a.push(currentArg[j]);
                if (i === max) r.push(a); else helper(a, i + 1);
            }
        }
        if (args.length > 0 && args.every(Array.isArray)) { helper([], 0); }
        return r;
    }

    function productForm(config) {
        return {
             hasVariants: config.hasVariants,
            allAttributes: config.allAttributes,
            selectedAttributeIds: config.initialSelectedAttributeIds,
            selectedValues: {},
            variants: [],
            specifications: [],
            deletedVariants: [],
            deletedSpecifications: [],
            
            init() {
                this.allAttributes.forEach(attr => {
                    this.selectedValues[String(attr.id)] = [];
                });
                                
                (config.initialVariants || []).forEach(v => {
                    const valueIds = (v.attribute_values || []).map(val => val.id);
                    const variantImage = v.media?.find(m => m.collection_name === 'variant_image');
                    this.variants.push({
                        id: v.id, clientId: `v_${Math.random()}`, attributeValueIds: valueIds,
                        price: v.price, sku: v.sku, quantity: v.quantity,
                        image_url: variantImage ? variantImage.original_url : null,
                    });
                    (v.attribute_values || []).forEach(val => {
                        const attrIdStr = String(val.attribute_id);
                        if (!this.selectedValues[attrIdStr].includes(val.id)) {
                            this.selectedValues[attrIdStr].push(val.id);
                        }
                    });
                });

                (config.initialSpecifications || []).forEach(s => {
                    if(s.key || s.value) {
                        this.specifications.push({ clientId: `s_${Math.random()}`, key: s.key, value: s.value });
                    }
                });
            },
            
            getAttributeName(id) { return this.allAttributes.find(a => a.id == id)?.name || ''; },
            getAttributeValues(id) { return this.allAttributes.find(a => a.id == id)?.values || []; },
            getVariantName(valueIds) {
                return valueIds.map(id => {
                    for (const attr of this.allAttributes) {
                        const value = attr.values.find(v => v.id === id);
                        if (value) return value.value;
                    } return '?';
                }).sort().join(' / ');
            },
            canGenerateVariants() {
                if (this.selectedAttributeIds.length === 0) return false;
                return this.selectedAttributeIds.every(id => this.selectedValues[id]?.length > 0);
            },
                        
            generateVariants() {
                const valueArrays = this.selectedAttributeIds.map(id => this.selectedValues[id]);
                const combinations = cartesian(...valueArrays);
                const allCurrentVariants = [...this.variants, ...this.deletedVariants];
                const existingSignatures = new Set(allCurrentVariants.map(v => v.attributeValueIds.sort((a,b)=>a-b).join('-')));
                                
                combinations.forEach(combo => {
                    const sortedCombo = [...combo].sort((a,b)=>a-b);
                    const signature = sortedCombo.join('-');
                    if (!existingSignatures.has(signature)) {
                        this.variants.push({
                            id: null, clientId: `v_${Math.random()}`, attributeValueIds: sortedCombo,
                            price: config.basePrice, sku: '', quantity: 0, image_url: null,
                        });
                    }
                });
            },
            
            deleteVariant(clientId) {
                const index = this.variants.findIndex(v => v.clientId === clientId);
                if (index > -1) {
                    if (this.variants[index].id) {
                        this.deletedVariants.push(this.variants[index]);
                    }
                    this.variants.splice(index, 1);
                }
            },
            undoVariantDeletion() {
                if (this.deletedVariants.length > 0) {
                    this.variants.push(this.deletedVariants.pop());
                }
            },
            
            addSpecification() { this.specifications.push({ clientId: `s_${Math.random()}`, key: '', value: '' }); },
            deleteSpecification(clientId) {
                const index = this.specifications.findIndex(s => s.clientId === clientId);
                if (index > -1) {
                    this.deletedSpecifications.push(this.specifications[index]);
                    this.specifications.splice(index, 1);
                }
            },
            undoSpecificationDeletion() {
                if (this.deletedSpecifications.length > 0) {
                    this.specifications.push(this.deletedSpecifications.pop());
                }
            },
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Slug Generation Helper
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        if (nameInput && slugInput) {
            nameInput.addEventListener('blur', function() {
                if (slugInput.value === '') {
                    const slug = nameInput.value
                        .toLowerCase()
                        .trim()
                        .replace(/\s+/g, '-')
                        .replace(/[^\w\-]+/g, '')
                        .replace(/\-\-+/g, '-')
                        .replace(/^-+/, '')
                        .replace(/-+$/, '');
                    slugInput.value = slug;
                }
            });
        }

        // Dynamic Video Fields
        const addVideoBtn = document.getElementById('add-video');
        const videoContainer = document.getElementById('video-container');
        
        function removeVideoEntry(event) {
            event.target.closest('.video-entry')?.remove();
        }

        function createVideoEntry() {
            if (!videoContainer) return;
            const videoEntry = document.createElement('div');
            videoEntry.className = 'video-entry space-y-4 pt-6 border-t border-gray-200 mt-6 relative';
            videoEntry.innerHTML = `
                <button type="button" class="absolute -top-3 right-0 inline-flex items-center justify-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 remove-video" title="Remove this video entry">
                     <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <div>
                    <input type="file" name="videos[]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="video/mp4,video/mov,video/avi,video/webm">
                    <p class="mt-1 text-sm text-gray-500">Accepted formats: MP4, MOV, AVI, WEBM (Max size check in controller)</p>
                </div>
                <div>
                    <input type="text" name="video_titles[]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Video Title (Optional)">
                </div>
                <div>
                    <textarea name="video_descriptions[]" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Video Description (Optional)"></textarea>
                </div>
            `;
            videoContainer.appendChild(videoEntry);
            
            const removeBtn = videoEntry.querySelector('.remove-video');
            if (removeBtn) {
                removeBtn.addEventListener('click', removeVideoEntry);
            }
        }

        if (addVideoBtn) {
            addVideoBtn.addEventListener('click', createVideoEntry);
        }

        const existingRemoveBtns = videoContainer ? videoContainer.querySelectorAll('.remove-video') : [];
        existingRemoveBtns.forEach(btn => {
             btn.addEventListener('click', removeVideoEntry);
        });
    });
</script>
@endpush
