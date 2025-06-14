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
            {{--MAIN PRODUCT CARD--}}
            <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md lg:grid lg:grid-cols-3 lg:gap-8 items-start">

               {{-- Left Column: Product Images with HORIZONTAL Thumbnails Below --}}
                <div class="lg:col-span-1 mb-8 lg:mb-0">
                    @php
                        $imagesCollection = $product->images;
                        $mainImage = $imagesCollection->first();
                    @endphp

                    {{-- The Alpine component wraps a simple container with spacing --}}
                    <div class="space-y-4" 
                        x-data="{
                            images: {{ Js::from($imagesCollection->map(fn($img) => ['url' => $img->image_url, 'alt' => $img->alt ?? $product->name])) }},
                            currentImage: {{ Js::from($mainImage?->image_url ?? asset('images/placeholder.png')) }},
                            currentImageAlt: {{ Js::from($mainImage?->alt ?? $product->name) }},
                            
                            changeImage(imageObject) {
                                this.currentImage = imageObject.url;
                                this.currentImageAlt = imageObject.alt;
                            }
                        }">
                        
                        {{-- MAIN IMAGE --}}
                        <div class="relative bg-gray-100 rounded-lg overflow-hidden aspect-square border border-gray-200 group">
                            <img :src="currentImage" :alt="currentImageAlt" class="w-full h-full object-contain cursor-pointer" @click="$dispatch('open-modal', 'product-image-zoom-modal')">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <x-heroicon-o-magnifying-glass-plus class="w-12 h-12 text-white"/>
                            </div>
                        </div>

                        {{-- THUMBNAILS ROW (Below) --}}
                        {{-- Only show this row if there are multiple images --}}
                        @if($imagesCollection->count() > 1)
                            <div class="flex flex-wrap gap-2"> {{-- flex-wrap allows thumbnails to wrap to the next line if they don't fit --}}
                                <template x-for="(image, index) in images" :key="index">
                                    <button @click="changeImage(image)"
                                            :class="{ 'ring-2 ring-pink-500 border-pink-300': currentImage === image.url, 'border-gray-200 hover:border-pink-300': currentImage !== image.url }"
                                            class="w-16 h-16 bg-white rounded-md overflow-hidden focus:outline-none border transition-all">
                                        <img :src="image.url" :alt="image.alt + ' thumbnail'" class="w-full h-full object-cover">
                                    </button>
                                </template>
                            </div>
                        @endif
                        
                    </div>
                </div>
                
               {{-- Middle Column: Product Details --}}
                <div class="lg:col-span-1 space-y-5"
                     x-data="productPage({
                         productId: {{ $product->id }},
                         productName: {{ Js::from($product->name) }},
                         productPrice: {{ (float)$product->price }},
                         productQuantity: {{ (int)$product->quantity }},                         
                         initialIsInCart: {{ session()->has("cart.{$product->id}") ? 'true' : 'false' }}
                     })">

                    {{-- Top Section: Product Name and Wishlist Button --}}
                    <div class="flex items-start justify-between">
                        <h1 class="text-xl lg:text-2xl font-semibold text-gray-800 leading-tight pr-4">{{ $product->name }}</h1>
                        
                        {{-- WISHLIST BUTTON (RESTORED) --}}
                        @auth
                            <div x-data="wishlistButton({ productId: {{ $product->id }}, initialIsInWishlist: {{ Auth::user()->hasInWishlist($product) ? 'true' : 'false' }} })" class="flex-shrink-0">
                                <button @click="toggleWishlist" type="button" :disabled="isLoading" :title="isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'" class="p-1.5 text-gray-400 hover:text-pink-500 disabled:opacity-50">
                                    <template x-if="isLoading">
                                        <svg class="animate-spin h-5 w-5 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!isLoading">
                                        <x-heroicon-o-heart class="w-5 h-5" ::class="{ 'text-pink-500 fill-current': isInWishlist }" />
                                    </template>
                                </button>
                            </div>
                        @endauth
                        @guest
                            <a href="{{ route('login') }}?redirect={{ url()->current() }}" title="Add to Wishlist" class="p-1.5 text-gray-400 hover:text-pink-500">
                                <x-heroicon-o-heart class="w-5 h-5"/>
                            </a>
                        @endguest
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
                            <span class="text-2xl font-bold text-gray-900">GH₵ {{ number_format($product->price, 2) }}</span>
                        </div>
                        <div class="text-sm min-h-[20px]">
                            @if($product->quantity > 0)
                                <p class="text-green-600 font-medium">In stock</p>
                            @else
                                <p class="text-red-600 font-medium">Out of stock</p>
                            @endif
                        </div>
                    </div>

                    {{-- Quantity Input for Simple Products --}}
                    <div class="mt-4">
                        <label for="pdp-quantity" class="block text-sm font-medium text-gray-900 mb-1">Quantity</label>
                        <div class="relative flex items-center max-w-[8rem]">
                            <button type="button" @click="quantity > 1 ? quantity-- : null" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-l-lg p-2.5 h-10 disabled:opacity-50" :disabled="quantity <= 1">-</button>
                            <input type="number" id="pdp-quantity" x-model.number="quantity" min="1" :max="productQuantity" class="bg-gray-50 border-x-0 border-gray-300 h-10 text-center text-gray-900 text-sm">
                            <button type="button" @click="quantity < productQuantity ? quantity++ : null" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-r-lg p-2.5 h-10 disabled:opacity-50" :disabled="quantity >= productQuantity">+</button>
                        </div>
                    </div>

                    {{-- Action Area --}}
                    <div class="mt-auto pt-4">
                        <button type="button" @click="toggleCart()"
                                :disabled="isLoading || productQuantity <= 0"
                                class="w-full flex items-center justify-center rounded-md border border-transparent px-8 py-3 text-base font-medium text-white transition-colors focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 disabled:bg-gray-400 disabled:cursor-not-allowed"
                                :class="{
                                    'bg-pink-600 hover:bg-pink-700': !isInCart,
                                    'bg-red-600 hover:bg-red-700': isInCart
                                }">
                            
                            {{-- Loading Spinner --}}
                            <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            
                            {{-- Add to Cart Icon/Text --}}
                            <template x-if="!isInCart">
                                <span class="flex items-center">
                                    <x-heroicon-o-shopping-cart class="w-6 h-6 mr-3"/>
                                    <span x-text="isLoading ? 'Adding...' : 'Add to Cart'"></span>
                                </span>
                            </template>

                            {{-- Remove from Cart Icon/Text --}}
                            <template x-if="isInCart">
                                <span class="flex items-center">
                                    <x-heroicon-o-trash class="w-6 h-6 mr-3"/>
                                    <span x-text="isLoading ? 'Removing...' : 'Remove from Cart'"></span>
                                </span>
                            </template>
                        </button>
                    </div>

                </div> {{-- End Middle Column --}}            

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

    {{--VARIANT MODAL--}}
    <x-modal name="select-variation-modal" maxWidth="lg" focusable>
    <div class="p-4 sm:p-6 bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Please select a variation</h3>
            <button @click="$dispatch('close')" type="button" class="text-gray-400 hover:text-gray-600"><x-heroicon-o-x-mark class="w-6 h-6"/></button>
        </div>
        <div class="mt-4 space-y-3 max-h-80 overflow-y-auto custom-scrollbar-mobile pr-2">
            <template x-for="variant in allStockedVariants" :key="variant.id">
                 <div class="flex justify-between items-center p-3 border rounded-md">
                     {{-- Variant Info --}}
                     <div class="flex-grow pr-4">
                        <span class="text-sm font-medium text-gray-800" x-text="getVariantName(variant.attributeValueIds)"></span>
                        <div class="flex items-center mt-1">
                            <span class="text-sm font-semibold text-pink-600" x-text="`GH₵ ${parseFloat(variant.price).toFixed(2)}`"></span>
                        </div>
                        <p class="text-xs mt-1" :class="variant.quantity <= 10 ? 'text-orange-600' : 'text-green-600'">
                            <span x-text="variant.quantity <= 10 ? `${variant.quantity} units left` : 'In Stock'"></span>
                        </p>
                     </div>
                     {{-- Quantity Stepper in Modal --}}
                     <div class="relative flex items-center max-w-[8rem] flex-shrink-0">
                        <button type="button" @click="updateCart(variant, getQuantityInCart(variant.id) - 1)"
                                class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-l-lg p-2.5 h-10 disabled:opacity-50">
                            <x-heroicon-s-minus class="w-4 h-4 text-gray-900"/>
                        </button>
                        <input type="number" readonly :value="getQuantityInCart(variant.id)" class="bg-gray-50 border-x-0 border-gray-300 h-10 text-center text-gray-900 text-sm w-full py-2.5 pointer-events-none">
                        <button type="button" @click="updateCart(variant, getQuantityInCart(variant.id) + 1)"
                                :disabled="getQuantityInCart(variant.id) >= variant.quantity"
                                class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-r-lg p-2.5 h-10 disabled:opacity-50">
                            <x-heroicon-s-plus class="w-4 h-4 text-gray-900"/>
                        </button>
                     </div>
                 </div>
            </template>
        </div>
        <div class="mt-6 sm:flex sm:flex-row-reverse sm:gap-3">
            <a href="{{ route('cart.index') }}" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-base font-medium text-white hover:bg-pink-700 sm:ml-3 sm:w-auto sm:text-sm">
                Go to Cart
            </a>
            <button type="button" @click="$dispatch('close')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                Continue Shopping
            </button>
        </div>
    </div>
