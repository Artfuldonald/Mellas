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
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-8 xl:gap-x-12 items-start max-w-7xl mx-auto">

            {{-- Left Column: Contains the main product card which itself contains the Alpine component --}}
            <div class="lg:col-span-8 xl:col-span-9 mb-8 lg:mb-0">
                {{-- THIS IS THE MAIN ALPINE COMPONENT FOR PRODUCT DETAILS, VARIANTS, AND ADD TO CART --}}
                <div class="md:flex md:space-x-6 lg:space-x-8 bg-white p-4 sm:p-6 rounded-xl shadow-xl"
                     x-data="productVariantSelector({
                        productId: {{ $product->id }},
                        productName: {{ Js::from($product->name) }},
                        productBasePrice: {{ (float)$product->price }},
                        productBaseQuantity: {{ $product->variants->isEmpty() && !$product->attributes->count() ? ($product->quantity ?? 0) : -1 }}, // -1 signifies variants mode or attributes present
                        allVariantsData: {{ $variantDataForJs }}, // From ProductController@show
                        options: {{ $optionsDataForJs }}        // From ProductController@show
                     })">

                    {{-- Image Gallery Section (can be its own nested Alpine component if complex) --}}
                    <div class="md:w-1/2 lg:w-5/12 flex-shrink-0 mb-6 md:mb-0 md:sticky md:top-24 self-start"
                         x-data="{
                            images: {{ Js::from($product->images->map(fn($img) => ['id' => $img->id, 'url' => $img->image_url ?? asset('images/placeholder.png'), 'alt' => $img->alt ?? $product->name])) }},
                            currentImage: {{ Js::from($product->images->first()->image_url ?? asset('images/placeholder.png')) }},
                            currentImageAlt: {{ Js::from($product->images->first()?->alt ?? $product->name) }},
                            changeImage(image) {
                                this.currentImage = image.url;
                                this.currentImageAlt = image.alt;
                            }
                         }">
                         <div class="aspect-w-1 aspect-h-1 w-full bg-pink-50 rounded-xl shadow-lg overflow-hidden border border-pink-100 relative group">
                            <img :src="currentImage" :alt="currentImageAlt" class="w-full h-full object-contain object-center cursor-pointer"
                                 x-ref="mainImageForZoom"
                                 @click="$dispatch('open-modal', { name: 'product-image-zoom-modal', imageUrl: currentImage })">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <x-heroicon-o-magnifying-glass-plus class="w-12 h-12 text-white"/>
                            </div>
                        </div>
                        @if($product->images->count() > 1)
                            <div class="mt-3 grid grid-cols-4 sm:grid-cols-5 gap-2 sm:gap-3">
                                <template x-for="image in images" :key="image.id">
                                    <button @click="changeImage(image)"
                                            :class="{ 'ring-2 ring-pink-500 ring-offset-1': currentImage === image.url }"
                                            class="aspect-w-1 aspect-h-1 bg-white rounded-md sm:rounded-lg overflow-hidden focus:outline-none border hover:border-pink-300 transition-all">
                                        <img :src="image.url" :alt="image.alt + ' thumbnail'" class="w-full h-full object-cover object-center">
                                    </button>
                                </template>
                            </div>
                        @endif
                    </div>

                    {{-- Product Information Section (PART OF productVariantSelector SCOPE) --}}
                    <div class="md:w-1/2 lg:w-7/12 flex-grow flex flex-col">
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">{{ $product->name }}</h1>

                        @if($product->brand)
                            <p class="text-sm text-gray-500 mt-1">Brand: <a href="{{ $product->brand->logo_url ? route('brands.show', $product->brand->slug) : '#' }}" class="text-pink-600 hover:underline">{{ $product->brand->name }}</a></p>
                        @endif

                        {{-- Reviews Summary --}}
                        <div class="mt-3">
                            @if ($product->approved_reviews_count > 0)
                                <div class="flex items-center">
                                    <div class="flex items-center">@for ($i = 1; $i <= 5; $i++) <x-heroicon-s-star class="h-5 w-5 {{ $i <= round($product->average_rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" /> @endfor</div>
                                    <a href="#reviews" @click.prevent="document.getElementById('reviews')?.scrollIntoView({behavior:'smooth'}); $dispatch('set-active-tab', 'reviews');" class="ml-2 text-sm font-medium text-pink-600 hover:text-pink-500">({{ $product->approved_reviews_count }} {{ Str::plural('review', $product->approved_reviews_count) }})</a>
                                </div>
                            @else
                                <div class="flex items-center">
                                    <div class="flex items-center">@for ($i = 1; $i <= 5; $i++) <x-heroicon-o-star class="h-5 w-5 text-gray-300" /> @endfor</div>
                                    <a href="#reviews" @click.prevent="document.getElementById('reviews')?.scrollIntoView({behavior:'smooth'}); $dispatch('set-active-tab', 'reviews');" class="ml-2 text-sm font-medium text-pink-600 hover:text-pink-500">Be the first to review</a>
                                </div>
                            @endif
                        </div>

                        {{-- Price Display (controlled by Alpine) --}}
                        <div class="mt-4">
                            <p class="text-3xl sm:text-4xl font-extrabold text-pink-600" x-text="`GH₵ ${currentPrice.toFixed(2)}`"></p>
                            {{-- Compare at price logic here if needed, can also be dynamic --}}
                        </div>

                        {{-- "Items Left" Text (controlled by Alpine) --}}
                        <div class="mt-2 text-sm min-h-[20px]">
                             <p x-show="itemsLeftText" x-text="itemsLeftText" class="text-orange-600 font-medium"></p>
                        </div>

                        {{-- Variant Options UI (controlled by Alpine) --}}
                        <div class="mt-6 space-y-5" x-show="!isSimpleProduct()">
                            <template x-for="(attributeData, attributeId) in options" :key="attributeId">
                                <fieldset>
                                    <legend class="text-sm font-medium text-gray-900 mb-1" x-text="attributeData.name"></legend>
                                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                        <template x-for="value in attributeData.values" :key="value.id">
                                            <label @click="selectOption(attributeId, value.id)"
                                                   :class="{
                                                        'ring-2 ring-pink-500 border-pink-500 bg-pink-50 text-pink-700 font-semibold': isSelected(attributeId, value.id),
                                                        'border-gray-300 text-gray-700 hover:bg-gray-50': !isSelected(attributeId, value.id)
                                                   }"
                                                   class="border rounded-md py-2.5 px-3 flex items-center justify-center text-xs sm:text-sm uppercase cursor-pointer focus:outline-none transition-all">
                                                <input type="radio" :name="`option_${attributeId}`" :value="value.id" x-model="selectedOptions[attributeId]" class="sr-only">
                                                <span x-text="value.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                </fieldset>
                            </template>
                        </div>

                        {{-- Quantity Input --}}
                        <div class="mt-8" x-show="isAnythingPurchasable() && (isSimpleProduct() || (currentSelectedVariantOnPage && currentSelectedVariantOnPage.quantity > 0) )">
                            <label for="pdp-quantity" class="block text-sm font-medium text-gray-900 mb-1">Quantity</label>
                            <div class="relative flex items-center max-w-[8rem]">
                                <button type="button" @click="quantity > 1 ? quantity-- : null" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-l-lg p-2.5 h-10 focus:ring-gray-100 focus:ring-2 focus:outline-none disabled:opacity-50" :disabled="quantity <= 1">
                                    <x-heroicon-s-minus class="w-4 h-4 text-gray-900"/>
                                </button>
                                <input type="number" id="pdp-quantity" name="quantity" x-model.number="quantity" min="1"
                                       :max="currentSelectedVariantOnPage ? currentSelectedVariantOnPage.quantity : productBaseQuantity"
                                       class="bg-gray-50 border-x-0 border-gray-300 h-10 text-center text-gray-900 text-sm focus:ring-pink-500 focus:border-pink-500 block w-full py-2.5" required>
                                <button type="button" @click="(currentSelectedVariantOnPage && quantity < currentSelectedVariantOnPage.quantity) || (isSimpleProduct() && quantity < productBaseQuantity) ? quantity++ : null"
                                        class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-r-lg p-2.5 h-10 focus:ring-gray-100 focus:ring-2 focus:outline-none disabled:opacity-50"
                                        :disabled="(currentSelectedVariantOnPage && quantity >= currentSelectedVariantOnPage.quantity) || (isSimpleProduct() && quantity >= productBaseQuantity)">
                                    <x-heroicon-s-plus class="w-4 h-4 text-gray-900"/>
                                </button>
                            </div>
                        </div>


                        {{-- Add to Cart Button & Messages (controlled by Alpine) --}}
                        <div x-show="cartActionMessage"
                             :class="{ 'bg-green-100 ...': cartActionMessageType === 'success', 'bg-red-100 ...': cartActionMessageType === 'error' }"
                             class="border px-4 py-3 rounded relative my-4 text-sm" role="alert" x-transition>
                            <span x-text="cartActionMessage"></span>
                        </div>
                        <div class="mt-8 flex-grow flex flex-col justify-end">
                            <button type="button" @click="handleAddToCartAttempt()"
                                    :disabled="addToCartButtonText === 'Adding...' || !isAnythingPurchasable()"
                                    class="w-full bg-pink-600 ...">
                                <template x-if="addToCartButtonText === 'Adding...'"><svg class="animate-spin ..."></svg></template>
                                <span x-text="addToCartButtonText"></span>
                            </button>
                        </div>

                        @if($product->short_description)
                            <div class="mt-6 text-sm text-gray-600 space-y-2 prose prose-sm max-w-none prose-pink">
                                {!! nl2br(e($product->short_description)) !!}
                            </div>
                        @endif

                        {{-- Wishlist Button (as designed before) --}}
                        @auth
                            <div class="mt-6 flex justify-start"
                                 x-data="wishlistButton({ productId: {{ $product->id }}, initialIsInWishlist: {{ Auth::user()->hasInWishlist($product) ? 'true' : 'false' }} })">
                                <button @click="toggleWishlist" type="button" :disabled="isLoading" class="text-sm font-medium text-pink-600 hover:text-pink-500 flex items-center disabled:opacity-50">
                                    <template x-if="isLoading"><svg class="animate-spin h-5 w-5 mr-1.5"></svg></template>
                                    <template x-if="!isLoading && isInWishlist"><x-heroicon-s-heart class="w-5 h-5 mr-1.5 text-pink-500"/></template>
                                    <template x-if="!isLoading && !isInWishlist"><x-heroicon-o-heart class="w-5 h-5 mr-1.5"/></template>
                                    <span x-text="isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'"></span>
                                </button>
                            </div>
                        @else
                            <div class="mt-6 flex justify-start">
                                <a href="{{ route('login') }}?redirect={{ url()->current() }}" class="text-sm font-medium text-pink-600 hover:text-pink-500 flex items-center">
                                    <x-heroicon-o-heart class="w-5 h-5 mr-1.5"/> Add to Wishlist
                                </a>
                            </div>
                        @endauth

                    </div> {{-- End Product Info Section --}}
                </div> {{-- End Main Product Card (md:flex) --}}

                {{-- Tabs Section (Full Description, Specifications, Reviews) --}}
                <div class="mt-10 md:mt-16" x-data="{ activeTab: 'description' }">
                    <div class="border-b border-pink-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'description'" :class="{ 'border-pink-500 text-pink-600': activeTab === 'description', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'description' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Description</button>
                            @if($product->specifications && count($product->specifications) > 0)
                                <button @click="activeTab = 'specifications'" :class="{ 'border-pink-500 text-pink-600': activeTab === 'specifications', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'specifications' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Specifications</button>
                            @endif
                            <button @click="activeTab = 'reviews'" id="reviews" :class="{ 'border-pink-500 text-pink-600': activeTab === 'reviews', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'reviews' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Reviews ({{ $product->approved_reviews_count }})</button>
                        </nav>
                    </div>
                    <div class="mt-8">
                        <div x-show="activeTab === 'description'" class="prose prose-pink max-w-none text-gray-600">{!! $product->description ?: '<p>No full description available for this product.</p>' !!}</div>
                        @if($product->specifications && count($product->specifications) > 0)
                            <div x-show="activeTab === 'specifications'" x-cloak class="bg-white p-6 rounded-lg shadow">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Product Specifications</h3>
                                <dl class="divide-y divide-gray-200">
                                    @foreach($product->specifications as $spec) {{-- Assuming specs are array of {key, value} --}}
                                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-700">{{ $spec['key'] }}</dt>
                                        <dd class="mt-1 text-sm text-gray-600 sm:mt-0 sm:col-span-2">{{ $spec['value'] }}</dd>
                                    </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endif
                        <div x-show="activeTab === 'reviews'" x-cloak>
                            @include('products.partials._reviews_section', ['reviews' => $product->approvedReviews, 'product' => $product])
                        </div>
                    </div>
                </div>
            </div> {{-- End Left Column --}}


            {{-- Right Column: Delivery, Seller Info etc. --}}
            <aside class="lg:col-span-4 xl:col-span-3 mt-8 lg:mt-0">
                <div class="space-y-6 sticky top-24">
                    <div class="bg-white p-4 sm:p-6 shadow rounded-lg">
                        <h3 class="text-sm font-semibold uppercase text-gray-700 mb-3 border-b pb-2">Delivery & Returns</h3>
                        <p class="text-xs text-gray-600">Details about delivery times, shipping costs, and return policies will go here.</p>
                        {{-- Placeholder for Jumia-style location selectors and delivery estimates --}}
                    </div>
                    @if($product->brand) {{-- Example: Show seller info if brand is considered the seller or link to brand page --}}
                    <div class="bg-white p-4 sm:p-6 shadow rounded-lg">
                        <h3 class="text-sm font-semibold uppercase text-gray-700 mb-3 border-b pb-2">Seller Information</h3>
                        <a href="{{ route('brands.show', $product->brand->slug) }}" class="font-medium text-pink-600 hover:underline">{{ $product->brand->name }}</a>
                        {{-- More seller details could go here --}}
                    </div>
                    @endif
                </div>
            </aside>

        </div> {{-- End Main Grid --}}


        {{-- "You May Also Like" Section --}}
        @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
        <section aria-labelledby="related-products-heading" class="mt-16 lg:mt-24 max-w-7xl mx-auto">
            <h2 id="related-products-heading" class="text-2xl font-bold tracking-tight text-gray-900 text-center mb-8 sm:mb-10">
                You May Also Like
            </h2>
            <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-x-6 xl:gap-x-8">
                @foreach($relatedProducts as $relatedProductItem)
                    <x-product-card :product="$relatedProductItem" :userWishlistProductIds="$userWishlistProductIds ?? []" />
                @endforeach
            </div>
        </section>
        @endif

    </div> {{-- End Page Container --}}


    {{-- MODALS --}}
    {{-- Product Image Zoom Modal --}}
    <x-modal name="product-image-zoom-modal" maxWidth="4xl" :show="$errors->any() ? true : false" focusable> {{-- :show is problematic here for dynamic opening --}}
    <div class="p-2 sm:p-4 bg-white rounded-lg shadow-xl relative">
        {{-- Changed x-data and src binding --}}
        <img x-data="{ zoomedImageUrl: '' }"
             @open-modal.window="if ($event.detail === 'product-image-zoom-modal' || (typeof $event.detail === 'object' && $event.detail.name === 'product-image-zoom-modal')) {
                // Get the current large image URL from the main display when modal opens
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

    {{-- "Please Select a Variation" Modal (Contents controlled by productVariantSelector Alpine component) --}}
    <x-modal name="select-variation-modal" maxWidth="lg" :show="false" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Please select a variation</h3>
                <button @click="$dispatch('close')" type="button" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6"/>
                </button>
            </div>
            {{-- Alpine will populate this based on productWithStockedVariants --}}
            <div class="mt-4 space-y-3 max-h-80 overflow-y-auto"
                 x-ref="variationModalContentContainer" {{-- Ref for Alpine if needed --}}
            >
                <template x-if="Object.keys(options).length > 0 && productWithStockedVariants.length > 0">
                    <template x-for="variantInModal in productWithStockedVariants" :key="variantInModal.id || variantInModal.clientId"> {{-- Use variant.id if available --}}
                         <div class="p-3 border rounded-md hover:border-pink-300 cursor-pointer transition-all"
                             :class="{ 'ring-2 ring-pink-500 border-pink-500 bg-pink-50 shadow-md': isVariantSelectedInModal(variantInModal) }"
                             @click="selectVariantInModal(variantInModal)">
                             <div class="flex justify-between items-center">
                                 <span class="text-sm font-medium text-gray-800" x-text="getVariantNameForModal(variantInModal.attributeValueIdsArray || variantInModal.attribute_value_ids)"></span>
                                 <span class="text-sm font-semibold text-pink-600" x-text="`GH₵ ${parseFloat(variantInModal.price).toFixed(2)}`"></span>
                             </div>
                             <p class="text-xs mt-1"
                                :class="variantInModal.quantity > 0 ? (variantInModal.quantity <= lowStockThresholdModal ? 'text-orange-600' : 'text-green-600') : 'text-red-600'">
                                <span x-text="variantInModal.quantity > 0 ? (variantInModal.quantity <= lowStockThresholdModal ? `${variantInModal.quantity} units left` : 'In Stock') : 'Out of Stock'"></span>
                             </p>
                         </div>
                    </template>
                </template>
                 <p x-show="Object.keys(options).length === 0 || productWithStockedVariants.length === 0" class="text-sm text-gray-500 py-4 text-center">
                    No variations currently available for selection.
                </p>
            </div>
            <div class="mt-6 sm:flex sm:flex-row-reverse sm:gap-3">
                <button type="button" @click="addSelectedVariantFromModalToCart()"
                        :disabled="!selectedVariantForModal || selectedVariantForModal.quantity <= 0"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:ml-3 sm:w-auto sm:text-sm disabled:bg-gray-400 disabled:cursor-not-allowed">
                    Add to Cart
                </button>
                <button type="button" @click="$dispatch('close'); selectedVariantForModal = null;" {{-- Clear selection on close --}}
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Continue Shopping
                </button>
            </div>
        </div>
    </x-modal>

