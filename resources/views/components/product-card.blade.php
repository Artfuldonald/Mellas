{{-- components/product-card.blade.php --}}
{{-- This component displays a product card with image, name, price, reviews, stock status, and an "Add to Cart" button. --}}
{{-- It supports wishlist functionality and handles both simple products and products with variants. --}}
@props([
    'product',
    'userWishlistProductIds' => []
])

@php
    use Illuminate\Support\Str;

    $name = $product->name ?? 'Product Name';
    $price = (float)($product->price ?? 0);
    $compareAtPrice = (float)($product->compare_at_price ?? 0);
    $productUrl = route('products.show', $product->slug ?? $product->id);

    $reviewCount = $product->reviews_count ?? 0;
    $rating = $product->reviews_avg_rating ?? ($product->rating ?? 0);

    // Handle image gallery - ensure proper handling for single or multiple images
    $images = $product->images ?? collect();
    $hasImages = $images->isNotEmpty();
    $mainImage = $hasImages ? $images->first() : null;
    $imageUrl = $mainImage?->image_url ?? asset('images/placeholder.png');
    $altText = $mainImage?->alt ?? $name;

    $isGenerallyAvailable = true;
    if ($product->variants_count == 0 && property_exists($product, 'quantity')) {
        $isGenerallyAvailable = $product->quantity > 0;
    } elseif ($product->variants_count > 0) {
        $isGenerallyAvailable = true;
    } else if (!property_exists($product, 'quantity') && $product->variants_count == 0) {
        $isGenerallyAvailable = false;
    }

    $currentStock = $product->quantity ?? 0;
    $discountPercentage = 0;
    if ($compareAtPrice > 0 && $compareAtPrice > $price) {
        $discountPercentage = round((($compareAtPrice - $price) / $compareAtPrice) * 100);
    }

    $lowStockThreshold = 10;
    $itemsLeftText = null;
    $stockBarPercentage = null;
    if ($isGenerallyAvailable && $product->variants_count == 0 && $currentStock > 0 && $currentStock <= $lowStockThreshold) {
        $itemsLeftText = $currentStock . ' ' . Str::plural('item', $currentStock) . ' left';
        $stockBarPercentage = ($currentStock / $lowStockThreshold) * 100;
        if ($stockBarPercentage < 10) $stockBarPercentage = 10;
    }

    // Calculate initialIsInWishlist based on the passed $userWishlistProductIds prop
    $_initialIsInWishlist_for_this_card = Auth::check() && in_array($product->id, $userWishlistProductIds);
@endphp

