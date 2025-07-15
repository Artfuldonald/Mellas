@props([
    'title',
    'viewAllUrl',
    'products',
    'themeColor' => 'pink' 
])

{{-- This component requires products to be passed. It will not render if the collection is empty or null. --}}
@if(isset($products) && $products->isNotEmpty())
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="flex items-center justify-between mb-4 sm:mb-6 bg-{{ $themeColor }}-600 text-white p-3 rounded-md">
            <h2 class="text-xl sm:text-2xl font-bold">{{ $title }}</h2>
            <a href="{{ $viewAllUrl }}" class="text-white hover:underline font-medium flex items-center text-sm">
                See All <x-heroicon-o-chevron-right class="w-4 h-4 ml-1" />
            </a>
        </div>

        {{-- Product Slider with Alpine.js for navigation --}}
        <div x-data="productSlider()" class="relative">
            <!-- Left Arrow -->
            <button x-show="!atStart" @click="prev()"
                    class="absolute top-1/2 -left-4 z-20 -translate-y-1/2 bg-white/80 backdrop-blur-sm p-2 rounded-full shadow-md hover:bg-white focus:outline-none focus:ring-2 focus:ring-{{ $themeColor }}-500">
                <x-heroicon-o-chevron-left class="w-6 h-6 text-gray-700" />
            </button>

            <!-- Slider Container -->
            <div x-ref="slider" @scroll="checkScroll()"
                 class="flex overflow-x-auto space-x-4 pb-4 scroll-smooth"
                 style="scrollbar-width: none; -ms-overflow-style: none;">
                 &::-webkit-scrollbar { display: none; }
                
                @php $userWishlistProductIds = Auth::check() ? Auth::user()->wishlistItems()->pluck('product_id')->toArray() : []; @endphp

                {{-- Loop through up to 10 products --}}
                @foreach($products->take(10) as $product)
                    <div class="flex-shrink-0 w-48 sm:w-56 product-card bg-white border rounded-lg p-3 relative flex flex-col">
                        <div class="relative">
                            @if($product->discount_percentage)
                                <div class="bg-{{ $themeColor }}-500 text-white absolute top-2 left-2 text-xs px-2 py-1 rounded-full font-bold z-10">
                                    -{{ $product->discount_percentage }}%
                                </div>
                            @endif
                            {{-- Wishlist Button --}}
                            <div class="absolute top-1 right-1 z-10" x-data="wishlistButton({
                                productId: {{ $product->id }},
                                initialIsInWishlist: {{ in_array($product->id, $userWishlistProductIds) ? 'true' : 'false' }},
                                isAuthenticated: {{ Auth::check() ? 'true' : 'false' }},
                                loginUrl: '{{ route('login') }}'
                            })">
                                <button @click="handleClick()" :disabled="isLoading" class="p-1.5 bg-white/70 backdrop-blur-sm rounded-full text-gray-600 hover:text-{{ $themeColor }}-500 hover:bg-white transition-all duration-200">
                                    <template x-if="isInWishlist"><x-heroicon-s-heart class="w-5 h-5 text-{{ $themeColor }}-500"/></template>
                                    <template x-if="!isInWishlist"><x-heroicon-o-heart class="w-5 h-5"/></template>
                                </button>
                            </div>

                            <a href="{{ route('products.show', $product->slug) }}">
                                <img src="{{ $product->image_url ?? Storage::url($product->images->first()->path ?? 'images/placeholder.png') }}" alt="{{ $product->name }}" class="w-full h-40 object-contain rounded mb-3">
                            </a>
                        </div>
                        
                        <div class="flex-grow">
                            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-{{ $themeColor }}-600">
                                <h3 class="text-sm font-medium text-gray-800 mb-2 line-clamp-2 h-10">{{ $product->name }}</h3>
                            </a>
                            <div class="space-y-1">
                                <div class="text-lg font-bold text-gray-900">GH₵ {{ number_format($product->current_price, 2) }}</div>
                                @if($product->original_price > $product->current_price)
                                    <div class="text-sm text-gray-500 line-through">GH₵ {{ number_format($product->original_price, 2) }}</div>
                                @endif
                            </div>
                        </div> 
                        {{-- ***** START: ADDED "ADD TO CART" BUTTON LOGIC ***** --}}
                        <div class="mt-auto pt-2">
                            @if($product->variants()->count() > 0)
                                <a href="{{ route('products.show', $product->slug) }}" class="block w-full text-center rounded bg-pink-500 px-2 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-pink-600 transition-colors">Select Options</a>
                            @elseif($product->stockCount() > 0)
                                {{-- Use the working productCardActions component --}}
                                <div x-data="productCardActions({
                                        productId: {{ $product->id }},
                                        initialQuantity: {{ \App\Models\Cart::getItemQuantity($product->id) }},
                                        maxStock: {{ $product->stockCount() }}
                                    })">
                                    <button x-show="quantity === 0" @click="updateCart(1)" :disabled="isLoading" class="w-full h-9 rounded bg-pink-600 px-2 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-pink-700 transition-colors flex items-center justify-center disabled:bg-pink-400">
                                        <span x-show="!isLoading">Add to Cart</span>
                                        <svg x-show="isLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </button>
                                    <div x-show="quantity > 0" x-cloak class="flex items-center justify-between h-9 rounded border border-gray-300 bg-white">
                                        <button @click="updateCart(quantity - 1)" :disabled="isLoading" class="px-3 text-lg text-gray-600 hover:text-pink-600 disabled:opacity-50 h-full">-</button>
                                        <span x-show="!isLoading" class="px-2 text-sm font-medium text-gray-800" x-text="quantity"></span>
                                        <svg x-show="isLoading" class="animate-spin h-4 w-4 text-pink-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <button @click="updateCart(quantity + 1)" :disabled="isLoading || quantity >= maxStock" class="px-3 text-lg text-gray-600 hover:text-pink-600 disabled:opacity-50 h-full">+</button>
                                    </div>
                                </div>
                            @else
                                <button disabled class="w-full text-center rounded bg-gray-300 px-2 py-2 text-xs sm:text-sm font-semibold text-gray-500 cursor-not-allowed">Out of Stock</button>
                            @endif
                        </div>
                        {{-- ***** END: ADDED "ADD TO CART" BUTTON LOGIC ***** --}}                       
                        
                    </div>
                @endforeach
            </div>

            <!-- Right Arrow -->
            <button x-show="!atEnd" @click="next()"
                    class="absolute top-1/2 -right-4 z-20 -translate-y-1/2 bg-white/80 backdrop-blur-sm p-2 rounded-full shadow-md hover:bg-white focus:outline-none focus:ring-2 focus:ring-{{ $themeColor }}-500">
                <x-heroicon-o-chevron-right class="w-6 h-6 text-gray-700" />
            </button>
        </div>
    </div>
</section>
@endif