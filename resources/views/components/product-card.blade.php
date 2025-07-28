{{--components product-card --}}
@props([
    'product',
    'userWishlistProductIds' => []
])

@php
    use Illuminate\Support\Str;

    $name = $product->name ?? 'Product Name';

    $productUrl = route('products.show', $product->slug ?? $product->id);

    $imageUrl = $product->getFirstMediaUrl('default', 'card_thumbnail') ?? 'https://placehold.net/400x400.png';
    
    $price = (float)($product->price ?? 0);
    $compareAtPrice = (float)($product->compare_at_price ?? 0);
    $variantsCount = $product->variants_count ?? 0;
    $reviewCount = $product->reviews_count ?? 0;
    $rating = $product->reviews_avg_rating ?? 0;
    $currentStock = $product->quantity ?? 0;
    $isGenerallyAvailable = ($variantsCount > 0) || ($currentStock > 0);

    $discountPercentage = 0;
    if ($compareAtPrice > 0 && $compareAtPrice > $price) {
        $discountPercentage = round((($compareAtPrice - $price) / $compareAtPrice) * 100);
    }
    
    $lowStockThreshold = 10;
    $showLowStockIndicator = $isGenerallyAvailable && $variantsCount == 0 && $currentStock > 0 && $currentStock <= $lowStockThreshold;

    $initialIsInWishlist = auth()->check() && in_array($product->id, $userWishlistProductIds);
    
    $rating = $product->approved_reviews_avg_rating ?? $product->reviews_avg_rating ?? 0;
@endphp

