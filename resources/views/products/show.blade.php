{{-- products/show.blade.php --}}
<x-app-layout :title="$product->name">
    {{-- Breadcrumbs --}}
    <div class="bg-pink-50 border-b border-pink-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex space-x-2 items-center">
                    <li class="flex items-center"><a href="{{ route('home') }}" class="text-gray-500 hover:text-pink-600">Home</a><x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/></li>
                    <li class="flex items-center"><a href="{{ route('products.index') }}" class="text-gray-500 hover:text-pink-600">Shop</a><x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/></li>
                    @if($product->categories->isNotEmpty())
                        @php $category = $product->categories->first(); @endphp
                        <li class="flex items-center"><a href="{{ route('products.index', ['category' => $category->slug]) }}" class="text-gray-500 hover:text-pink-600">{{ $category->name }}</a><x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/></li>
                    @endif
                    <li class="flex items-center"><span class="text-pink-700 font-medium" aria-current="page">{{ Str::limit($product->name, 40) }}</span></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="max-w-7xl mx-auto">
            {{-- Main Product Display Card --}}
            <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md lg:grid lg:grid-cols-3 lg:gap-8 items-start">

                {{-- Left Column: Product Images (Keeping Alpine for image gallery for now, can be converted later if desired) --}}
                <div class="lg:col-span-1 mb-8 lg:mb-0">
                    @php
                        $imagesCollection = $product->images;
                        $mainImage = $imagesCollection->first();
                        $imageUrl = $mainImage?->image_url ?? asset('images/placeholder.png');
                        $altText = $mainImage?->alt ?? $product->name;
                    @endphp
                    <div class="space-y-4" x-data="{ currentImage: {{ Js::from($imageUrl) }}, currentImageAlt: {{ Js::from($altText) }} }">
                        <div class="relative bg-gray-100 rounded-lg overflow-hidden aspect-square border border-gray-200 group">
                            <img :src="currentImage" :alt="currentImageAlt" class="w-full h-full object-contain cursor-pointer" @click="$dispatch('open-modal', 'product-image-zoom-modal')">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <x-heroicon-o-magnifying-glass-plus class="w-12 h-12 text-white"/>
                            </div>
                        </div>
                        @if($imagesCollection->count() > 1)
                            <div class="flex flex-wrap gap-2">
                                @foreach($imagesCollection as $image)
                                    <button @click="currentImage = '{{ $image->image_url }}'; currentImageAlt = '{{ $image->alt ?? $product->name }}';"
                                            :class="{ 'ring-2 ring-pink-500 border-pink-300': currentImage === '{{ $image->image_url }}', 'border-gray-200 hover:border-pink-300': currentImage !== '{{ $image->image_url }}' }"
                                            class="w-16 h-16 bg-white rounded overflow-hidden focus:outline-none border transition-all">
                                        <img src="{{ $image->image_url }}" alt="{{ $image->alt ?? $product->name . ' thumbnail' }}" class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Middle Column: Product Details & Actions (Plain JS will target these IDs) --}}
                <div id="pdp-details-column" class="lg:col-span-1 space-y-5 mt-8 lg:mt-0">
                    {{-- Hidden script tags to pass data to JavaScript --}}
                    <script id="pdp-product-id" type="application/json">{{ json_encode($product->id) }}</script>
                    <script id="pdp-base-price" type="application/json">{{ json_encode((float)$product->price) }}</script>
                    <script id="pdp-base-quantity" type="application/json">{{ json_encode((int)$product->quantity) }}</script>
                    <script id="pdp-has-variants" type="application/json">{{ json_encode($hasVariantsForView) }}</script>
                    <script id="pdp-options-data" type="application/json">{!! $optionsDataForJs !!}</script> {{-- Use {!! !!} as $optionsDataForJs is already JS::from() --}}
                    <script id="pdp-variants-data" type="application/json">{!! $variantDataForJs !!}</script> {{-- Use {!! !!} as $variantDataForJs is already JS::from() --}}

                    <div class="flex items-start justify-between">
                        <h1 class="text-xl lg:text-2xl font-semibold text-gray-800 leading-tight pr-4">{{ $product->name }}</h1>
                        @auth
                            {{-- Wishlist button - if you convert this to plain JS, give it an ID --}}
                            <div x-data="wishlistButton({ productId: {{ $product->id }}, initialIsInWishlist: {{ Auth::user()->hasInWishlist($product) ? 'true' : 'false' }} })" class="flex-shrink-0">
                                <button @click="toggleWishlist" type="button" :disabled="isLoading" :title="isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'" class="p-1.5 text-gray-400 hover:text-pink-500 disabled:opacity-50">
                                    <template x-if="isLoading"><svg class="animate-spin h-5 w-5 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></template>
                                    <template x-if="!isLoading"><x-heroicon-o-heart class="w-5 h-5" ::class="{ 'text-pink-500 fill-current': isInWishlist }" /></template>
                                </button>
                            </div>
                        @endauth
                    </div>

                    @if($product->brand)
                        <p class="text-sm">Brand: <a href="{{ route('brands.show', $product->brand->slug) }}" class="text-pink-600 hover:underline font-medium">{{ $product->brand->name }}</a></p>
                    @endif

                    <div class="flex items-center gap-2">
                        <div class="flex">@for ($i = 1; $i <= 5; $i++) <x-heroicon-s-star class="w-4 h-4 {{ $i <= round($product->approved_reviews_avg_rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" /> @endfor</div>
                        @if ($product->approved_reviews_count > 0)
                            <a href="#reviews" class="text-sm text-pink-600 hover:underline">({{ $product->approved_reviews_count }} verified ratings)</a>
                        @else
                            <a href="#reviews" class="text-sm text-pink-600 hover:underline">Be the first to review</a>
                        @endif
                    </div>

                    <hr class="border-gray-100" />

                    <div class="space-y-1">
                        <div class="flex items-baseline gap-2">
                            <span id="pdp-current-price" class="text-2xl font-bold text-gray-900">GH₵ {{ number_format($product->price, 2) }}</span>
                            @if($product->compare_at_price && $product->compare_at_price > $product->price)
                                @php $discount = round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100); @endphp
                                <span class="text-lg text-gray-500 line-through">GH₵ {{ number_format($product->compare_at_price, 2) }}</span>
                                <span class="text-sm font-medium text-red-600">-{{ $discount }}%</span>
                            @endif
                        </div>
                        <div class="text-sm min-h-[20px]">
                            <p id="pdp-items-left-text" class="text-orange-600 font-medium" style="display: none;"></p>
                            <p id="pdp-stock-status" class="text-green-600 font-medium" style="display: none;">In stock</p> {{-- JS will manage this --}}
                        </div>
                    </div>

                    {{-- Variation Available Section (JS will populate this if needed) --}}
                    <div id="pdp-variant-options-container" class="space-y-3" style="display: none;"> {{-- Initially hidden --}}
                        <div class="flex items-center justify-between">
                          <h3 class="text-sm font-medium text-gray-900 uppercase">Variation Available</h3>
                          {{-- <a href="#" class="text-sm text-pink-600 hover:underline">Size Guide</a> --}}
                        </div>
                        <div id="pdp-options-render-area" class="space-y-4">
                            {{-- JavaScript will build <fieldset> and <label> here --}}
                        </div>
                    </div>

                    {{-- Quantity Input (JS will show/hide for simple products) --}}
                    <div id="pdp-quantity-section" class="mt-4" style="display: none;"> {{-- Initially hidden --}}
                        <label for="pdp-quantity-input" class="block text-sm font-medium text-gray-900 mb-1">Quantity</label>
                        <div class="relative flex items-center max-w-[8rem]">
                            <button id="pdp-quantity-minus" type="button" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-l-lg p-2.5 h-10 disabled:opacity-50">
                                <x-heroicon-s-minus class="w-4 h-4 text-gray-900"/>
                            </button>
                            <input type="number" id="pdp-quantity-input" value="1" min="1" class="bg-gray-50 border-x-0 border-gray-300 h-10 text-center text-gray-900 text-sm focus:ring-pink-500 focus:border-pink-500 block w-full py-2.5" required>
                            <button id="pdp-quantity-plus" type="button" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-r-lg p-2.5 h-10 disabled:opacity-50">
                                <x-heroicon-s-plus class="w-4 h-4 text-gray-900"/>
                            </button>
                        </div>
                    </div>

                    {{-- Action Area: Cart Messages & Button --}}
                    <div class="mt-auto pt-4 space-y-4">
                        <div id="pdp-cart-action-message" class="border px-3 py-2 rounded-md text-sm" style="display: none;">
                            {{-- JS will set text and classes --}}
                        </div>

                        <button type="button" id="pdp-add-to-cart-button"
                                class="w-full flex items-center justify-center rounded-md border border-transparent bg-pink-600 px-8 py-3 text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors">
                            <span id="pdp-add-to-cart-button-icon-loading" style="display: none;">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </span>
                            <span id="pdp-add-to-cart-button-icon-default">
                                <x-heroicon-o-shopping-cart class="w-6 h-6 mr-3" />
                            </span>
                            <span id="pdp-add-to-cart-button-text">Add to Cart</span>
                        </button>
                    </div>

                    @if($product->short_description)
                        <div class="mt-6 text-sm text-gray-600 space-y-2 prose prose-sm max-w-none prose-pink">
                            {!! nl2br(e($product->short_description)) !!}
                        </div>
                    @endif
                </div>                   

                {{-- Right Column: Delivery & Returns --}}
                <div class="lg:col-span-1 mt-8 lg:mt-0">
                    <div class="bg-pink-50 rounded-lg p-4 space-y-6 sticky top-24 border border-pink-100">
                        <h2 class="text-lg font-medium text-gray-900 uppercase">Delivery & Returns</h2>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium">JUMIA</span>
                                <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded border">GLOBAL</span>
                            </div>
                            <button class="text-sm text-blue-600 underline">Shipped from abroad Details</button>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-sm font-medium text-gray-900">Choose your location</h3>

                            <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                                <option value="greater-accra">Greater Accra</option>
                                <option value="ashanti">Ashanti</option>
                                <option value="western">Western</option>
                            </select>

                            <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-pink-500 focus:border-pink-500">
                                <option value="abeka">Abeka</option>
                                <option value="tema">Tema</option>
                                <option value="kumasi">Kumasi</option>
                            </select>
                        </div>

                        <hr class="border-pink-200">

                        {{-- Pickup Station --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-building-storefront class="w-5 h-5 text-gray-600" />
                                    <span class="text-sm font-medium">Pickup Station</span>
                                </div>
                                <button class="text-sm text-blue-600 underline">Details</button>
                            </div>
                            <p class="text-sm text-gray-600">Delivery Fees GH₵ 14.43</p>
                            <p class="text-xs text-gray-500">
                                Ready for pickup between <span class="font-medium">25 June</span> and 
                                <span class="font-medium">04 July</span> if you place your order within the next 
                                <span class="font-medium text-red-600">15hrs 53mins</span>
                            </p>
                        </div>

                        <hr class="border-pink-200">

                        {{-- Door Delivery --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-truck class="w-5 h-5 text-gray-600" />
                                    <span class="text-sm font-medium">Door Delivery</span>
                                </div>
                                <button class="text-sm text-blue-600 underline">Details</button>
                            </div>
                            <p class="text-sm text-gray-600">Delivery Fees GH₵ 28.43</p>
                            <p class="text-xs text-gray-500">
                                Ready for delivery between <span class="font-medium">25 June</span> and 
                                <span class="font-medium">04 July</span> if you place your order within the next 
                                <span class="font-medium text-red-600">15hrs 53mins</span>
                            </p>
                        </div>

                        <hr class="border-pink-200">

                        {{-- Return Policy --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-arrow-path class="w-5 h-5 text-gray-600" />
                                <span class="text-sm font-medium">Return Policy</span>
                            </div>
                            <p class="text-xs text-gray-500">
                                Free return within 15 days for all eligible items. 
                                <button class="text-blue-600 underline">Details</button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs Section --}}
            <div class="mt-10 md:mt-16" x-data="{ activeTab: 'description' }">
                <div class="border-b border-pink-200">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="activeTab = 'description'" 
                                :class="{ 'border-pink-500 text-pink-600': activeTab === 'description', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'description' }" 
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Description
                        </button>
                        @if($product->specifications && count($product->specifications) > 0)
                            <button @click="activeTab = 'specifications'" 
                                    :class="{ 'border-pink-500 text-pink-600': activeTab === 'specifications', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'specifications' }" 
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Specifications
                            </button>
                        @endif
                        <button @click="activeTab = 'reviews'" id="reviews"
                                :class="{ 'border-pink-500 text-pink-600': activeTab === 'reviews', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'reviews' }" 
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Reviews ({{ $product->approved_reviews_count }})
                        </button>
                    </nav>
                </div>

                <div class="mt-8">
                    {{-- Description Tab --}}
                    <div x-show="activeTab === 'description'" x-cloak class="bg-white p-4 sm:p-6 rounded-lg shadow-md border border-pink-100">
                        <div class="prose prose-pink max-w-none text-gray-600">
                            {!! $product->description ?: '<p>No full description available for this product.</p>' !!}
                        </div>
                    </div>

                    {{-- Specifications Tab --}}
                    @if($product->specifications && count($product->specifications) > 0)
                        <div x-show="activeTab === 'specifications'" x-cloak class="bg-white p-4 sm:p-6 rounded-lg shadow-md border border-pink-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Product Specifications</h3>
                            <dl class="divide-y divide-gray-200">
                                @foreach($product->specifications as $spec)
                                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-700">{{ $spec['key'] }}</dt>
                                    <dd class="mt-1 text-sm text-gray-600 sm:mt-0 sm:col-span-2">{{ $spec['value'] }}</dd>
                                </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    {{-- Reviews Tab --}}
                    <div x-show="activeTab === 'reviews'" x-cloak class="bg-white p-4 sm:p-6 rounded-lg shadow-md border border-pink-100">
                        @include('products.partials._reviews_section', ['reviews' => $product->approvedReviews, 'product' => $product])
                    </div>
                </div>
            </div>

            {{-- Related Products Section --}}
            @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
            <section class="mt-16 lg:mt-24">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 text-center mb-8 sm:mb-10">
                    You May Also Like
                </h2>
                <div class="grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 xl:gap-x-8">
                    @foreach($relatedProducts as $relatedProductItem)
                        <div>
                            <x-product-card-small :product="$relatedProductItem" :userWishlistProductIds="$userWishlistProductIds ?? []" />
                        </div>
                    @endforeach
                </div>
            </section>
            @endif
        </div>
    </div>

    {{-- Image Zoom Modal --}}
    <x-modal name="product-image-zoom-modal" maxWidth="4xl">
        <div class="p-2 sm:p-4 bg-white rounded-lg shadow-xl relative">
            <img x-data="{ zoomedImageUrl: '' }"
                 @open-modal.window="if ($event.detail.name === 'product-image-zoom-modal') {
                    const mainImgElement = document.querySelector('[x-ref=\'mainImageForZoom\']');
                    if (mainImgElement) zoomedImageUrl = mainImgElement.src;
                 }"
                 :src="zoomedImageUrl"
                 alt="Zoomed product image"
                 class="max-w-full max-h-[85vh] object-contain mx-auto">
            <button @click="$dispatch('close')" class="absolute top-2 right-2 sm:top-3 sm:right-3 text-gray-600 hover:text-pink-700 p-1 bg-white/70 rounded-full shadow hover:bg-white transition">
                <x-heroicon-o-x-mark class="w-6 h-6 sm:w-7 sm:h-7"/>
            </button>
        </div>
    </x-modal>

</x-app-layout>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailsContainer = document.getElementById('pdp-details-column');
    if (!detailsContainer) return; // Exit if not on a PDP

    // --- Retrieve Data from Blade ---
    const productId = JSON.parse(document.getElementById('pdp-product-id').textContent);
    let currentPrice = JSON.parse(document.getElementById('pdp-base-price').textContent);
    let productBaseQuantity = JSON.parse(document.getElementById('pdp-base-quantity').textContent);
    const hasVariants = JSON.parse(document.getElementById('pdp-has-variants').textContent);
    const optionsData = JSON.parse(document.getElementById('pdp-options-data').textContent || '{}');
    const allVariantsData = JSON.parse(document.getElementById('pdp-variants-data').textContent || '{}');

    // --- DOM Elements ---
    const currentPriceElem = document.getElementById('pdp-current-price');
    const itemsLeftTextElem = document.getElementById('pdp-items-left-text');
    const stockStatusElem = document.getElementById('pdp-stock-status');
    const variantOptionsContainer = document.getElementById('pdp-variant-options-container');
    const optionsRenderArea = document.getElementById('pdp-options-render-area');
    const quantitySection = document.getElementById('pdp-quantity-section');
    const quantityInput = document.getElementById('pdp-quantity-input');
    const quantityMinusBtn = document.getElementById('pdp-quantity-minus');
    const quantityPlusBtn = document.getElementById('pdp-quantity-plus');
    const addToCartButton = document.getElementById('pdp-add-to-cart-button');
    const cartButtonTextElem = document.getElementById('pdp-add-to-cart-button-text');
    const cartIconDefault = document.getElementById('pdp-add-to-cart-button-icon-default');
    const cartIconLoading = document.getElementById('pdp-add-to-cart-button-icon-loading');
    const cartActionMessageElem = document.getElementById('pdp-cart-action-message');

    // --- State ---
    let selectedOptions = {}; // { attributeId: valueId }
    let currentSelectedVariant = null;
    let currentQuantity = 1; // For simple product quantity input
    let isLoading = false;
    const lowStockThreshold = 10;

    // --- Helper Functions ---
    function isSimpleProduct() {
        return !hasVariants || Object.keys(optionsData).length === 0;
    }

    function areAllOptionsSelected() {
        if (isSimpleProduct()) return false; // No options to select for simple
        return Object.keys(optionsData).every(attrId => selectedOptions.hasOwnProperty(attrId) && selectedOptions[attrId] !== null);
    }

    function isAnythingPurchasable() {
        if (isSimpleProduct()) {
            return productBaseQuantity > 0;
        }
        return Object.values(allVariantsData).some(variant => variant.quantity > 0);
    }

    function getVariantFromSelection() {
        if (!areAllOptionsSelected()) return null;
        const key = Object.values(selectedOptions).map(id => parseInt(id)).sort((a, b) => a - b).join('-');
        return allVariantsData[key] || null;
    }

    function isOptionCombinationAvailable(attributeIdToCheck, valueIdToCheck) {
        // Create a temporary selection including the one to check
        const tempSelection = { ...selectedOptions };
        tempSelection[attributeIdToCheck] = valueIdToCheck;

        for (const variantKey in allVariantsData) {
            const variant = allVariantsData[variantKey];
            if (variant.quantity > 0) {
                let match = true;
                // Check if this variant matches ALL currently considered options (tempSelection)
                // For an option to be "available", it must lead to at least one stocked variant
                // if other options were also selected.
                const variantAttributeValueIds = variant.attributeValues; // This should be array of value IDs

                for (const attrId in optionsData) { // Iterate over all possible attribute types
                    const selectedValueId = tempSelection[attrId];
                    if (selectedValueId !== null && typeof selectedValueId !== 'undefined') { // If this attribute type is part of our temp selection
                        if (!variantAttributeValueIds.includes(selectedValueId)) {
                            match = false;
                            break;
                        }
                    }
                }
                if (match) return true; // Found a stocked variant that could be formed with this option
            }
        }
        return false;
    }


    // --- UI Update Functions ---
    function updatePriceAndStockDisplay() {
        let displayPrice = basePrice;
        let stockText = '';
        let lowStock = false;
        let inStockGeneral = false;

        if (isSimpleProduct()) {
            displayPrice = basePrice;
            if (productBaseQuantity > 0 && productBaseQuantity <= lowStockThreshold) {
                stockText = `${productBaseQuantity} items left`;
                lowStock = true;
            }
            inStockGeneral = productBaseQuantity > 0;
        } else { // Has Variants
            if (areAllOptionsSelected()) {
                currentSelectedVariant = getVariantFromSelection();
                if (currentSelectedVariant) {
                    displayPrice = currentSelectedVariant.price;
                    if (currentSelectedVariant.quantity > 0 && currentSelectedVariant.quantity <= lowStockThreshold) {
                        stockText = `${currentSelectedVariant.quantity} items left`;
                        lowStock = true;
                    }
                    inStockGeneral = currentSelectedVariant.quantity > 0;
                } else {
                    stockText = 'This combination is unavailable.';
                }
            } else {
                displayPrice = basePrice; // Show base price if not all options selected
                // No specific stock text until all options are selected
                inStockGeneral = isAnythingPurchasable(); // General availability for variants
            }
        }

        if (currentPriceElem) currentPriceElem.textContent = `GH₵ ${parseFloat(displayPrice).toFixed(2)}`;
        if (itemsLeftTextElem) {
            itemsLeftTextElem.textContent = stockText;
            itemsLeftTextElem.style.display = stockText ? 'block' : 'none';
            itemsLeftTextElem.classList.toggle('text-orange-600', lowStock);
        }

        if (stockStatusElem) {
            stockStatusElem.style.display = 'none'; // Hide by default
            if (!stockText && inStockGeneral) {
                stockStatusElem.textContent = 'In stock';
                stockStatusElem.className = 'text-sm text-green-600 font-medium';
                stockStatusElem.style.display = 'block';
            } else if (!inStockGeneral && !stockText) { // If not purchasable and no specific message
                 stockStatusElem.textContent = 'Out of stock';
                 stockStatusElem.className = 'text-sm text-red-600 font-medium';
                 stockStatusElem.style.display = 'block';
            }
        }
        updateAddToCartButton();
    }

    function updateAddToCartButton() {
        if (!addToCartButton || !cartButtonTextElem || !cartIconDefault || !cartIconLoading) return;

        const purchasable = isAnythingPurchasable();
        addToCartButton.disabled = isLoading || !purchasable;

        if (isLoading) {
            cartButtonTextElem.textContent = 'Adding...';
            cartIconDefault.style.display = 'none';
            cartIconLoading.style.display = 'inline-block';
        } else {
            cartButtonTextElem.textContent = purchasable ? 'Add to Cart' : 'Out of Stock';
            cartIconDefault.style.display = 'inline-block';
            cartIconLoading.style.display = 'none';
        }
    }

    function showActionMessage(message, type = 'error') {
        if (cartActionMessageElem) {
            cartActionMessageElem.textContent = message;
            cartActionMessageElem.className = `border px-3 py-2 rounded-md text-sm ${type === 'success' ? 'bg-green-100 border-green-300 text-green-700' : 'bg-red-100 border-red-300 text-red-800'}`;
            cartActionMessageElem.style.display = 'block';
            setTimeout(() => { cartActionMessageElem.style.display = 'none'; }, 4000);
        }
    }

    // --- Render Variant Options ---
    function renderVariantOptions() {
        if (isSimpleProduct() || !optionsRenderArea) {
            if(variantOptionsContainer) variantOptionsContainer.style.display = 'none';
            if(quantitySection) quantitySection.style.display = isSimpleProduct() ? 'block' : 'none';
            return;
        }
        if(variantOptionsContainer) variantOptionsContainer.style.display = 'block';
        if(quantitySection) quantitySection.style.display = 'none'; // Hide simple qty input for variants

        optionsRenderArea.innerHTML = ''; // Clear previous options

        Object.keys(optionsData).forEach(attributeId => {
            const attribute = optionsData[attributeId];
            const fieldset = document.createElement('fieldset');
            const legend = document.createElement('legend');
            legend.className = 'text-sm font-medium text-gray-700 mb-2';
            legend.textContent = attribute.name;
            fieldset.appendChild(legend);

            const valuesContainer = document.createElement('div');
            valuesContainer.className = 'flex flex-wrap gap-2';

            attribute.values.forEach(value => {
                const label = document.createElement('label');
                label.className = 'border border-gray-200 bg-white text-gray-900 hover:bg-gray-50 rounded-md py-2 px-4 text-sm font-medium uppercase cursor-pointer focus:outline-none transition-all';
                label.dataset.attributeId = attributeId;
                label.dataset.valueId = value.id;

                const input = document.createElement('input');
                input.type = 'radio';
                input.name = `option_${attributeId}`;
                input.value = value.id;
                input.className = 'sr-only';

                const span = document.createElement('span');
                span.textContent = value.name;

                label.appendChild(input);
                label.appendChild(span);
                valuesContainer.appendChild(label);

                // Check initial availability for styling
                if (!isOptionCombinationAvailable(parseInt(attributeId), parseInt(value.id))) {
                    label.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                    input.disabled = true;
                }


                label.addEventListener('click', function() {
                    if (input.disabled) return;

                    // Update selected state visually
                    this.closest('.pdp-variant-attribute-group', group => {
                        group.querySelectorAll('.pdp-variant-option-label').forEach(l => {
                             l.classList.remove('ring-2', 'ring-pink-500', 'border-pink-500', 'bg-pink-50', 'text-pink-700', 'font-semibold');
                             l.classList.add('border-gray-200', 'bg-white', 'text-gray-900', 'hover:bg-gray-50');
                        });
                    });
                    
                    this.classList.remove('border-gray-200', 'bg-white', 'text-gray-900', 'hover:bg-gray-50');
                    this.classList.add('ring-2', 'ring-pink-500', 'border-pink-500', 'bg-pink-50', 'text-pink-700', 'font-semibold');


                    selectedOptions[attributeId] = parseInt(value.id);
                    // After selection, re-evaluate availability of other options
                    // (More complex: would involve checking combinations)
                    updatePriceAndStockDisplay();
                });
            });
            fieldset.appendChild(valuesContainer);
            optionsRenderArea.appendChild(fieldset);
            // Add data-attribute-id to the valuesContainer for easier targeting if needed
            valuesContainer.dataset.attributeId = attributeId; 
            valuesContainer.classList.add('pdp-variant-attribute-group');
        });
    }


    // --- Event Handlers Setup ---
    if (quantityMinusBtn && quantityInput) {
        quantityMinusBtn.addEventListener('click', () => {
            let val = parseInt(quantityInput.value);
            if (val > 1) { quantityInput.value = val - 1; currentQuantity = val - 1;}
            quantityMinusBtn.disabled = currentQuantity <= 1;
            quantityPlusBtn.disabled = currentQuantity >= parseInt(quantityInput.max);
        });
    }
    if (quantityPlusBtn && quantityInput) {
        quantityPlusBtn.addEventListener('click', () => {
            let val = parseInt(quantityInput.value);
            const max = parseInt(quantityInput.max);
            if (val < max) { quantityInput.value = val + 1; currentQuantity = val + 1; }
            quantityMinusBtn.disabled = currentQuantity <= 1;
            quantityPlusBtn.disabled = currentQuantity >= max;
        });
    }
    if (quantityInput) {
        quantityInput.addEventListener('input', (e) => { // Use 'input' for better responsiveness
            let val = parseInt(e.target.value);
            const min = parseInt(e.target.min);
            const max = parseInt(e.target.max);
            if (isNaN(val) || val < min) val = min;
            if (val > max) val = max;
            e.target.value = val; // Correct the input value if out of bounds
            currentQuantity = val;
            if(quantityMinusBtn) quantityMinusBtn.disabled = currentQuantity <= min;
            if(quantityPlusBtn) quantityPlusBtn.disabled = currentQuantity >= max;
        });
    }

    if (addToCartButton) {
        addToCartButton.addEventListener('click', function () {
            if (isLoading) return;
            if (cartActionMessageElem) cartActionMessageElem.style.display = 'none';

            if (isSimpleProduct()) {
                if (productBaseQuantity > 0 && currentQuantity <= productBaseQuantity) {
                    performAddToCart(productId, null, currentQuantity);
                } else {
                    showActionMessage(productBaseQuantity > 0 ? `Only ${productBaseQuantity} units available.` : 'This item is out of stock.', 'error');
                }
                return;
            }

            if (areAllOptionsSelected()) {
                currentSelectedVariant = getVariantFromSelection(); // Ensure this is updated
                if (currentSelectedVariant && currentSelectedVariant.quantity > 0) {
                    performAddToCart(productId, currentSelectedVariant.id, 1); // Add 1 unit of selected variant for now
                } else {
                    showActionMessage('This specific combination is not available or out of stock.', 'error');
                }
            } else {
                showActionMessage('Please select a variation to add to cart.', 'error');
            }
        });
    }

    function performAddToCart(prodId, varId, qty) {
        isLoading = true;
        updateAddToCartButton(); // Show loading state
        // ... (your existing performAddToCart fetch logic from previous correct answer) ...
        // Make sure it dispatches 'cart-updated' and 'toast-show'
        let payload = { product_id: prodId, quantity: qty };
        if (varId) payload.variant_id = varId;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route('cart.add') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.ok ? response.json() : response.json().then(err => {err.status = response.status; throw err;}))
        .then(data => {
            if (data.success) {
                showActionMessage(data.message || 'Item added to cart!', 'success');
                if (typeof data.cart_distinct_items_count !== 'undefined') {
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: data.cart_distinct_items_count }}));
                }
                window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'success', message: data.message }}));
            } else {
                showActionMessage(data.message || 'Could not add item.', 'error');
            }
        })
        .catch(error => {
            console.error('Add to cart error:', error);
            showActionMessage(error.message || 'An error occurred.', 'error');
        })
        .finally(() => {
            isLoading = false;
            updateAddToCartButton(); // Revert button to normal state
        });
    }

    // --- Initial Setup ---
    if (isSimpleProduct()) {
        if(quantitySection) quantitySection.style.display = 'block';
        if(quantityInput) quantityInput.max = productBaseQuantity;
        if(quantityMinusBtn) quantityMinusBtn.disabled = currentQuantity <= 1;
        if(quantityPlusBtn) quantityPlusBtn.disabled = currentQuantity >= productBaseQuantity;
    } else {
        renderVariantOptions(); // Build the variant selection UI
    }
    updatePriceAndStockDisplay(); // Set initial price, stock text, and button state

});
</script>
@endpush