</x-app-layout>@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productVariantSelector', (config) => ({
        // --- CONFIGURATION PASSED FROM BLADE ---
        productId: config.productId,
        productName: config.productName || 'Product',
        productBasePrice: parseFloat(config.productBasePrice) || 0,
        productBaseQuantity: parseInt(config.productBaseQuantity === undefined || config.productBaseQuantity === -1 ? 0 : config.productBaseQuantity) || 0,
        // Raw variant data: { 'valueId1-valueId2': {id, sku, price, quantity, attribute_value_ids (original array from PHP)} }
        allVariantsDataFromPHP: config.allVariantsData || {},
        options: config.options || {},   

       
        processedVariants: {}, 
        selectedOptions: {},    
        currentSelectedVariantOnPage: null, 
        quantity: 1,            
        currentPrice: 0,        
        itemsLeftText: '',      
        lowStockThreshold: 5,   

       
        addToCartButtonText: ''
        cartActionMessage: '',   
        cartActionMessageType: '', 

        // --- "SELECT VARIATION" MODAL STATE ---
        // productWithStockedVariants will be a filtered array from processedVariants
        productWithStockedVariants: [],
        selectedVariantInModal: null, 
        lowStockThresholdModal: 5, 

        // --- INITIALIZATION ---
        init() {
            this.currentPrice = this.productBasePrice;

            // Initialize selectedOptions for each attribute type to null
            Object.keys(this.options).forEach(attrId => {
                this.selectedOptions[parseInt(attrId)] = null; // Ensure attributeId is number for consistency
            });

            // Pre-process allVariantsDataFromPHP to create processedVariants and productWithStockedVariants
            let tempStockedVariants = [];
            for (const key in this.allVariantsDataFromPHP) {
                const phpVariant = this.allVariantsDataFromPHP[key];
                const variantForJS = {
                    ...phpVariant, // Spread original properties
                    // Ensure attribute_value_ids are numbers and sorted for consistent key generation/lookup
                    // This array is crucial for matching and naming
                    attributeValueIdsArray: Array.isArray(phpVariant.attribute_value_ids)
                                          ? phpVariant.attribute_value_ids.map(id => parseInt(id)).sort((a, b) => a - b)
                                          : []
                };
                this.processedVariants[key] = variantForJS; // Store processed variant by original key

                if (variantForJS.quantity > 0) {
                    tempStockedVariants.push(variantForJS);
                }
            }
            this.productWithStockedVariants = tempStockedVariants;

            // Initial UI update based on no selections (or default if any)
            this.updateDisplayBasedOnSelection();
            console.log('PDP Alpine Initialized. Product ID:', this.productId, 'Base Qty:', this.productBaseQuantity);
            console.log('Processed Variants:', JSON.parse(JSON.stringify(this.processedVariants)));
            console.log('Options for selection:', JSON.parse(JSON.stringify(this.options)));
            console.log('Stocked Variants for Modal:', JSON.parse(JSON.stringify(this.productWithStockedVariants)));
        },

        // --- COMPUTED-LIKE GETTERS (for readability in template) ---
        isSimpleProduct() {
            return Object.keys(this.options).length === 0;
        },

        areAllOptionsSelectedOnPage() {
            if (this.isSimpleProduct()) return true;
            return Object.values(this.selectedOptions).every(val => val !== null && val !== undefined);
        },

        // Checks if there's ANY way to purchase this product (either simple in stock, or any variant in stock)
        isAnythingPurchasable() {
            if (this.isSimpleProduct()) return this.productBaseQuantity > 0;
            return this.productWithStockedVariants.length > 0;
        },

        // Checks if the *currently selected combination on the page* can be added to cart
        canCurrentlyAddToCartFromPageSelection() {
            if (this.isSimpleProduct()) return this.productBaseQuantity > 0;
            return this.areAllOptionsSelectedOnPage() && this.currentSelectedVariantOnPage && this.currentSelectedVariantOnPage.quantity > 0;
        },

        // --- CORE LOGIC METHODS ---
        selectOption(attributeId, valueId) { // attributeId from loop, valueId from clicked option
            this.selectedOptions[parseInt(attributeId)] = parseInt(valueId); // Ensure numbers
            this.updateDisplayBasedOnSelection();
        },

        updateDisplayBasedOnSelection() {
            if (this.isSimpleProduct()) {
                this.currentPrice = this.productBasePrice;
                this.currentSelectedVariantOnPage = null;
                this.itemsLeftText = (this.productBaseQuantity > 0 && this.productBaseQuantity <= this.lowStockThreshold) ? `${this.productBaseQuantity} ${this.productBaseQuantity === 1 ? 'unit' : 'units'} left` : '';
            } else { // Product has variants
                if (this.areAllOptionsSelectedOnPage()) {
                    const currentKey = Object.values(this.selectedOptions).map(id => parseInt(id)).sort((a, b) => a - b).join('-');
                    this.currentSelectedVariantOnPage = this.processedVariants[currentKey] || null;

                    if (this.currentSelectedVariantOnPage) {
                        this.currentPrice = parseFloat(this.currentSelectedVariantOnPage.price);
                        const qty = this.currentSelectedVariantOnPage.quantity;
                        this.itemsLeftText = (qty > 0 && qty <= this.lowStockThreshold) ? `${qty} ${qty === 1 ? 'unit' : 'units'} left` : '';
                        // Adjust quantity input if selection changes and current quantity is invalid for new selection
                        if (this.quantity > qty || this.quantity === 0 && qty > 0) {
                           this.quantity = qty > 0 ? 1 : 0;
                        }
                    } else { // Valid combination of options selected, but no such variant exists in data
                        this.currentPrice = this.productBasePrice; // Or show error/ "Unavailable"
                        this.itemsLeftText = 'This combination is not available.';
                        this.quantity = 1; // Reset
                    }
                } else { // Not all options selected yet
                    this.currentPrice = this.productBasePrice;
                    this.currentSelectedVariantOnPage = null;
                    this.itemsLeftText = '';
                    this.quantity = 1; // Reset
                }
            }
            this.updateButtonText(); // Update button text based on the new state
        },

        updateButtonText() {
            if (this.addToCartButtonText === 'Adding...') return; // Preserve loading state

            if (!this.isAnythingPurchasable()) {
                this.addToCartButtonText = 'Out of Stock';
            } else if (!this.isSimpleProduct() && !this.areAllOptionsSelectedOnPage()) {
                this.addToCartButtonText = 'Select Options'; // Or still "Add to Cart" to trigger modal
            } else if (this.isSimpleProduct() && this.productBaseQuantity <= 0) {
                this.addToCartButtonText = 'Out of Stock';
            } else if (!this.isSimpleProduct() && this.currentSelectedVariantOnPage && this.currentSelectedVariantOnPage.quantity <= 0) {
                 this.addToCartButtonText = 'Out of Stock';
            }
            else {
                this.addToCartButtonText = 'Add to Cart';
            }
        },

        isSelected(attributeId, valueId) { // For styling selected option buttons
            return this.selectedOptions[parseInt(attributeId)] === parseInt(valueId);
        },

        // --- "SELECT VARIATION" MODAL INTERACTION ---
        getVariantNameForModal(attributeValueIdsArray) {
            if (!attributeValueIdsArray || attributeValueIdsArray.length === 0) return 'Variant';
            return attributeValueIdsArray.map(valueId => {
                for (const attrId in this.options) { // options keys are strings from PHP
                    const numericAttrId = parseInt(attrId);
                    // Find the attribute that contains this valueId
                    const valueObj = this.options[numericAttrId]?.values.find(v => parseInt(v.id) === parseInt(valueId));
                    if (valueObj) return valueObj.name;
                }
                return '?';
            }).sort().join(' / '); // Sort for consistent naming
        },

        isVariantSelectedInModal(variantInModal) {
            return this.selectedVariantInModal && this.selectedVariantInModal.id === variantInModal.id;
        },

        selectVariantInModal(variantInModal) {
            if (variantInModal.quantity > 0) {
                this.selectedVariantInModal = variantInModal;
            } else {
                // Optionally provide feedback if trying to select OOS variant in modal
                console.warn("Cannot select out-of-stock variant from modal.");
            }
        },

        addSelectedVariantFromModalToCart() {
            if (!this.selectedVariantInModal || this.selectedVariantInModal.quantity <= 0) {
                this.cartActionMessage = 'Please select an available variation from the list.';
                this.cartActionMessageType = 'error'; // This message is for the main page, not modal
                setTimeout(() => { this.cartActionMessage = ''; }, 5000);
                return;
            }
            // Add to cart with quantity 1 from modal
            this.performAddToCart(this.productId, this.selectedVariantInModal.id, 1);
            this.$dispatch('close'); // Generic close for the x-modal component
            this.selectedVariantInModal = null; // Reset modal selection
        },

        // --- ADD TO CART ACTIONS ---
        handleAddToCartAttempt() {A
            this.cartActionMessage = ''; this.cartActionMessageType = ''; // Clear previous messages

            if (this.isSimpleProduct()) {
                if (this.productBaseQuantity > 0 && this.quantity > 0 && this.quantity <= this.productBaseQuantity) {
                    this.performAddToCart(this.productId, null, this.quantity);
                } else if (this.productBaseQuantity <= 0) {
                    this.cartActionMessage = 'This product is out of stock.';
                    this.cartActionMessageType = 'error';
                } else {
                     this.cartActionMessage = `Only ${this.productBaseQuantity} units available. Please adjust quantity.`;
                    this.cartActionMessageType = 'error';
                }
            } else { // Product has variants
                if (this.areAllOptionsSelectedOnPage() && this.currentSelectedVariantOnPage) {
                    if (this.currentSelectedVariantOnPage.quantity > 0 && this.quantity > 0 && this.quantity <= this.currentSelectedVariantOnPage.quantity) {
                        this.performAddToCart(this.productId, this.currentSelectedVariantOnPage.id, this.quantity);
                    } else if (this.currentSelectedVariantOnPage.quantity <= 0) {
                        this.cartActionMessage = 'This selected variation is out of stock.';
                        this.cartActionMessageType = 'error';
                    } else {
                        this.cartActionMessage = `Only ${this.currentSelectedVariantOnPage.quantity} units of this variation available. Please adjust quantity.`;
                        this.cartActionMessageType = 'error';
                    }
                } else if (this.isAnythingPurchasable()) {
                    // Not all options selected on page OR selected combination is OOS, but some other variants ARE in stock
                    this.selectedVariantInModal = null; // Reset modal selection
                    this.$dispatch('open-modal', 'select-variation-modal'); // Open the "select variation" modal
                } else {
                    // No variants in stock at all, or not enough options selected and nothing purchasable
                    this.cartActionMessage = 'This product is currently out of stock in all variations.';
                    this.cartActionMessageType = 'error';
                }
            }
            if(this.cartActionMessage) setTimeout(() => { this.cartActionMessage = ''; }, 7000);
        },

        performAddToCart(productId, variantId, quantity) {
            const originalButtonText = this.addToCartButtonText;
            this.addToCartButtonText = 'Adding...';

            let payload = { product_id: productId, quantity: quantity };
            if (variantId) payload.variant_id = variantId;

            fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                if (!response.ok) return response.json().then(errData => { errData.status = response.status; throw errData; });
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.cartActionMessage = data.message || 'Item added to cart!';
                    this.cartActionMessageType = 'success';
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
                    // Potentially update client-side stock perception or reset quantity
                    // For simplicity now, user must refresh or rely on next selection to see updated stock text
                    // this.quantity = 1; // Reset quantity input
                } else {
                    this.cartActionMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Could not add item to cart.');
                    this.cartActionMessageType = 'error';
                }
            })
            .catch(errorData => {
                console.error('Add to cart error:', errorData);
                let msg = 'An error occurred. Please try again.';
                if (errorData && errorData.message) {
                    msg = errorData.message;
                    if (errorData.errors) msg += ': ' + Object.values(errorData.errors).flat().join('; ');
                } else if (typeof errorData === 'string') msg = errorData;
                this.cartActionMessage = msg;
                this.cartActionMessageType = 'error';
            })
            .finally(() => {
                this.updateButtonText(); // Recalculate button text based on current state
                if (this.addToCartButtonText === 'Adding...') this.addToCartButtonText = originalButtonText; // Failsafe if updateButtonText didn't change it
                setTimeout(() => { this.cartActionMessage = ''; this.cartActionMessageType = ''; }, 7000);
            });
        }
    }));
});
</script>
@endpush