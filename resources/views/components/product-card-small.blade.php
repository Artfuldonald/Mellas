{{-- components/product-card-small.blade.php --}}
@props([
    'product',
    'userWishlistProductIds' => []
])

@php
    $name = $product->name ?? 'Product Name';
    $imageUrl = $product->images->first()?->image_url ?? asset('images/placeholder.png');
    $altText = $product->images->first()?->alt ?? $name;
    $price = (float)($product->price ?? 0);
    $compareAtPrice = (float)($product->compare_at_price ?? 0);
    $productUrl = route('products.show', $product->slug ?? $product->id);

    $discountPercentage = 0;
    if ($compareAtPrice > 0 && $compareAtPrice > $price) {
        $discountPercentage = round((($compareAtPrice - $price) / $compareAtPrice) * 100);
    }

    // $_initialIsInWishlist_for_this_small_card is better for Alpine if we were to use it here
    // For direct form submission, this is fine for initial state.
    $isInWishlist = Auth::check() && in_array($product->id, $userWishlistProductIds);
@endphp

<div class="bg-white rounded shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col overflow-hidden border border-gray-100 w-full h-full"> 
       {{-- Image Section --}}
    <div class="relative">
        <a href="{{ $productUrl }}" class="block aspect-[4/3] bg-gray-50 overflow-hidden">
            <img src="{{ $imageUrl }}" alt="{{ $altText }}" class="w-full h-full object-contain transition-transform duration-300 hover:scale-105"> {{-- Removed group-hover here, direct hover on image --}}
        </a>

        @if($discountPercentage > 0)
            <span class="absolute top-1 left-1 bg-pink-600 text-white text-[9px] font-bold px-1 py-0.5 rounded-sm">-{{ $discountPercentage }}%</span>
        @endif

       <div class="absolute top-1 right-1 z-10 flex flex-col space-y-1">
            @auth
            {{-- Wishlist Form - using ajax-wishlist-form for consistency if you implement generic JS --}}
            <form action="{{ $isInWishlist ? route('wishlist.remove', $product->id) : route('wishlist.add', $product->id) }}"
                  method="POST"
                  class="ajax-wishlist-form" {{-- Or your specific small-card-wishlist-form --}}
                  data-product-id="{{ $product->id }}">
                @csrf
                <button type="submit"
                        aria-label="{{ $isInWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
                        title="{{ $isInWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
                        class="p-1.5 bg-white/80 hover:bg-white rounded-full text-gray-500 hover:text-pink-600 shadow-sm transition focus:outline-none focus:ring-1 focus:ring-pink-400 wishlist-button-icon-container">
                    {{-- SVGs for wishlist icon (filled/outline) and spinner like in previous JS example --}}
                    @if($isInWishlist)
                        <svg class="w-3.5 h-3.5 text-pink-500 wishlist-icon-filled wishlist-icon" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                        <svg class="w-3.5 h-3.5 wishlist-icon-outline wishlist-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    @else
                        <svg class="w-3.5 h-3.5 wishlist-icon-outline wishlist-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        <svg class="w-3.5 h-3.5 text-pink-500 wishlist-icon-filled wishlist-icon hidden" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                    @endif
                    <svg class="animate-spin h-3.5 w-3.5 text-pink-500 hidden wishlist-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </form>
            @endauth

            {{-- Add to Cart Icon for simple products, or link for variants --}}
            @if($product->is_active && (($product->quantity ?? 0) > 0 || $product->variants_count > 0))
                @if($product->variants_count > 0)
                    <a href="{{ $productUrl }}"
                       title="View Options"
                       class="p-1.5 bg-white/80 hover:bg-white rounded-full text-gray-500 hover:text-pink-600 shadow-sm transition focus:outline-none focus:ring-1 focus:ring-pink-400">
                        <x-heroicon-o-ellipsis-horizontal-circle class="w-4 h-4" />
                    </a>
                @else
                    {{-- Using 'product-card-add-to-cart-form' to match your existing JS --}}
                    <form action="{{ route('cart.add') }}" method="POST" class="product-card-add-to-cart-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" title="Add to Cart"
                                class="p-1.5 bg-white/80 hover:bg-white rounded-full text-gray-500 hover:text-pink-600 shadow-sm transition focus:outline-none focus:ring-1 focus:ring-pink-400">
                            <span class="cart-action-icon">
                                <x-heroicon-o-shopping-bag class="w-4 h-4" />
                            </span>
                            <span class="cart-action-spinner hidden">
                                <svg class="animate-spin h-4 w-4 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    {{-- Content Section --}}
    <div class="p-2 flex flex-col flex-grow">
        <h3 class="text-xs font-normal text-gray-600 leading-tight mb-1 min-h-[28px] line-clamp-2">
            <a href="{{ $productUrl }}" class="hover:text-pink-600">
                {{ $name }}
            </a>
        </h3>
        <div class="mt-auto"> {{-- Pushes price to bottom --}}
            <p class="text-sm font-semibold text-gray-800">GH₵ {{ number_format($price, 2) }}</p>
            @if($discountPercentage > 0)
                <div class="text-[10px] mt-0">
                    <span class="text-gray-400 line-through">GH₵ {{ number_format($compareAtPrice, 2) }}</span>
                </div>
            @endif
        </div>
    </div>
</div>