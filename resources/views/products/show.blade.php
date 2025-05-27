{{-- resources/views/products/show.blade.php --}}
<x-app-layout :title="$product->name">

    {{-- Breadcrumbs --}}
    <div class="bg-pink-50 border-b border-pink-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex space-x-2 items-center">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-pink-600 transition-colors">Home</a>
                        <x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/>
                    </li>
                    <li class="flex items-center">
                        <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-pink-600 transition-colors">Shop</a>
                        <x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/>
                    </li>
                    @if($product->categories->isNotEmpty())
                        @php $category = $product->categories->first(); @endphp
                        <li class="flex items-center">
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="text-gray-500 hover:text-pink-600 transition-colors">{{ $category->name }}</a>
                            <x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/>
                        </li>
                    @endif
                    <li class="flex items-center">
                        <span class="text-pink-700 font-medium" aria-current="page">{{ Str::limit($product->name, 40) }}</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 xl:gap-x-12 items-start max-w-6xl mx-auto">
            {{-- Image gallery --}}
            <div x-data="{
                images: {{ Js::from($product->images->map(fn($img) => ['id' => $img->id, 'url' => $img->image_url ?? asset('images/placeholder.png'), 'alt' => $product->name])) }},
                currentImage: {{ Js::from($product->images->first()->image_url ?? asset('images/placeholder.png')) }},
                currentImageAlt: '{{ $product->name }}',
                changeImage(image) {
                    this.currentImage = image.url;
                    this.currentImageAlt = image.alt;
                }
            }" class="w-full mb-8 lg:mb-0 sticky top-24">
                <div class="aspect-w-1 aspect-h-1 w-full bg-pink-50 rounded-xl shadow-lg overflow-hidden border border-pink-100">
                    <img :src="currentImage" :alt="currentImageAlt" class="w-full h-full object-contain object-center">
                </div>
                @if(count($product->images) > 1)
                <div class="mt-4 grid grid-cols-4 sm:grid-cols-5 gap-3">
                    <template x-for="image in images" :key="image.id">
                        <button @click="changeImage(image)"
                                :class="{ 'ring-2 ring-pink-500 ring-offset-2': currentImage === image.url }"
                                class="aspect-w-1 aspect-h-1 bg-gray-100 rounded-lg overflow-hidden focus:outline-none">
                            <img :src="image.url" :alt="image.alt + ' thumbnail'" class="w-full h-full object-cover object-center">
                        </button>
                    </template>
                </div>
                @endif
            </div>

            {{-- Product info --}}
            <div x-data="productVariantSelector({
                productName: '{{ $product->name }}',
                productBasePrice: {{ (float)$product->price }},
                variants: {{ $variantDataForJs }},
                options: {{ $optionsDataForJs }}
            })" class="bg-white p-6 sm:p-8 rounded-xl shadow-xl">
                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">{{ $product->name }}</h1>

                {{-- Optional: Brand --}}
                {{-- <p class="text-sm text-gray-500 mt-1">Brand: <span class="font-medium text-pink-600">ExampleBrand</span></p> --}}

                <div class="mt-4">
                    <p class="text-4xl font-extrabold text-pink-600" x-text="`$${currentPrice.toFixed(2)}`"></p>
                    {{-- Optional: Original price for discounts --}}
                    {{-- <p class="text-sm text-gray-500 line-through" x-show="originalPrice > currentPrice" x-text="`$${originalPrice.toFixed(2)}`"></p> --}}
                </div>

                {{-- Optional: Reviews Placeholder --}}
                <div class="mt-4 flex items-center">
                    <div class="flex items-center">
                        @for ($i = 0; $i < 5; $i++)
                            <x-heroicon-s-star class="h-5 w-5 {{ $i < 4 ? 'text-yellow-400' : 'text-gray-300' }}" /> {{-- Example static rating --}}
                        @endfor
                    </div>
                    <a href="#reviews" class="ml-3 text-sm font-medium text-pink-600 hover:text-pink-500">(12 Reviews)</a> {{-- Example --}}
                </div>

                @if($product->short_description)
                    <div class="mt-6 text-gray-600 space-y-3 prose prose-sm max-w-none">
                        {!! nl2br(e($product->short_description)) !!}
                    </div>
                @endif

                {{-- Variant selections --}}
                <form @submit.prevent="addToCart" class="mt-8 space-y-6">
                    <template x-for="(attribute, attributeId) in options" :key="attributeId">
                        <div x-show="attribute.values.length > 0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900" x-text="attribute.name"></h3>
                                {{-- Optional: Size guide link --}}
                                {{-- <a href="#" class="text-sm font-medium text-pink-600 hover:text-pink-500" x-show="attribute.name.toLowerCase() === 'size'">Size guide</a> --}}
                            </div>

                            <fieldset class="mt-2">
                                <legend class="sr-only" x-text="`Choose a ${attribute.name}`"></legend>
                                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                                    <template x-for="value in attribute.values" :key="value.id">
                                        <label
                                            @click="selectOption(attributeId, value.id)"
                                            :class="{
                                                'ring-2 ring-pink-500 border-pink-500 bg-pink-50 text-pink-700': isSelected(attributeId, value.id),
                                                'border-gray-300 text-gray-900 hover:bg-gray-50': !isSelected(attributeId, value.id) && isOptionAvailable(attributeId, value.id),
                                                'border-gray-200 text-gray-400 bg-gray-50 cursor-not-allowed opacity-50': !isOptionAvailable(attributeId, value.id) && !isSelected(attributeId, value.id)
                                            }"
                                            class="border rounded-md py-3 px-3 flex items-center justify-center text-sm font-medium uppercase cursor-pointer focus:outline-none transition-all">
                                            <input type="radio" :name="`option_${attributeId}`" :value="value.id" x-model="selectedOptions[attributeId]" class="sr-only" :disabled="!isOptionAvailable(attributeId, value.id) && !isSelected(attributeId, value.id)">
                                            <span x-text="value.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </fieldset>
                        </div>
                    </template>

                    {{-- Quantity --}}
                     <div class="mt-8" x-show="selectedVariant && selectedVariant.quantity > 0">
                        <label for="quantity" class="block text-sm font-medium text-gray-900 mb-1">Quantity</label>
                        <div class="relative flex items-center max-w-[8rem]">
                            <button type="button" @click="quantity > 1 ? quantity-- : null" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-l-lg p-2.5 h-10 focus:ring-gray-100 focus:ring-2 focus:outline-none">
                                <x-heroicon-s-minus class="w-4 h-4 text-gray-900"/>
                            </button>
                            <input type="number" id="quantity" name="quantity" x-model.number="quantity" min="1" :max="selectedVariant ? selectedVariant.quantity : 1" class="bg-gray-50 border-x-0 border-gray-300 h-10 text-center text-gray-900 text-sm focus:ring-pink-500 focus:border-pink-500 block w-full py-2.5" placeholder="1" required>
                            <button type="button" @click="selectedVariant && quantity < selectedVariant.quantity ? quantity++ : null" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-r-lg p-2.5 h-10 focus:ring-gray-100 focus:ring-2 focus:outline-none">
                                <x-heroicon-s-plus class="w-4 h-4 text-gray-900"/>
                            </button>
                        </div>
                    </div>

                    {{-- Availability Message --}}
                    <div class="mt-4 text-sm" x-cloak>
                        <p x-show="allOptionsSelected && selectedVariant && selectedVariant.quantity > 0" class="text-green-600 font-medium">
                            <x-heroicon-s-check-circle class="inline-block w-5 h-5 mr-1"/>
                            In Stock (<span x-text="selectedVariant.quantity"></span> available)
                        </p>
                        <p x-show="allOptionsSelected && selectedVariant && selectedVariant.quantity === 0" class="text-red-600 font-medium">
                            <x-heroicon-s-x-circle class="inline-block w-5 h-5 mr-1"/>
                            Currently unavailable for this selection.
                        </p>
                        <p x-show="!allOptionsSelected && Object.keys(options).length > 0" class="text-yellow-600 font-medium">
                            <x-heroicon-s-exclamation-triangle class="inline-block w-5 h-5 mr-1"/>
                            Please select all options to check availability.
                        </p>
                         <p x-show="Object.keys(options).length === 0 && productBaseQuantity > 0" class="text-green-600 font-medium">
                            <x-heroicon-s-check-circle class="inline-block w-5 h-5 mr-1"/>
                            In Stock
                        </p>
                        <p x-show="Object.keys(options).length === 0 && productBaseQuantity === 0" class="text-red-600 font-medium">
                            <x-heroicon-s-x-circle class="inline-block w-5 h-5 mr-1"/>
                            Out of Stock
                        </p>
                    </div>

                    <button type="submit"
                            :disabled="!canAddToCart"
                            class="mt-10 w-full bg-pink-600 border border-transparent rounded-lg py-3.5 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 disabled:bg-gray-300 disabled:cursor-not-allowed disabled:text-gray-500 transition-colors shadow-lg hover:shadow-xl">
                        <x-heroicon-s-shopping-cart class="w-5 h-5 mr-2"/>
                        <span x-text="addToCartText"></span>
                    </button>
                </form>

                {{-- Optional: Wishlist, Compare --}}
                <div class="mt-6 flex justify-center space-x-4">
                    <button type="button" class="text-sm font-medium text-pink-600 hover:text-pink-500 flex items-center">
                        <x-heroicon-o-heart class="w-5 h-5 mr-1.5"/> Add to wishlist
                    </button>
                    {{-- <button type="button" class="text-sm font-medium text-pink-600 hover:text-pink-500 flex items-center">
                        <x-heroicon-o-arrows-right-left class="w-5 h-5 mr-1.5"/> Add to compare
                    </button> --}}
                </div>
            </div>
        </div>

        {{-- Product Details, Reviews, etc. (Tabs) --}}
        <div class="mt-16 lg:mt-20" x-data="{ activeTab: 'description' }">
            <div class="border-b border-pink-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'description'"
                           :class="{ 'border-pink-500 text-pink-600': activeTab === 'description', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'description' }"
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Description
                    </button>
                    {{-- Example for specifications --}}
                    @if($product->specifications && count((array)$product->specifications) > 0)
                    <button @click="activeTab = 'specifications'"
                           :class="{ 'border-pink-500 text-pink-600': activeTab === 'specifications', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'specifications' }"
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Specifications
                    </button>
                    @endif
                    <button @click="activeTab = 'reviews'" id="reviews"
                           :class="{ 'border-pink-500 text-pink-600': activeTab === 'reviews', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'reviews' }"
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Reviews (12) {{-- Replace with dynamic count --}}
                    </button>
                </nav>
            </div>

            <div class="mt-8">
                <div x-show="activeTab === 'description'" class="prose prose-pink max-w-none text-gray-600">
                    {!! $product->description ?: '<p>No description available for this product.</p>' !!}
                </div>

                @if($product->specifications && count((array)$product->specifications) > 0)
                <div x-show="activeTab === 'specifications'" x-cloak>
                    <dl class="divide-y divide-gray-200">
                        @foreach((array)$product->specifications as $specKey => $specValue)
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-700">{{ Str::title(str_replace('_', ' ', $specKey)) }}</dt>
                            <dd class="mt-1 text-sm text-gray-600 sm:mt-0 sm:col-span-2">{{ $specValue }}</dd>
                        </div>
                        @endforeach
                    </dl>
                </div>
                @endif

                <div x-show="activeTab === 'reviews'" x-cloak>
                    <h3 class="text-xl font-medium text-gray-900 mb-4">Customer Reviews</h3>
                    {{-- Placeholder for reviews list --}}
                    <div class="space-y-6">
                        <p class="text-gray-500">No reviews yet. Be the first to review this product!</p>
                        {{-- Example Review Item
                        <div class="border-b border-gray-200 pb-6">
                            <div class="flex items-center mb-1">
                                <x-heroicon-s-star class="h-5 w-5 text-yellow-400" /> ...
                                <p class="ml-2 text-sm font-medium text-gray-900">Jane Doe</p>
                            </div>
                            <p class="text-xs text-gray-500">March 10, 2024</p>
                            <p class="mt-2 text-gray-600">This product is amazing! Highly recommend.</p>
                        </div>
                         --}}
                    </div>
                    {{-- Placeholder for review form --}}
                    <div class="mt-8 bg-pink-50 p-6 rounded-lg">
                        <h4 class="text-lg font-medium text-pink-700 mb-3">Write a review</h4>
                        <form action="#" method="POST">
                            @csrf
                            {{-- Form fields: Rating, Name, Email, Comment --}}
                            <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                Submit Review
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Related Products --}}
        {{-- @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
        <section aria-labelledby="related-products-heading" class="mt-16 lg:mt-20">
            <h2 id="related-products-heading" class="text-2xl font-bold tracking-tight text-gray-900 text-center mb-8">
                You May Also Like
            </h2>
            <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8">
                @foreach($relatedProducts as $relatedProduct)
                    <x-product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </section>
        @endif --}}

    </div>
