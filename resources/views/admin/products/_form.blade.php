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
    $selectedCategories = old('categories', $product->categories->pluck('id')->toArray() ?? []);
   
    $productHasVariantsLoaded = $product->exists && $product->relationLoaded('variants') && $product->variants->isNotEmpty();
    $productHasAttributesAssociated = $product->exists && $product->relationLoaded('attributes') && $product->attributes->isNotEmpty();
    // $product->attributes()->exists() is used by your Alpine init for hasVariants, so it implies product might have attributes set for variants
    // For the initial determination of the toggle, if attributes are set (even if no variants generated yet), we consider it as "has variants" mode.
    // The Alpine's hasVariants can then be toggled by the user.

    $hasVariants = old(
        'has_variants', 
        ($productHasVariantsLoaded || $productHasAttributesAssociated) 
    );   
    $selectedAttributes = old('product_attributes', $product->attributes->pluck('id')->map(fn($id) => (string)$id)->toArray() ?? []);    
    $initialVariants = $product->exists && $product->relationLoaded('variants') ? $product->variants : collect();    
    $allAttributesForAlpine = $allAttributes ?? \App\Models\Attribute::with(['values' => fn($q) => $q->select(['id', 'attribute_id', 'value'])->orderBy('value')])->orderBy('name')->get(['id', 'name']);
    $brandsForSelect = $brandsForSelect ?? \App\Models\Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']);
    $jsProductId = $product->exists ? $product->id : null;

    
    $formSpecifications = [];
    if (!empty(old('spec_keys'))) {
        foreach (old('spec_keys') as $index => $key) {
            if (!empty($key) && isset(old('spec_values')[$index])) {
                $formSpecifications[] = ['key' => $key, 'value' => old('spec_values')[$index]];
            }
        }
    } elseif ($product->exists && $product->specifications) { 
        if (is_array($product->specifications) && !empty($product->specifications) && isset($product->specifications[0]['key'])) {
            $formSpecifications = $product->specifications;
        }
        elseif (is_array($product->specifications) && !empty($product->specifications) && !isset($product->specifications[0]['key'])) {
            foreach($product->specifications as $key => $value) {
                $formSpecifications[] = ['key' => $key, 'value' => $value];
            }
        }
    }
@endphp

<div x-data="productForm({
        productId: {{ Js::from($jsProductId) }},
        hasVariants: {{ $hasVariants ? 'true' : 'false' }},
        allAttributes: {{ Js::from($allAttributesForAlpine) }},
        initialAttributes: {{ Js::from($selectedAttributes) }},
        initialVariants: {{ Js::from($initialVariants) }},
        initialSpecifications: {{ Js::from($formSpecifications) }}
     })"
    class="grid grid-cols-1 md:grid-cols-3 gap-6"