</x-modal>

</x-app-layout>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {

    Alpine.data('productPage', (config) => ({
        // --- Data from Blade ---
        productId: config.productId,
        productName: config.productName,
        productPrice: config.productPrice,
        productQuantity: config.productQuantity,
        
        // --- Component State ---
        isInCart: config.initialIsInCart, // Is this specific simple product in the cart?
        isLoading: false,
        quantity: 1, // The quantity the user selects on the page

        // --- The Core Method ---
        toggleCart() {
            if (this.isLoading) return;
            this.isLoading = true;

            const endpoint = this.isInCart 
                ? '{{ route("cart.remove-item") }}' 
                : '{{ route("cart.add") }}';
            
            const payload = {
                product_id: this.productId,
                quantity: this.quantity, // Use the quantity from the input
                // No variant_id needed for this simple case
            };

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.ok ? res.json() : res.json().then(err => Promise.reject(err)))
            .then(data => {
                if (data.success) {
                    // Update state based on which action was just performed
                    this.isInCart = !this.isInCart;

                    // Dispatch toast notification
                    window.dispatchEvent(new CustomEvent('toast-show', { 
                        detail: { type: 'success', message: data.message } 
                    }));

                    // Dispatch event for header cart count update
                    if (data.cart_distinct_items_count !== undefined) {
                        window.dispatchEvent(new CustomEvent('cart-updated', { 
                            detail: { cart_distinct_items_count: data.cart_distinct_items_count } 
                        }));
                    }
                } else {
                    window.dispatchEvent(new CustomEvent('toast-show', { 
                        detail: { type: 'error', message: data.message || 'An operation failed.' } 
                    }));
                }
            })
            .catch(err => {
                console.error('Cart toggle error:', err);
                window.dispatchEvent(new CustomEvent('toast-show', { 
                    detail: { type: 'error', message: err.message || 'An error occurred.' } 
                }));
            })
            .finally(() => {
                this.isLoading = false;
            });
        }
    }));

});
</script>
@endpush