</x-app-layout>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productVariantSelector', (config) => ({
        productName: config.productName || 'Product',
        productBasePrice: config.productBasePrice || 0,
        // If product has no variants/options, quantity might be on the product itself
        productBaseQuantity: {{ $product->quantity ?? ($product->variants->isEmpty() ? 0 : -1) }}, // -1 if variants exist but not checking base
        variants: config.variants || {},
        options: config.options || {}, // { attribute_id: { name: 'Color', values: [{id: value_id, name: 'Red'}, ...] } }
        selectedOptions: {}, // { attribute_id: value_id, ... }
        selectedVariant: null,
        quantity: 1,
        currentPrice: config.productBasePrice || 0,
        // originalPrice: config.productBasePrice || 0, // For showing discounts

        init() {
            this.currentPrice = this.productBasePrice;
            // Pre-select first available option if only one choice for an attribute
            // Or if you want to pre-select defaults based on some logic
            // For now, users must select all.
            if (Object.keys(this.options).length === 0) { // No variants
                 this.allOptionsSelected = true; // Treat as all selected
            } else {
                 this.allOptionsSelected = false;
            }
        },

        get addToCartText() {
            if (!this.allOptionsSelected && Object.keys(this.options).length > 0) return 'Select Options';
            if (this.selectedVariant && this.selectedVariant.quantity > 0) return 'Add to Cart';
            if (this.selectedVariant && this.selectedVariant.quantity === 0) return 'Out of Stock';
            if (Object.keys(this.options).length === 0 && this.productBaseQuantity > 0) return 'Add to Cart';
            if (Object.keys(this.options).length === 0 && this.productBaseQuantity === 0) return 'Out of Stock';
            return 'Unavailable';
        },

        get canAddToCart() {
            if (Object.keys(this.options).length > 0) { // Product has variants
                return this.allOptionsSelected && this.selectedVariant && this.selectedVariant.quantity > 0;
            }
            // Product has no variants, check base quantity
            return this.productBaseQuantity > 0;
        },

        selectOption(attributeId, valueId) {
            this.selectedOptions[attributeId] = valueId;
            this.updateSelectedVariant();
        },

        isSelected(attributeId, valueId) {
            return this.selectedOptions[attributeId] === valueId;
        },

        updateSelectedVariant() {
            const numOptionTypes = Object.keys(this.options).length;
            const numSelectedOptionTypes = Object.keys(this.selectedOptions).length;

            this.allOptionsSelected = numOptionTypes === numSelectedOptionTypes;

            if (this.allOptionsSelected) {
                const selectedValueIds = Object.values(this.selectedOptions).sort((a, b) => a - b).join('-');
                this.selectedVariant = this.variants[selectedValueIds] || { quantity: 0, price: this.productBasePrice }; // Fallback
            } else {
                this.selectedVariant = null;
            }

            this.currentPrice = this.selectedVariant ? this.selectedVariant.price : this.productBasePrice;
            this.quantity = (this.selectedVariant && this.selectedVariant.quantity > 0) ? 1 : 0;

            // If a variant is selected, reset quantity if it exceeds new stock
            if (this.selectedVariant && this.quantity > this.selectedVariant.quantity) {
                this.quantity = this.selectedVariant.quantity > 0 ? 1 : 0;
            }
        },

        isOptionAvailable(attributeId, valueId) {
            // This is a complex check: an option is available if selecting it *could* lead to an in-stock variant.
            // For simplicity here, we'll assume all defined options are potentially part of a valid variant.
            // A more robust check would iterate through variants.
            // For now, just check if the option is part of *any* variant.
            // This basic implementation doesn't grey out options based on other selections yet.
            // To do that, you'd need to:
            // 1. Temporarily select the option.
            // 2. Iterate through all variants that match ALL currently selected options (including the temporary one).
            // 3. If any such variant has stock > 0, the option is available.
            return true; // Simplified
        },

        addToCart() {
            if (!this.canAddToCart) {
                alert('This product configuration is not available.');
                return;
            }
            let cartData = {
                product_id: {{ $product->id }},
                product_name: this.productName,
                quantity: this.quantity,
                price: this.currentPrice,
            };
            if (this.selectedVariant) {
                cartData.variant_id = this.selectedVariant.id;
                cartData.variant_sku = this.selectedVariant.sku;
                cartData.attributes = {};
                for (const attrId in this.selectedOptions) {
                    const attrName = this.options[attrId].name;
                    const valueId = this.selectedOptions[attrId];
                    const valueObj = this.options[attrId].values.find(v => v.id === valueId);
                    cartData.attributes[attrName] = valueObj ? valueObj.name : 'N/A';
                }
            }
            console.log('Adding to cart:', cartData);
            // Implement actual AJAX call to add to cart
            alert(`${this.quantity} x ${this.productName} added to cart! (Variant ID: ${this.selectedVariant ? this.selectedVariant.id : 'N/A'}) Price: $${this.currentPrice.toFixed(2)}`);
            // Potentially dispatch an event for cart update
            // window.dispatchEvent(new CustomEvent('cart-updated', { detail: cartData }));
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
    /* Ensure prose styles for pink are defined in your app.css or here */
    .prose-pink a { color: #ec4899; } /* pink-500 */
    .prose-pink a:hover { color: #db2777; } /* pink-600 */
    /* Add more prose styles as needed */
</style>
@endpush