<div class="bg-white rounded-md shadow-sm hover:shadow-lg transition-shadow duration-300 flex flex-col overflow-hidden group border border-gray-200 hover:border-pink-300 text-sm">
    <div class="relative">
        <a href="{{ $productUrl }}" class="block aspect-[4/3] sm:aspect-square bg-gray-50 overflow-hidden">
            <img src="{{ $imageUrl }}" alt="{{ $name }}" class="w-full h-full object-contain transition-transform duration-300 ">
        </a>
        @if($discountPercentage > 0)
             <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-sm">-{{ $discountPercentage }}%</span>
        @endif

        <div x-data="wishlistButton({ 
                productId: {{ $product->id }}, 
                initialIsInWishlist: {{ $initialIsInWishlist ? 'true' : 'false' }},
                isAuthenticated: {{ auth()->check() ? 'true' : 'false' }},
                loginUrl: '{{ route('login') }}'
             })" 
             class="absolute top-1.5 left-1.5 z-10">
           <button @click.prevent="handleClick()"
                    type="button"
                    :title="isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'"
                    :disabled="isLoading"
                    class="p-1.5 bg-white/80 hover:bg-white rounded-full text-gray-500 hover:text-pink-600 shadow-sm transition focus:outline-none focus:ring-1 focus:ring-pink-400 disabled:opacity-50">
                <template x-if="isLoading">
                    <svg class="animate-spin h-3.5 w-3.5 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </template>
                <template x-if="!isLoading">
                    {{-- This uses the exact heart path from your product details page, but smaller --}}
                    <svg :class="{ 'text-pink-500': isInWishlist }" class="w-3.5 h-3.5" :fill="isInWishlist ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </template>
            </button>
        </div>
    </div>

    <div class="p-2.5 flex flex-col flex-grow">
        <h3 class="text-xs font-normal text-gray-700 leading-tight mb-1 min-h-[32px] line-clamp-2">
            <a href="{{ $productUrl }}" class="hover:text-pink-600">{{ $name }}</a>
        </h3>

        <div class="mb-1"><p class="text-base font-semibold text-gray-900 inline-block">GH₵ {{ number_format($price, 2) }}</p> @if($discountPercentage > 0)<span class="text-[12px] text-gray-500 line-through ml-1.5">GH₵ {{ number_format($compareAtPrice, 2) }}</span>@endif</div>
        
        <div class="mb-1.5 min-h-[16px]">
            @if($reviewCount > 0)
                    <div class="flex items-center text-xs text-gray-500 hover:text-pink-600">
                        <div class="flex items-center">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= floor($rating) ? 'text-yellow-400' : 'text-gray-300' }}" 
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="ml-1">({{ $reviewCount }})</span>
                    </div>
                @endif
        </div>

        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
            @if($variantsCount > 0)
                <span>{{ $variantsCount }} {{ Str::plural('variant', $variantsCount) }}</span>
            @endif           
        </div>

        {{-- Stock status --}}

        {{-- Low stock indicator --}}
        <div class="min-h-[22px] mb-1.5">
            @if($showLowStockIndicator)
                <p class="text-[11px] text-gray-600 font-medium mb-0.5">{{ $currentStock }} {{ Str::plural('item', $currentStock) }} left</p>
                <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">                  
                    <div class="bg-orange-400 h-1.5 rounded-full" style="width: {{ ($currentStock / $lowStockThreshold) * 100 }}%"></div>
                </div>
            @endif
        </div>

        <div class="mt-auto pt-1 h-9">
            @if($isGenerallyAvailable)
                @if($product->variants_count > 0)
                    <a href="{{ route('products.show', $product->slug) }}" class="block w-full text-center rounded bg-pink-600 px-2 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-pink-700 transition-colors">Select Options</a>
                @else
                    {{-- This Alpine component now includes the toast dispatch calls --}}
                    <div x-data="{
                                quantity: {{ \App\Models\Cart::getItemQuantity($product->id) }},
                                isLoading: false,
                                maxStock: {{ $currentStock }},
                                productId: {{ $product->id }},
                                debounceTimer: null,

                                updateCart(newQuantity) {
                                    if (this.isLoading) return;
                                    if (newQuantity < 0) return;
                                    if (newQuantity > this.maxStock) {
                                        window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: 'Maximum stock reached.' }}));
                                        return;
                                    }
                                    this.quantity = newQuantity;
                                    clearTimeout(this.debounceTimer);
                                    this.isLoading = true;
                                    this.debounceTimer = setTimeout(() => {
                                        if (this.quantity === 0) {
                                            this.removeFromCart();
                                        } else {
                                            // The sendUpdateRequest now handles both add and update
                                            this.sendUpdateRequest(this.quantity);
                                        }
                                    }, 350);
                                },

                                sendUpdateRequest(qty) {
                                  
                                    fetch('{{ route("cart.add") }}', { 
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
                                        body: JSON.stringify({ product_id: this.productId, quantity: qty, is_replacement: true }) 
                                    })
                                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                                    .then(({ ok, data }) => {                                        
                                        window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: ok ? 'success' : 'error', message: data.message || 'Cart updated.' } }));
                                       
                                        if (ok) {
                                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: data.cart_count } }));
                                        }
                                    })
                                    .finally(() => this.isLoading = false);
                                },

                                removeFromCart() {
                                    fetch('{{ route("cart.remove-simple") }}', {
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
                                        body: JSON.stringify({ product_id: this.productId })
                                    })
                                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                                    .then(({ ok, data }) => {
                                     
                                        window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: ok ? 'success' : 'error', message: data.message || 'Item removed.' } }));
                                        
                                        if (ok) {
                                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: data.cart_count } }));
                                        }
                                    })
                                    .finally(() => this.isLoading = false);
                                }
                            }">
                        
                        <button x-show="quantity === 0" @click="updateCart(1)" :disabled="isLoading"
                                class="w-full h-9 rounded bg-pink-600 px-2 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-pink-700 transition-colors flex items-center justify-center disabled:bg-pink-400">
                            <svg x-show="isLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-show="!isLoading">Add to Cart</span>
                        </button>

                        <div x-show="quantity > 0" x-cloak class="flex items-center justify-between h-9 rounded border border-gray-300 bg-white">
                            <button @click="updateCart(quantity - 1)" :disabled="isLoading" class="px-3 text-lg text-gray-600 hover:text-pink-600 disabled:opacity-50 h-full">-</button>
                            <span x-show="!isLoading" class="px-2 text-sm font-medium text-gray-800" x-text="quantity"></span>
                            <svg x-show="isLoading" class="animate-spin h-4 w-4 text-pink-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            
                            
                            <button @click="updateCart(quantity + 1)" :disabled="isLoading || quantity >= maxStock" class="px-3 text-lg text-gray-600 hover:text-pink-600 disabled:opacity-50 h-full">+</button>
                        </div>
                    </div>
                @endif
            @else
                <button type="button" disabled class="block w-full h-9 text-center rounded bg-gray-300 px-2 py-2 text-xs sm:text-sm font-semibold text-gray-500 cursor-not-allowed">Out of Stock</button>
            @endif
        </div>
    </div>
</div>