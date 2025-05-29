@props([
    'product',
    'userWishlistProductIds' => [] // For dynamic wishlist icon
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

    $isInWishlist = Auth::check() && in_array($product->id, $userWishlistProductIds);

    // For the small card, we might not show detailed stock or ratings to save space
    // but the data is available if you choose to add it.
    // $reviewCount = $product->reviews_count ?? 0;
    // $rating = $product->reviews_avg_rating ?? ($product->rating ?? 0);
@endphp

{{-- Target width roughly 231.5px. Max-width for responsiveness. --}}
{{-- Actual width will be determined by the grid it's placed in. --}}
<div class="bg-white rounded shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col overflow-hidden group border border-gray-100 w-full max-w-[232px]">
    {{-- Image Section --}}
    <div class="relative">
        <a href="{{ $productUrl }}" class="block aspect-[4/3] bg-gray-50 overflow-hidden"> {{-- Common aspect ratio for these small cards --}}
            <img src="{{ $imageUrl }}" alt="{{ $altText }}" class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105">
        </a>

        {{-- Discount Badge (Top-Left) --}}
        @if($discountPercentage > 0)
            <span class="absolute top-1 left-1 bg-pink-600 text-white text-[9px] font-bold px-1 py-0.5 rounded-sm">-{{ $discountPercentage }}%</span>
        @endif

        {{-- Hover Icons: Wishlist & Add to Cart (Small) --}}
        <div class="absolute top-1 right-1 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col space-y-1">
            @auth
            <form action="{{ $isInWishlist ? route('wishlist.remove', $product->id) : route('wishlist.add', $product->id) }}" method="POST" class="add-to-wishlist-form-small">
                @csrf
                <button type="submit"
                        aria-label="{{ $isInWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
                        title="{{ $isInWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
                        class="p-1.5 bg-white/80 hover:bg-white rounded-full text-gray-500 hover:text-pink-600 shadow-sm transition focus:outline-none focus:ring-1 focus:ring-pink-400">
                    @if($isInWishlist)
                        <x-heroicon-s-heart class="w-3.5 h-3.5 text-pink-500" />
                    @else
                        <x-heroicon-o-heart class="w-3.5 h-3.5" />
                    @endif
                </button>
            </form>
            @endauth

            {{-- Add to Cart Icon - Links to PDP for products with variants for simplicity on this small card --}}
            @if($product->is_active && (($product->quantity ?? 0) > 0 || $product->variants_count > 0))
                @if($product->variants_count > 0)
                    <a href="{{ $productUrl }}"
                       title="View Options"
                       class="p-1.5 bg-white/80 hover:bg-white rounded-full text-gray-500 hover:text-pink-600 shadow-sm transition focus:outline-none focus:ring-1 focus:ring-pink-400">
                        <x-heroicon-o-ellipsis-horizontal-circle class="w-4 h-4" />
                    </a>
                @else
                    {{-- Direct Add to Cart for simple products --}}
                    <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form-small">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" title="Add to Cart"
                                class="p-1.5 bg-white/80 hover:bg-white rounded-full text-gray-500 hover:text-pink-600 shadow-sm transition focus:outline-none focus:ring-1 focus:ring-pink-400">
                            <x-heroicon-o-shopping-bag class="w-4 h-4" />
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    {{-- Content Section --}}
    <div class="p-2 flex flex-col flex-grow">
        {{-- Product Name --}}
        <h3 class="text-xs font-normal text-gray-600 leading-tight mb-1 min-h-[28px] line-clamp-2"> {{-- line-clamp for 2 lines --}}
            <a href="{{ $productUrl }}" class="hover:text-pink-600">
                {{ $name }}
            </a>
        </h3>

        {{-- Price --}}
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