{{-- components/product-card.blade.php --}}
@props([
    'product',
    'userWishlistProductIds' => []
])

@php
    use Illuminate\Support\Str;

    // --- Safe Variable Initialization ---
    $name = $product->name ?? 'Product Name';
    $productUrl = $product ? route('products.show', $product->slug ?? $product->id) : '#';
    $imageUrl = $product->images->first()?->image_url ?? asset('images/placeholder.png');
    $altText = $product->images->first()?->alt ?? $name;
    
    $price = (float)($product->price ?? 0);
    $compareAtPrice = (float)($product->compare_at_price ?? 0);

    // --- Eager-loaded Data Handling ---
    $variantsCount = $product->variants_count ?? 0;
    $productQuantity = $product->quantity ?? 0;
    $reviewCount = $product->reviews_count ?? 0;
    $rating = $product->approved_reviews_avg_rating ?? 0;

    // --- Availability Logic ---
    $isGenerallyAvailable = false;
    if ($variantsCount > 0) {
        // A product with variants is generally considered available on the card.
        // We assume at least one variant is in stock. A more complex check could be done here if needed.
        $isGenerallyAvailable = true;
    } else {
        // A simple product is available only if its own quantity is > 0.
        $isGenerallyAvailable = $productQuantity > 0;
    }

    // --- Discount Calculation ---
    $discountPercentage = 0;
    if ($compareAtPrice > 0 && $compareAtPrice > $price) {
        $discountPercentage = round((($compareAtPrice - $price) / $compareAtPrice) * 100);
    }

    // --- Low Stock Text Logic ---
    $lowStockThreshold = 10;
    $itemsLeftText = null;
    if ($isGenerallyAvailable && $variantsCount == 0 && $productQuantity > 0 && $productQuantity <= $lowStockThreshold) {
        $itemsLeftText = $productQuantity . ' ' . Str::plural('item', $productQuantity) . ' left';
    }

    // --- Wishlist State ---
    $_initialIsInWishlist_for_this_card = Auth::check() && $product && in_array($product->id, $userWishlistProductIds);
@endphp

<div class="bg-white rounded-md shadow-sm hover:shadow-lg transition-shadow duration-300 flex flex-col overflow-hidden group border border-pink-100 text-sm">
    {{-- Image Section --}}
    <div class="relative">
        <a href="{{ $productUrl }}" class="block aspect-[4/3] sm:aspect-square bg-gray-50 overflow-hidden">
            <img src="{{ $imageUrl }}" alt="{{ $altText }}" class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105">
        </a>
        @if($discountPercentage > 0)
            <span class="absolute top-1 left-1 bg-pink-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-sm">-{{ $discountPercentage }}%</span>
        @endif
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
            <a href="{{ $productUrl }}" class="hover:text-pink-600">{{ $name }}</a>
        </h3>

        <div class="mb-1">
            <p class="text-base font-semibold text-gray-900 inline-block">GH₵ {{ number_format($price, 2) }}</p>
            @if($discountPercentage > 0)
                <span class="text-[12px] text-gray-500 line-through ml-1.5">GH₵ {{ number_format($compareAtPrice, 2) }}</span>
                <span class="ml-1.5 bg-pink-100 text-pink-700 text-[10px] font-semibold px-1.5 py-0.5 rounded-sm">-{{ $discountPercentage }}%</span>
            @endif
        </div>

        <div class="mb-1.5 min-h-[16px]">
            @if($reviewCount > 0)
                <a href="{{ $productUrl }}#reviews" class="flex items-center text-xs text-gray-500 hover:text-pink-600">
                    <div class="flex">@for ($i = 1; $i <= 5; $i++)<x-heroicon-s-star class="w-3.5 h-3.5 {{ $i <= round($rating) ? 'text-yellow-400' : 'text-gray-300' }}"/>@endfor</div>
                    <span class="ml-1">({{ $reviewCount }})</span>
                </a>
            @endif
        </div>

        @if($itemsLeftText)
            <div class="mb-1.5">
                <p class="text-[10px] text-pink-600 font-medium">{{ $itemsLeftText }}</p>
                <div class="w-full bg-gray-200 rounded-full h-1 mt-0.5 overflow-hidden">
                    <div class="bg-pink-500 h-1 rounded-full" style="width: {{ (($productQuantity ?? 0) / $lowStockThreshold) * 100 }}%"></div>
                </div>
            </div>
        @else
            <div class="h-[18px] mb-1.5"></div>
        @endif

        {{-- CORRECTED Action Button Logic --}}
        <div class="mt-auto pt-1">
            @if($isGenerallyAvailable)
                @if($variantsCount > 0)
                    {{-- For products with variants, this is a simple link to the PDP --}}
                    <a href="{{ $productUrl }}"
                       class="block w-full text-center rounded bg-pink-600 px-2 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 transition-colors">
                        Select Options
                    </a>
                @else
                    {{-- For simple products, use the Alpine cartToggleButton with the new helper function --}}
                    <div x-data="cartToggleButton({
                        productId: {{ $product->id }},
                        initialIsInCart: {{ is_product_in_cart($product) ? 'true' : 'false' }}
                    })">
                        <button @click="toggleCart()"
                                :disabled="isLoading"
                                class="block w-full text-center rounded px-2 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:cursor-not-allowed"
                                :class="{
                                    'bg-pink-600 hover:bg-pink-700 focus:ring-pink-500': !isInCart,
                                    'bg-red-600 hover:bg-red-700 focus:ring-red-500': isInCart,
                                    'bg-gray-400': isLoading
                                }">
                            
                            <div x-show="isLoading" class="flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                            
                            <div x-show="!isLoading && isInCart" class="flex items-center justify-center">
                                <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                <span>Remove</span>
                            </div>

                            <div x-show="!isLoading && !isInCart">
                                <span>Add to Cart</span>
                            </div>
                        </button>
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