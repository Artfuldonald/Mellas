{{-- resources/views/products/show.blade.php --}}
<x-app-layout>

    {{-- Optional: Breadcrumbs --}}
    <div class="bg-pink-50 py-3 border-b border-pink-100">
        <nav class="container mx-auto px-4 sm:px-6 lg:px-8 text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex space-x-2">
              <li class="flex items-center">
                <a href="{{ route('home') }}" class="hover:text-pink-600">Home</a>
                <x-heroicon-o-chevron-right class="w-3 h-3 mx-1 text-gray-400"/>
              </li>
              <li class="flex items-center">
                <a href="{{ route('products.index') }}" class="hover:text-pink-600">Shop</a>
                 {{-- Add category breadcrumb if applicable --}}
                @if($product->categories->isNotEmpty())
                    <x-heroicon-o-chevron-right class="w-3 h-3 mx-1 text-gray-400"/>
                    <a href="{{ route('products.index', ['category' => $product->categories->first()->slug]) }}" class="hover:text-pink-600">
                        {{ $product->categories->first()->name }}
                    </a>
                @endif
                 <x-heroicon-o-chevron-right class="w-3 h-3 mx-1 text-gray-400"/>
              </li>
              <li class="flex items-center">
                <span class="text-gray-400" aria-current="page">{{ $product->name }}</span>
              </li>
            </ol>
          </nav>
    </div>


    {{-- Main Product Section --}}
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Alpine component for variant interactions --}}
        <div x-data="productVariantSelector({
                productName: {{ Js::from($product->name) }},
                productBasePrice: {{ Js::from((float)$product->price) }},
                variants: {{ $variantDataForJs }},
                options: {{ $optionsDataForJs }}
             })"
             class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start"
        >

            {{-- Image Gallery (Left Column) --}}
            <div class="sticky top-24"> {{-- Make gallery sticky on large screens --}}
                {{-- Main Image Display --}}
                <div class="aspect-square w-full bg-gray-100 rounded-lg overflow-hidden mb-4 border">
                     {{-- Display first image initially, Alpine will update :src --}}
                     <img x-ref="mainImage"
                          src="{{ $product->images->first()?->path ? Storage::url($product->images->first()->path) : asset('placeholder.jpg') }}"
                          alt="{{ $product->name }}"
                          class="h-full w-full object-cover object-center transition-opacity duration-300 ease-in-out">
                </div>
                {{-- Thumbnails --}}
                @if($product->images->count() > 1)
                <div class="grid grid-cols-5 gap-2">
                    @foreach($product->images as $image)
                        <button type="button"
                                @click="changeImage('{{ Storage::url($image->path) }}', $el)"
                                class="aspect-square rounded-lg overflow-hidden border-2 hover:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                                :class="{ 'border-pink-500': $refs.mainImage.src === '{{ Storage::url($image->path) }}', 'border-transparent': $refs.mainImage.src !== '{{ Storage::url($image->path) }}' }"
                                data-image-url="{{ Storage::url($image->path) }}" {{-- Store URL for JS --}}
                                >
                            <img src="{{ Storage::url($image->path) }}" alt="Thumbnail {{ $loop->iteration }}" class="h-full w-full object-cover object-center">
                        </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Product Info & Options (Right Column) --}}
            <div>
                {{-- Product Name --}}
                <h1 class="text-3xl lg:text-4xl font-bold tracking-tight text-gray-900 mb-3" x-text="productName">
                    {{ $product->name }}
                </h1>

                {{-- Price Display - Updated by Alpine --}}
                <div class="mb-4">
                    <p class="text-3xl tracking-tight text-gray-900">
                        $<span x-text="selectedVariant ? selectedVariant.price.toFixed(2) : productBasePrice.toFixed(2)">
                            {{ number_format($product->price, 2) }}
                        </span>
                    </p>
                    {{-- TODO: Add compare at price logic if needed, potentially based on variant --}}
                </div>

                {{-- Optional: Reviews Summary Link --}}
                {{-- <div class="mb-4"> ... </div> --}}

                {{-- Description --}}
                <div class="prose prose-sm text-gray-600 mb-6">
                    {!! nl2br(e($product->description)) !!} {{-- Display description safely --}}
                </div>

                {{-- Variant Selection Options --}}
                <form> {{-- Form needed for Add to Cart later --}}
                    <div class="space-y-6" x-show="hasOptions">
                        <template x-for="(option, attributeId) in options" :key="attributeId">
                            <fieldset>
                                <legend class="text-sm font-medium text-gray-900" x-text="option.name"></legend>
                                {{-- Radio Buttons or Swatches --}}
                                <div class="mt-2 flex flex-wrap gap-3">
                                    <template x-for="value in option.values" :key="value.id">
                                        <label :for="`option-${attributeId}-${value.id}`"
                                               @click="selectOption(attributeId, value.id)"
                                               :class="{
                                                   'ring-2 ring-offset-1 ring-pink-500': selectedOptions[attributeId] == value.id,
                                                   'ring-1 ring-gray-300': selectedOptions[attributeId] != value.id,
                                                   'cursor-pointer': isOptionAvailable(attributeId, value.id),
                                                   'opacity-50 cursor-not-allowed': !isOptionAvailable(attributeId, value.id)
                                               }"
                                               class="relative flex items-center justify-center rounded-md border py-2 px-4 text-sm font-medium uppercase bg-white text-gray-900 hover:bg-gray-50 focus:outline-none sm:flex-1">
                                            <input type="radio" :name="`option-${attributeId}`" :value="value.id" :id="`option-${attributeId}-${value.id}`"
                                                   class="sr-only" :disabled="!isOptionAvailable(attributeId, value.id)"
                                                   aria-labelledby="`option-${attributeId}-${value.id}-label`">
                                            <span :id="`option-${attributeId}-${value.id}-label`" x-text="value.name"></span>
                                            {{-- Strike-through for unavailable options --}}
                                             <span x-show="!isOptionAvailable(attributeId, value.id)" aria-hidden="true" class="pointer-events-none absolute -inset-px rounded-md border-2 border-gray-200">
                                                <svg class="absolute inset-0 h-full w-full stroke-2 text-gray-200" viewBox="0 0 100 100" preserveAspectRatio="none" stroke="currentColor"><line x1="0" y1="100" x2="100" y2="0" vector-effect="non-scaling-stroke"></line></svg>
                                            </span>
                                        </label>
                                    </template>
                                </div>
                                <p class="mt-1 text-xs text-red-600" x-show="selectionError && !selectedOptions[attributeId]">Please select a {{ option.name }}.</p>
                            </fieldset>
                        </template>
                    </div>

                    {{-- Stock Status - Updated by Alpine --}}
                    <p class="mt-4 text-sm"
                       :class="{ 'text-red-600': selectedVariant && selectedVariant.quantity <= 0, 'text-green-700': selectedVariant && selectedVariant.quantity > 0, 'text-gray-500': !selectedVariant && hasOptions }"
                       x-text="stockMessage">
                       {{-- Stock message updated by JS --}}
                    </p>

                    {{-- Add to Cart Button --}}
                    <div class="mt-8">
                        <button type="submit"
                                {{-- Disable button if variant needed but not fully selected or out of stock --}}
                                :disabled="!isSelectionComplete || (selectedVariant && selectedVariant.quantity <= 0)"
                                class="flex w-full items-center justify-center rounded-md border border-transparent bg-pink-600 px-8 py-3 text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            Add to cart
                        </button>
                         <p class="text-xs text-red-600 mt-1" x-show="!isSelectionComplete && selectionError">Please make a selection for all options.</p>
                    </div>
                </form>

            </div> {{-- End Right Column --}}

        </div> {{-- End Main Grid & Alpine Component --}}
    </div> {{-- End Container --}}

    {{-- Alpine.js Logic for Variant Selection --}}
    @push('scripts')
    <script>
        function productVariantSelector(config) {
            return {
                productName: config.productName || 'Product',
                productBasePrice: config.productBasePrice || 0,
                variants: config.variants || {}, // Key: 'valueId1-valueId2', Value: { id, sku, price, quantity }
                options: config.options || {}, // Key: attributeId, Value: { name, values: [{id, name}] }
                selectedOptions: {}, // Key: attributeId, Value: selected valueId
                selectedVariant: null, // Holds the variant object matching selection
                selectionError: false, // Flag to show validation messages

                init() {
                    // Initialize selectedOptions object with null values for each attribute
                    Object.keys(this.options).forEach(attrId => {
                        this.selectedOptions[attrId] = null;
                    });
                    console.log('Initial Options:', JSON.parse(JSON.stringify(this.options)));
                    console.log('Initial Variants:', JSON.parse(JSON.stringify(this.variants)));
                },

                // --- Computed Properties ---
                get hasOptions() {
                    return Object.keys(this.options).length > 0;
                },
                get isSelectionComplete() {
                    if (!this.hasOptions) return true; // No options to select
                    // Check if a value is selected for EVERY option attribute
                    return Object.keys(this.options).every(attrId => this.selectedOptions[attrId] !== null);
                },
                 get stockMessage() {
                    if (!this.hasOptions) {
                        // TODO: Check base product stock if no variants
                        return 'In Stock'; // Placeholder
                    }
                    if (!this.isSelectionComplete) {
                        return 'Select options to see availability';
                    }
                    if (this.selectedVariant) {
                        if (this.selectedVariant.quantity > 0) {
                            return `In Stock (${this.selectedVariant.quantity} available)`;
                        } else {
                            return 'Out of Stock';
                        }
                    }
                    return 'Unavailable with selected options';
                },

                // --- Methods ---
                selectOption(attributeId, valueId) {
                    // Prevent selecting unavailable options (optional, could just rely on visual disabling)
                    // if (!this.isOptionAvailable(attributeId, valueId)) return;

                    this.selectedOptions[attributeId] = valueId;
                    this.selectionError = false; // Reset error on selection
                    this.findSelectedVariant();
                     // TODO: Potentially change main image based on selection later
                     // this.updateImageForVariant(this.selectedVariant);
                },

                findSelectedVariant() {
                    if (!this.isSelectionComplete) {
                        this.selectedVariant = null;
                        return;
                    }
                    // Create the key based on current selection, sorted
                    const selectedIds = Object.values(this.selectedOptions).sort((a, b) => a - b);
                    const variantKey = selectedIds.join('-');

                    this.selectedVariant = this.variants[variantKey] || null; // Find variant by key
                     console.log('Selected Key:', variantKey, 'Found Variant:', this.selectedVariant);
                },

                // Check if a specific value selection is possible given other selections
                isOptionAvailable(checkAttributeId, checkValueId) {
                    // If this is the only option being selected, it's always available (initially)
                    const otherSelections = Object.entries(this.selectedOptions)
                                                .filter(([attrId, valId]) => attrId != checkAttributeId && valId !== null);
                    if (otherSelections.length === 0) return true;

                    // Check all possible variant keys that include this value + other selected values
                     let possible = false;
                     const baseSelectionIds = otherSelections.map(([attrId, valId]) => valId); // IDs already selected
                     const potentialSelection = [...baseSelectionIds, checkValueId].sort((a, b) => a - b).join('-');

                      // Also need to check partial matches if more options exist
                     // This gets complex quickly. A simpler approach for now:
                     // Check if *any* variant exists containing this specific checkValueId
                     for (const key in this.variants) {
                         const variantValueIds = key.split('-').map(Number);
                         if (variantValueIds.includes(checkValueId)) {
                             // Now check if this variant is compatible with OTHER selections
                             const otherSelectedIdsInVariant = variantValueIds.filter(id => id !== checkValueId);
                             const requiredOtherIds = baseSelectionIds;
                             if (requiredOtherIds.every(reqId => otherSelectedIdsInVariant.includes(reqId))) {
                                 possible = true;
                                 break;
                             }
                         }
                     }

                     // Fallback: If complexity arises, might just check if *any* variant uses this valueId
                     // possible = Object.keys(this.variants).some(key => key.split('-').map(Number).includes(checkValueId));

                    return possible;
                },

                changeImage(imageUrl, clickedThumbnail) {
                    if(this.$refs.mainImage) {
                        this.$refs.mainImage.src = imageUrl;
                        // Optional: Update active state on thumbnails
                         this.$el.querySelectorAll('[data-image-url]').forEach(btn => {
                             btn.classList.toggle('border-pink-500', false);
                             btn.classList.toggle('border-transparent', true);
                         });
                         clickedThumbnail.classList.remove('border-transparent');
                         clickedThumbnail.classList.add('border-pink-500');
                    }
                },

                // Trigger validation on form submit attempt? (Or rely on server)
                // handleSubmit() {
                //     if (!this.isSelectionComplete && this.hasOptions) {
                //         this.selectionError = true;
                //         return false; // Prevent submission
                //     }
                //     // Add item to cart logic here (e.g., dispatch Alpine event, call AJAX)
                //     alert(`Adding variant ${this.selectedVariant.id || 'base product'} to cart!`);
                // }
            }
        }
    </script>
    @endpush

</x-app-layout>