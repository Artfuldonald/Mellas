{{-- components/product-card-small.blade.php --}}
@props([
    'product',
    'userWishlistProductIds' => []
])

@php
    $name = $product->name ?? 'Product Name';
    $imageUrl = $product->getFirstMediaUrl('default', 'card_thumbnail') ?? asset('images/placeholder.png');
    $altText = $product->name;
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