>

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

                {{-- === SHORT DESCRIPTION === --}}
                <div>
                    <x-input-label for="short_description" :value="__('Short Description')" />
                    <textarea id="short_description" name="short_description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm @error('short_description') border-red-500 @enderror"
                    >{{ old('short_description', $product->short_description) }}</textarea>
                    <x-input-error :messages="$errors->get('short_description')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">A brief summary, often shown on product listings or quick views (max 500 chars).</p>
                </div>
                {{-- === END SHORT DESCRIPTION === --}}

                <div>
                    <x-input-label for="description" :value="__('Full Description')" />
                    <textarea id="description" name="description" rows="5"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm @error('description') border-red-500 @enderror"
                    >{{ old('description', $product->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- === SPECIFICATIONS CARD === --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Specifications</h3>
                    <button type="button" @click="addSpecification"
                            class="inline-flex items-center rounded-md bg-pink-50 px-2.5 py-1.5 text-xs font-semibold text-pink-700 shadow-sm hover:bg-pink-100">
                        <x-heroicon-s-plus class="-ml-0.5 mr-1 h-4 w-4"/> Add Specification
                    </button>
                </div>
                <p class="text-sm text-gray-500 -mt-4">Define key features or technical details (e.g., Material: Cotton, Warranty: 1 Year).</p>

                <div id="specifications-container" class="space-y-4">
                    <template x-for="(spec, index) in specifications" :key="spec.clientId">
                        <div class="grid grid-cols-1 sm:grid-cols-11 gap-x-3 items-end">
                            <div class="sm:col-span-5">
                                <x-input-label ::for="`spec_key_${spec.clientId}`" value="Spec Name / Key" class="text-xs"/>
                                <x-text-input type="text" ::name="`spec_keys[${index}]`" ::id="`spec_key_${spec.clientId}`"
                                       x-model="spec.key" placeholder="e.g., Material"
                                       class="mt-1 block w-full text-sm"/>
                            </div>
                            <div class="sm:col-span-5 mt-2 sm:mt-0">
                                <x-input-label ::for="`spec_value_${spec.clientId}`" value="Spec Value" class="text-xs"/>
                                <x-text-input type="text" ::name="`spec_values[${index}]`" ::id="`spec_value_${spec.clientId}`"
                                       x-model="spec.value" placeholder="e.g., Cotton"
                                       class="mt-1 block w-full text-sm"/>
                            </div>
                            <div class="sm:col-span-1 mt-2 sm:mt-0 flex items-end">
                                <button type="button" @click="removeSpecification(index)"
                                        class="p-1.5 text-red-500 hover:text-red-700 rounded-md hover:bg-red-50" title="Remove Specification">
                                    <x-heroicon-o-trash class="w-4 h-4"/>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <p x-show="specifications.length === 0" class="text-sm text-gray-500 italic">No specifications added yet.</p>
                {{-- Hidden input to ensure 'specifications' key is sent even if all are removed by JS --}}
                <input type="hidden" name="specifications_submitted_flag" value="1">
            </div>
        </div>
        {{-- === END SPECIFICATIONS CARD === --}}

        {{-- Pricing Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                 <h3 class="text-lg font-medium leading-6 text-gray-900">Pricing<span class="text-red-500">*</span></h3>
                 <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="price" :value="__('Price')" /> 
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"> <span class="text-gray-500 sm:text-sm">$</span> </div>
                            <x-text-input type="number" name="price" id="price" step="0.01" min="0" :value="old('price', $product->price ?? '')" required class="block w-full pl-7 pr-12" placeholder="0.00"/>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"> <span class="text-gray-500 sm:text-sm">USD</span> </div>
                        </div>
                         <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>
                     <div>
                        <x-input-label for="compare_at_price" :value="__('Compare-at Price')" />
                         <div class="relative mt-1 rounded-md shadow-sm">
                             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"> <span class="text-gray-500 sm:text-sm">$</span> </div>
                            <x-text-input type="number" name="compare_at_price" id="compare_at_price" step="0.01" min="0" :value="old('compare_at_price', $product->compare_at_price ?? '')" class="block w-full pl-7 pr-12" placeholder="0.00"/>
                             <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"> <span class="text-gray-500 sm:text-sm">USD</span> </div>
                         </div>
                        <x-input-error :messages="$errors->get('compare_at_price')" class="mt-2" />
                    </div>
                     <div>
                        <x-input-label for="cost_price" :value="__('Cost Price')" />
                         <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"> <span class="text-gray-500 sm:text-sm">$</span> </div>
                            <x-text-input type="number" name="cost_price" id="cost_price" step="0.01" min="0" :value="old('cost_price', $product->cost_price ?? '')" class="block w-full pl-7 pr-12" placeholder="0.00"/>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"> <span class="text-gray-500 sm:text-sm">USD</span> </div>
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
        <div class="bg-white shadow sm:rounded-lg" x-show="hasVariants" x-transition.opacity style="display: none;">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Product Variants</h3>
                <p class="text-sm text-gray-500 -mt-4">Define attributes and generate variants with unique SKU, price, and quantity.</p>

                {{-- 1. Attribute Selection --}}
                <div>
                    <x-input-label for="product_attributes" :value="__('Variant Attributes')" />
                    <select name="product_attributes[]" id="product_attributes" multiple
                            x-model="selectedAttributeIds" {{-- Binds to array of strings --}}
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <template x-if="allAttributes && allAttributes.length > 0">
                            <template x-for="attribute in allAttributes" :key="attribute.id">
                                {{-- Option value must be string for x-model --}}
                                <option :value="String(attribute.id)" x-text="attribute.name"></option>
                            </template>
                        </template>
                        <option value="" disabled x-show="!allAttributes || allAttributes.length === 0">No attributes defined</option>
                    </select>
                    <p class="mt-2 text-sm text-gray-500">Select attributes like Color, Size. Manage global attributes <a href="{{ route('admin.attributes.index') }}" target="_blank" class="text-indigo-600 hover:underline">here</a>.</p>
                    <x-input-error :messages="$errors->get('product_attributes')" class="mt-2" />
                    <x-input-error :messages="$errors->get('product_attributes.*')" class="mt-2" />
                </div>

                {{-- 2. Attribute Value Selection --}}
                <div class="space-y-4" x-show="selectedAttributeIds.length > 0" x-transition>
                     <h4 class="text-md font-medium text-gray-800 border-b pb-1">Configure Attribute Values</h4>
                     <template x-for="attributeIdStr in selectedAttributeIds" :key="attributeIdStr">
                         {{-- Use number internally if needed --}}
                        <div class="p-3 border rounded-md bg-gray-50/50" x-data="{ attributeIdNum: Number(attributeIdStr) }">
                             <label x-text="getAttributeName(attributeIdNum)" class="block text-sm font-medium text-gray-700 mb-2"></label>
                             <div class="flex flex-wrap gap-2">
                                 <template x-if="getAttributeValues(attributeIdNum).length > 0">
                                      <template x-for="value in getAttributeValues(attributeIdNum)" :key="value.id">
                                         <label class="inline-flex items-center px-3 py-1 bg-white border border-gray-300 rounded-full text-sm cursor-pointer hover:bg-gray-100 has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-300 has-[:checked]:text-indigo-900">
                                             <input type="checkbox"
                                                    :name="`attribute_values[${attributeIdNum}][]`"
                                                    :value="value.id"
                                                    x-model="selectedValues[attributeIdNum]" {{-- Use number key --}}
                                                    class="form-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2 h-4 w-4">
                                             <span x-text="value.value"></span>
                                         </label>
                                     </template>
                                 </template>
                                 <span x-show="getAttributeValues(attributeIdNum).length === 0" class="text-sm text-gray-500 italic">No values defined.</span>
                                  <a :href="`{{ url('admin/attributes') }}/${attributeIdNum}/edit`" target="_blank" class="text-indigo-600 text-sm self-center ml-2 hover:underline" title="Add/Edit Values">+ Add/Edit Values</a>
                             </div>
                         </div>
                     </template>
                     <x-input-error :messages="$errors->get('attribute_values')" class="mt-2" />
                     <x-input-error :messages="$errors->get('attribute_values.*')" class="mt-2" />
                     <x-input-error :messages="$errors->get('attribute_values.*.*')" class="mt-2" />
                </div>

                {{-- 3. Generate Variants Button --}}
                <div x-show="canGenerateVariants()" x-transition>
                     <button type="button" @click.prevent="generateVariants"
                             class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                         Generate Variants
                     </button>
                     <p class="mt-1 text-xs text-gray-500">Generates combinations from selected values.</p>
                </div>

                {{-- 4. Variants Table --}}
                <div id="variants-table-container" class="mt-6" x-show="variants.length > 0" x-transition>
                    <h4 class="text-md font-medium text-gray-800 mb-2">Generated Variants</h4>
                    <div class="overflow-x-auto border border-gray-200 rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Variant</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity <span class="text-red-500">*</span></th>
                                    <th class="relative px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(variant, index) in variants" :key="variant.clientId">
                                    <tr>
                                        {{-- Hidden inputs --}}
                                        <input type="hidden" :name="`variants[${index}][id]`" :value="variant.id">
                                        <template x-for="valueId in variant.attributeValueIds" :key="valueId">
                                            <input type="hidden" :name="`variants[${index}][attribute_value_ids][]`" :value="valueId">
                                        </template>
                                        {{-- Variant Name --}}
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900" x-text="getVariantName(variant.attributeValueIds)"></td>
                                        {{-- Price Input --}}
                                        <td class="px-4 py-2">
                                            <label :for="`variant_price_${index}`" class="sr-only">Price</label>
                                            <x-text-input type="number" ::name="`variants[${index}][price]`" ::id="`variant_price_${index}`"
                                                   x-model.number="variant.price"
                                                   step="0.01" min="0" required placeholder="0.00"
                                                   class="block w-24"/>
                                        </td>
                                        {{-- SKU Input --}}
                                        <td class="px-4 py-2">
                                             <label :for="`variant_sku_${index}`" class="sr-only">SKU</label>
                                            <x-text-input type="text" ::name="`variants[${index}][sku]`" ::id="`variant_sku_${index}`"
                                                   x-model.lazy="variant.sku"
                                                   required placeholder="VARIANT-SKU"
                                                   class="block w-36"/>
                                        </td>
                                        {{-- Quantity Input --}}
                                        <td class="px-4 py-2">
                                             <label :for="`variant_qty_${index}`" class="sr-only">Quantity</label>
                                            <x-text-input type="number" ::name="`variants[${index}][quantity]`" ::id="`variant_qty_${index}`"
                                                   x-model.number="variant.quantity"
                                                   min="0" step="1" required placeholder="0"
                                                   class="block w-20"/>
                                        </td>
                                        {{-- ACTIONS - Only Remove Button --}}
                                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm space-x-2">
                                            {{-- ADJUST STOCK LINK REMOVED --}}
                                            <button type="button" @click.prevent="removeVariant(variant.clientId)"
                                                    class="text-red-600 hover:text-red-800 inline-flex items-center" title="Remove Variant Row">
                                                 <x-heroicon-o-trash class="w-4 h-4"/>
                                                 <span class="sr-only">Remove</span>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    {{-- Total Stock Display --}}
                     <div class="mt-4 pt-4 border-t border-gray-200" x-show="hasVariants && variants.length > 0">
                        <p class="text-sm font-medium text-gray-700">
                            Total Stock (All Variants):
                            <span x-text="variants.reduce((sum, v) => sum + parseInt(v.quantity || 0), 0)"
                                  class="font-bold text-gray-900 ml-2">
                            </span>
                        </p>
                     </div>
                </div>

                 {{-- Messages for variant generation --}}
                <p x-show="variants.length === 0 && selectedAttributeIds.length > 0 && canGenerateVariants()" class="text-sm text-gray-500 mt-4 italic">
                    Click "Generate Variants" to create combinations based on selected values.
                </p>
                 <p x-show="variants.length === 0 && !canGenerateVariants() && selectedAttributeIds.length > 0" class="text-sm text-gray-500 mt-4 italic">
                    Please select at least one value for each chosen attribute to generate variants.
                </p>
                 <x-input-error :messages="$errors->get('variants')" class="mt-2" />
                 <x-input-error :messages="$errors->get('variants.*')" class="mt-2" />
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
                            <option value="kg" {{ old('weight_unit', $product->weight_unit ?? 'kg') == 'kg' ? 'selected' : '' }}>kg</option> {{-- Default kg --}}
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
                               {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} > {{-- Default active --}}
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

                {{-- === BRAND SELECTION === --}}
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
                {{-- Display Existing Images --}}
                @if(isset($product) && $product->images->isNotEmpty())
                <div class="space-y-3">
                    <x-input-label :value="__('Current Images')" />
                    <div class="grid grid-cols-3 gap-4">
                        @foreach($product->images->sortBy('position') as $image)
                            <div class="relative group">
                                {{-- Added placeholder logic and aspect-square --}}
                                <img src="{{ $image->path ? Storage::url($image->path) : asset('path/to/your/placeholder.jpg') }}"
                                     alt="{{ $image->alt ?? $product->name }}"
                                     class="block w-full aspect-square object-cover rounded-md border border-gray-200 bg-gray-100"> {{-- Added bg-gray-100 --}}
                                {{-- Delete Checkbox Overlay --}}
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity duration-200 flex items-center justify-center">
                                    {{-- Use unique ID for label's 'for' attribute --}}
                                    <label for="delete_image_{{ $image->id }}"
                                           class="hidden group-hover:inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 cursor-pointer">
                                        {{-- Hide the actual checkbox using sr-only --}}
                                        <input type="checkbox" name="delete_images[]" id="delete_image_{{ $image->id }}" value="{{ $image->id }}" class="sr-only">
                                        <x-heroicon-o-trash class="w-4 h-4 mr-1"/> Delete
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500">Hover over an image and click delete to mark it for removal on update.</p>
                     <x-input-error :messages="$errors->get('delete_images.*')" class="mt-2" />
                </div>
                @endif

                {{-- Upload New Images --}}
                <div>
                    <x-input-label for="images" :value="isset($product) && $product->images->isNotEmpty() ? 'Upload Additional Images' : 'Upload Images'" />
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
                                         {{-- Label wraps hidden checkbox for better click area --}}
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
                   {{-- Container for dynamic video fields JS --}}
                   <div id="video-container" class="space-y-6">
                       {{-- Initial block (template for JS) --}}
                       <div class="video-entry space-y-4 p-4 border border-dashed rounded-md">
                           <div>
                               <label class="block text-sm font-medium text-gray-700 sr-only">Upload Video File</label>
                               <input type="file" name="videos[]" id="video-upload-input"
                                      class="block w-full text-sm text-gray-500 border border-gray-300 rounded-md cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                      accept="video/mp4,video/mov,video/avi,video/webm">
                               <x-input-error :messages="$errors->get('videos.*')" class="mt-1" />
                               <p class="mt-1 text-sm text-gray-500">Accepted: MP4, MOV, AVI, WEBM.</p>
                           </div>
                           <div>
                               <label class="block text-sm font-medium text-gray-700 sr-only">Video Title</label>
                               <x-text-input type="text" name="video_titles[]" class="mt-1 block w-full" placeholder="Video Title (Optional)" />
                               <x-input-error :messages="$errors->get('video_titles.*')" class="mt-1" />
                           </div>
                           <div>
                               <label class="block text-sm font-medium text-gray-700 sr-only">Video Description</label>
                               <textarea name="video_descriptions[]" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Video Description (Optional)"></textarea>
                               <x-input-error :messages="$errors->get('video_descriptions.*')" class="mt-1" />
                           </div>
                           {{-- JS adds remove button here --}}
                       </div>
                   </div>
                   <button type="button" id="add-video" class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                       <x-heroicon-o-plus class="-ml-1 mr-2 h-5 w-5 text-gray-400"/>
                       Add Another Video Field
                   </button>
               </div>
           </div>
       </div> {{-- End Videos Card --}}

    </div> {{-- End Sidebar Column --}}

</div> {{-- End Grid --}}


{{-- Action Buttons --}}
<div class="mt-8 pt-5 border-t border-gray-200">
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.products.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Cancel
        </a>
        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{-- Check if $product exists and has an ID --}}
            {{ isset($product) && $product->exists ? 'Update Product' : 'Create Product' }}
        </button>
    </div>
</div>

{{-- dynamic elements --}}
@push('scripts')
<script>
    // Helper function to calculate the Cartesian product (combinations)
    // Needs to be defined before productForm or globally
    function cartesian(...args) {
        const r = [], max = args.length - 1;
        function helper(arr, i) {
            // Ensure the current argument is an array before accessing length
            const currentArg = Array.isArray(args[i]) ? args[i] : [];
            for (let j = 0, l = currentArg.length; j < l; j++) {
                const a = arr.slice(0); // clone arr
                a.push(currentArg[j]);
                if (i === max)
                    r.push(a);
                else
                    helper(a, i + 1);
            }
        }
        if (args.length > 0 && args.every(Array.isArray)) { // Only run if args exist and are arrays
            helper([], 0);
        }
        return r;
    }

    // Main Alpine.js component logic function
    function productForm(config) {
        return {
            // --- State Properties (ensure these are defined) ---
            hasVariants: config.hasVariants || false,
            allAttributes: config.allAttributes || [], // Expects: [{id: 1, name: 'Color', values: [{id: 10, value: 'Red'}, ...]}, ...]
            selectedAttributeIds: [], // Will be populated by init, ensure they are numbers
            selectedValues: {}, // Object keyed by attributeId, value is array of selected value IDs: { 1: [10, 11], 2: [20] }
            variants: [], // Array of variant objects: { id: null|int, clientId: '...', attributeValueIds: [10, 20], price: 0, sku: '', quantity: 0, markedForDeletion: false }

            specifications: [],

            // --- Initialization ---
            init() {
                 console.log('Alpine init started');
                 console.log('Initial Config:', JSON.parse(JSON.stringify(config))); // Deep copy for logging

                 const initialIdsAsStrings = (config.initialAttributes || []).map(id => String(id));
                console.log('Prepared initial IDs as strings:', initialIdsAsStrings);

                // Initialize the array property, but potentially set the actual value later
                this.selectedAttributeIds = []; // Start empty or with the prepared array

                // Defer setting the value bound to x-model until the next DOM update cycle
                this.$nextTick(() => {
                    this.selectedAttributeIds = initialIdsAsStrings;
                    console.log('Set selectedAttributeIds inside $nextTick:', this.selectedAttributeIds);
                    // Optional: Force refresh if using a JS library like TomSelect/Choices.js
                    // const selectElement = document.getElementById('product_attributes');
                    // if (selectElement && selectElement.tomselect) { // Example for TomSelect
                    //     selectElement.tomselect.setValue(this.selectedAttributeIds);
                    //     selectElement.tomselect.refreshOptions(false); // Refresh options if needed
                    // }
                });
                            // Initialize selectedValues structure for all potential attributes
                 this.allAttributes.forEach(attr => {
                     this.selectedValues[attr.id] = []; // Start with empty arrays
                 });

                 // Populate variants and selectedValues based on initial server data
                (config.initialVariants || []).forEach(variant => {
                    // Ensure attribute_values is an array
                    const attributeValues = Array.isArray(variant.attribute_values) ? variant.attribute_values : [];
                    // Ensure IDs are numbers and sort them for consistent comparison/signature later
                    const attributeValueIds = attributeValues.map(v => Number(v.id)).sort((a, b) => a - b);
                    const clientId = `client_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`; // Unique enough for UI key

                    // Check if variant with this combination already exists in our JS state
                    const existingVariantIndex = this.variants.findIndex(v =>
                        v.attributeValueIds.length === attributeValueIds.length &&
                        v.attributeValueIds.every((valId, idx) => valId === attributeValueIds[idx])
                    );

                    if (existingVariantIndex === -1) { // Only add if not already present
                        this.variants.push({
                            id: variant.id, // Real DB ID (null if new)
                            clientId: clientId, // Unique ID for UI reactivity key
                            attributeValueIds: attributeValueIds,
                            price: Number(variant.price) || 0, // Ensure number
                            sku: variant.sku || '',
                            quantity: Number(variant.quantity) || 0, // Ensure number
                            markedForDeletion: false // Initialize deletion flag
                        });

                        // Populate selectedValues based on this variant's values
                        attributeValues.forEach(value => {
                        if (value.attribute_id && value.id) {
                            const attrId = Number(value.attribute_id); // Keep using numbers as keys for selectedValues object
                            const valId = Number(value.id);
                            if (!this.selectedValues[attrId]) {
                                this.selectedValues[attrId] = [];
                            }
                            if (!this.selectedValues[attrId].includes(valId)) {
                                this.selectedValues[attrId].push(valId);
                            }
                        }
                    });
                    }
                });

                 // Ensure selectedValues object still has keys for initially selected attributes, even if no variants used them
                 this.selectedAttributeIds.forEach(attrIdAsString =>  {
                    const attrId = Number(attrIdAsString); // Convert back to number for key lookup if needed
                        if (!this.selectedValues.hasOwnProperty(attrId)) {
                            this.selectedValues[attrId] = [];
                        }
                 });

                 console.log('Initial Selected Attributes:', this.selectedAttributeIds);
                 console.log('Initial Selected Values:', JSON.parse(JSON.stringify(this.selectedValues)));
                 console.log('Initial Variants:', JSON.parse(JSON.stringify(this.variants)));
                 console.log('Alpine init finished');


                // --- Watchers ---
                this.$watch('selectedAttributeIds', (newIdsAsStrings, oldIdsAsStrings) => {
                   
                    console.log('Attributes changed (strings):', newIdsAsStrings);
                // Convert to numbers for internal logic if necessary, but keep selectedAttributeIds as strings
                const numericNewIds = newIdsAsStrings.map(id => Number(id));
                const numericOldIds = (oldIdsAsStrings || []).map(id => Number(id)); // Handle initial undefined oldIds
                  
                const deselected = numericOldIds.filter(id => !numericNewIds.includes(id));
                deselected.forEach(id => {
                    if(this.selectedValues.hasOwnProperty(id)) {
                        this.selectedValues[id] = [];
                    }
                });
                // Example: Ensuring structure for newly selected
                numericNewIds.forEach(id => {
                    if (!this.selectedValues.hasOwnProperty(id)) { this.selectedValues[id] = []; }
                });

                       // Example: Filtering variants (ensure comparison uses numbers)
                this.variants = this.variants.filter(variant => {
                    const variantAttributeIds = [];
                    variant.attributeValueIds.forEach(valueId => { // valueId is likely number here
                        const attr = this.findAttributeContainingValue(valueId); // findAttributeContainingValue expects number
                        if(attr && !variantAttributeIds.includes(attr.id)) { // attr.id is number
                            variantAttributeIds.push(attr.id);
                        }
                    });
                    // Compare numeric arrays
                    return variantAttributeIds.every(attrId => numericNewIds.includes(attrId));
                });
                console.log('Selected Values after attr change:', JSON.parse(JSON.stringify(this.selectedValues)));
                console.log('Variants after attr change:', JSON.parse(JSON.stringify(this.variants)));
            });

                this.$watch('selectedValues', () => {
                     // Optional: Could add logic here if needed when values change,
                     // but generally handled by generate/remove actions.
                     // console.log('Selected values updated:', JSON.parse(JSON.stringify(this.selectedValues)));
                }, { deep: true }); // Use deep watch for nested object changes


                 this.$watch('hasVariants', (isVariantMode) => {
                     // This toggle mainly controls UI visibility. Backend handles logic based on submission.
                     console.log('Variant mode toggled:', isVariantMode);
                 });

                // === Initialize Specifications ===
                if (Array.isArray(config.initialSpecifications)) {
                    config.initialSpecifications.forEach(spec => {
                        this.specifications.push({
                            clientId: `spec_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`,
                            key: spec.key || '',
                            value: spec.value || ''
                        });
                    });
                }
                console.log('Initial Specifications for form:', JSON.parse(JSON.stringify(this.specifications)));
            },

            // --- Helper Methods ---
            findAttributeContainingValue(valueId) {
                 valueId = Number(valueId);
                 for (const attribute of this.allAttributes) {
                     if (Array.isArray(attribute.values) && attribute.values.some(v => v.id === valueId)) {
                         return attribute;
                     }
                 }
                 return null;
            },
            getAttributeName(attributeId) {
                attributeId = Number(attributeId);
                const attr = this.allAttributes.find(a => a.id === attributeId);
                return attr ? attr.name : 'Unknown';
            },
            getAttributeValues(attributeId) {
                attributeId = Number(attributeId);
                const attr = this.allAttributes.find(a => a.id === attributeId);
                return (attr && Array.isArray(attr.values)) ? attr.values : [];
            },
            canGenerateVariants() {
                // Check if at least one value is selected for EACH selected attribute
                 if (this.selectedAttributeIds.length === 0) return false;
                 return this.selectedAttributeIds.every(attrId =>
                    this.selectedValues.hasOwnProperty(attrId) &&
                    Array.isArray(this.selectedValues[attrId]) &&
                    this.selectedValues[attrId].length > 0
                 );
            },
            getVariantName(valueIds) {
                // Creates a display name like "Red / Large"
                return valueIds.map(valueId => {
                    valueId = Number(valueId); // Ensure number for comparison
                    const attribute = this.findAttributeContainingValue(valueId);
                    if(attribute && Array.isArray(attribute.values)) {
                        const valueObj = attribute.values.find(v => v.id === valueId);
                        return valueObj ? valueObj.value : '?';
                    }
                    return '?'; // Return placeholder if value/attribute not found
                }).sort().join(' / '); // Sort values alphabetically for consistent display
            },

            // === Specification Methods ===
            addSpecification() {
                this.specifications.push({
                    clientId: `spec_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`,
                    key: '',
                    value: ''
                });
                this.$nextTick(() => { // Focus the new key input
                    const newKeyInput = document.querySelector(`#specifications-container > div:last-child input[name^="spec_keys"]`);
                    if (newKeyInput) newKeyInput.focus();
                });
            },
            removeSpecification(index) {
                this.specifications.splice(index, 1);
            },

            // --- Core Logic ---
            generateVariants() {
                if (!this.canGenerateVariants()) {
                    alert('Please select at least one value for each chosen attribute.');
                    return;
                }
                console.log("Generating variants...");

                // Get arrays of selected value IDs for each selected attribute
                const valueArrays = this.selectedAttributeIds.map(attrId => {
                    // Ensure the value is an array before using it
                    const values = this.selectedValues[attrId];
                    return Array.isArray(values) ? values : [];
                });

                // Calculate combinations [[valId1, valIdA], [valId1, valIdB], ...]
                const combinations = cartesian(...valueArrays);

                const variantsToAdd = [];
                // Create a set of signatures for existing variants for quick lookup
                const existingSignatures = new Set(this.variants.map(v =>
                    v.attributeValueIds.slice().sort((a, b) => a - b).join('-') // Sort IDs before joining
                ));

                combinations.forEach(comboValueIds => {
                    // Ensure IDs are numbers and sort for consistent signature
                    const sortedComboIds = comboValueIds.map(id => Number(id)).sort((a, b) => a - b);
                    const signature = sortedComboIds.join('-');

                    // Only add if this combination signature doesn't already exist
                    if (!existingSignatures.has(signature)) {
                        const clientId = `client_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
                        variantsToAdd.push({
                            id: null, // New variant has null DB ID
                            clientId: clientId, // Unique ID for x-for key & removal
                            attributeValueIds: sortedComboIds,
                            // Use base product price as default, ensure it's a number
                            price: Number(document.getElementById('price')?.value) || 0,
                            sku: '', // Needs user input
                            quantity: 0, // Needs user input
                            markedForDeletion: false // Not relevant for new variants
                        });
                        // Add signature to set to prevent duplicates within this generation run
                        existingSignatures.add(signature);
                    }
                });

                // Add the new variants to the main array
                // Using spread ensures Alpine detects the array change for reactivity
                this.variants = [...this.variants, ...variantsToAdd];
                console.log('Variants after generation:', JSON.parse(JSON.stringify(this.variants)));
            },

            removeVariant(clientId) { // Use clientId to find the variant
                console.log("Attempting to remove variant with clientId:", clientId);
                const index = this.variants.findIndex(v => v.clientId === clientId);
                if (index !== -1) {
                    // Remove the variant from the Alpine array visually
                    this.variants.splice(index, 1);
                    console.log(`Removed variant (ClientID: ${clientId}) from UI.`);
                    // The backend logic handles actual deletion based on which variants
                    // are *not* submitted back or are explicitly marked in delete_variants[] (if implemented)
                } else {
                    console.error("Could not find variant with clientId:", clientId);
                }
            },

        }; // End of return object
    } // End of productForm function

    // Existing DOMContentLoaded listener for slug/video adder etc.
    document.addEventListener('DOMContentLoaded', function() {
        // --- Slug Generation Helper ---
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput) {
            nameInput.addEventListener('blur', function() {
                // Only fill slug if it's currently empty
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

         // --- START: Dynamic Video Fields ---
         const addVideoBtn = document.getElementById('add-video');
        const videoContainer = document.getElementById('video-container');

        // Function to handle removing a video entry
        function removeVideoEntry(event) {
            // event.target is the button clicked
            // Find the closest parent with the 'video-entry' class and remove it
            event.target.closest('.video-entry')?.remove();
        }

        // Function to create a new video entry block
        function createVideoEntry() {
            if (!videoContainer) return; // Safety check

            const videoEntry = document.createElement('div');
            // Add Tailwind classes for styling and spacing
            videoEntry.className = 'video-entry space-y-4 pt-6 border-t border-gray-200 mt-6 relative';

            // Use innerHTML to set the structure easily
            videoEntry.innerHTML = `
                <button type="button" class="absolute -top-3 right-0 inline-flex items-center justify-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 remove-video" title="Remove this video entry">
                     <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <div>
                    {{-- <label class="block text-sm font-medium text-gray-700 sr-only">Upload Video File</label> --}}
                    <input type="file" name="videos[]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="video/mp4,video/mov,video/avi,video/webm">
                    <p class="mt-1 text-sm text-gray-500">Accepted formats: MP4, MOV, AVI, WEBM (Max size check in controller)</p>
                    {{-- Add @error directive if needed, though tricky for dynamic arrays --}}
                </div>
                <div>
                    {{-- <label class="block text-sm font-medium text-gray-700 sr-only">Video Title</label> --}}
                    <input type="text" name="video_titles[]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Video Title (Optional)">
                     {{-- @error('video_titles.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror --}}
                </div>
                <div>
                    {{-- <label class="block text-sm font-medium text-gray-700 sr-only">Video Description</label> --}}
                    <textarea name="video_descriptions[]" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Video Description (Optional)"></textarea>
                     {{-- @error('video_descriptions.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror --}}
                </div>
            `;
            videoContainer.appendChild(videoEntry);

            // Add event listener specifically to the new remove button
            const removeBtn = videoEntry.querySelector('.remove-video');
            if (removeBtn) {
                removeBtn.addEventListener('click', removeVideoEntry);
            }
        }

        // Event listener for the main "Add Video" button
        if (addVideoBtn) {
            addVideoBtn.addEventListener('click', createVideoEntry);
        }

        // Add event listeners to any remove buttons that might exist on page load
        // (e.g., if the form was re-rendered after validation error with multiple entries)
        // Use event delegation on the container for robustness if needed, but this is simpler for now.
        const existingRemoveBtns = videoContainer ? videoContainer.querySelectorAll('.remove-video') : [];
        existingRemoveBtns.forEach(btn => {
             // Check if listener already added to prevent duplicates (though unlikely here)
             // if (!btn.dataset.listenerAdded) {
                btn.addEventListener('click', removeVideoEntry);
            //     btn.dataset.listenerAdded = true;
            // }
        });
        // --- END: Dynamic Video Fields ---

    }); // End of DOMContentLoaded
</script>
@endpush