<div class="bg-white rounded-md shadow-sm hover:shadow-lg transition-shadow duration-300 flex flex-col overflow-hidden group border border-pink-100 text-sm">
    {{-- Image Section --}}
    <div class="relative">
        <a href="{{ $productUrl }}" class="block aspect-[4/3] sm:aspect-square bg-gray-50 overflow-hidden">
            <img src="{{ $imageUrl }}" alt="{{ $altText }}" class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105">
        </a>

        {{-- Discount Badge --}}
        @if($discountPercentage > 0)
            <span class="absolute top-1 left-1 bg-pink-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-sm">-{{ $discountPercentage }}%</span>
        @endif

        {{-- WISHLIST BUTTON - ALPINE DRIVEN --}}
        @auth
            <div x-data="wishlistButton({ productId: {{ $product->id }}, initialIsInWishlist: {{ $_initialIsInWishlist_for_this_card ? 'true' : 'false' }} })" class="absolute top-1.5 right-1.5 z-10">
                <button @click.prevent="toggleWishlist"
                        type="button"
                        :aria-label="isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'"
                        :title="isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'"
                        :disabled="isLoading"
                        class="p-1.5 bg-white/70 hover:bg-white rounded-full text-gray-600 hover:text-pink-600 shadow-sm transition focus:outline-none focus:ring-1 focus:ring-pink-400 disabled:opacity-50">
                    <template x-if="isLoading">
                        <svg class="animate-spin h-4 w-4 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!isLoading && isInWishlist">
                        <x-heroicon-s-heart class="w-4 h-4 text-pink-500" />
                    </template>
                    <template x-if="!isLoading && !isInWishlist">
                        <x-heroicon-o-heart class="w-4 h-4" />
                    </template>
                </button>
            </div>
        @endauth
    </div>

    {{-- Content Section --}}
    <div class="p-2.5 flex flex-col flex-grow"> 
        <h3 class="text-xs font-normal text-gray-700 leading-tight mb-1 min-h-[32px] line-clamp-2"> 
            <a href="{{ $productUrl }}" class="hover:text-pink-600">
                {{ $name }}
            </a>
        </h3>

        {{-- Price and Discount --}}
        <div class="mb-1">
            <p class="text-base font-semibold text-gray-900 inline-block">GH₵ {{ number_format($price, 2) }}</p>
            @if($discountPercentage > 0)
                <span class="text-[12px] text-gray-500 line-through ml-1.5">GH₵ {{ number_format($compareAtPrice, 2) }}</span>
                <span class="ml-1.5 bg-pink-100 text-pink-700 text-[10px] font-semibold px-1.5 py-0.5 rounded-sm">-{{ $discountPercentage }}%</span> 
            @endif
        </div>

        {{-- Rating --}}
        <div class="mb-1.5 min-h-[16px]">
            @if($reviewCount > 0)
                <a href="{{ $productUrl }}#reviews" class="flex items-center text-xs text-gray-500 hover:text-pink-600">
                    <div class="flex">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star class="w-3.5 h-3.5 {{ $i <= round($rating) ? 'text-yellow-400' : 'text-gray-300' }}"/>
                        @endfor
                    </div>
                    <span class="ml-1">({{ $reviewCount }})</span>
                </a>
            @endif
        </div>

        {{-- Stock Information --}}
        @if($itemsLeftText)
            <div class="mb-1.5">
                <p class="text-[10px] text-pink-600 font-medium">{{ $itemsLeftText }}</p>
                @if($stockBarPercentage !== null)
                <div class="w-full bg-gray-200 rounded-full h-1 mt-0.5 overflow-hidden">
                    <div class="bg-pink-500 h-1 rounded-full" style="width: {{ $stockBarPercentage }}%"></div>
                </div>
                @endif
            </div>
        @else
            {{-- Placeholder to maintain consistent card height when no stock info bar --}}
            <div class="h-[18px] mb-1.5"></div>
        @endif

        {{-- Action Button (Full Width) --}}
        <div class="mt-auto pt-1">
            @if($isGenerallyAvailable)
                @if($product->variants_count > 0)
                    <a href="{{ $productUrl }}"
                       class="block w-full text-center rounded bg-pink-600 px-2 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 transition-colors">
                        Add to Cart
                    </a>
                @else
                    <div x-data="{ isAdding: false, isAdded: false }" class="product-card-add-to-cart-container">
                        <form @submit.prevent="
                            isAdding = true;
                            fetch('{{ route('cart.add') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    product_id: {{ $product->id }},
                                    quantity: 1
                                })
                            })
                            .then(response => {
                                if (!response.ok) return response.json().then(err => { throw err; });
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    isAdded = true;
                                    window.dispatchEvent(new CustomEvent('cart-updated', { 
                                        detail: { count: data.cart_count } 
                                    }));
                                    setTimeout(() => { isAdded = false; }, 2000);
                                }
                            })
                            .catch(error => {
                                console.error('Add to cart error:', error);
                            })
                            .finally(() => {
                                isAdding = false;
                            })
                        " class="product-card-add-to-cart-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit"
                                    :disabled="isAdding"
                                    class="block w-full text-center rounded bg-pink-600 px-2 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-1 disabled:bg-pink-400 disabled:cursor-not-allowed transition-colors">
                                <template x-if="isAdding">
                                    <div class="flex items-center justify-center">
                                        <svg class="animate-spin h-4 w-4 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Adding...</span>
                                    </div>
                                </template>
                                <template x-if="!isAdding && isAdded">
                                    <div class="flex items-center justify-center">
                                        <x-heroicon-s-check class="w-4 h-4 mr-1" />
                                        <span>Added!</span>
                                    </div>
                                </template>
                                <template x-if="!isAdding && !isAdded">
                                    <span>Add to Cart</span>
                                </template>
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <button type="button" disabled
                        class="block w-full text-center rounded bg-gray-300 px-2 py-2 text-xs sm:text-sm font-semibold text-gray-500 cursor-not-allowed">
                    Out of Stock
                </button>
            @endif
        </div>
    </div>
</div>