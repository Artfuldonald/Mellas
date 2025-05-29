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

    // Use the aliases from your controller's withCount/withAvg
    $reviewCount = $product->reviews_count ?? 0;
    $rating = $product->reviews_avg_rating ?? 0;

    $isAvailable = ($product->quantity ?? 0) > 0 || ($product->variants_count > 0);
    if ($product->variants_count == 0 && property_exists($product, 'quantity')) {
        $isAvailable = $product->quantity > 0;
    }
    $currentStock = $product->quantity ?? 0;

    $discountPercentage = 0;
    if ($compareAtPrice > 0 && $compareAtPrice > $price) {
        $discountPercentage = round((($compareAtPrice - $price) / $compareAtPrice) * 100);
    }

    $lowStockThreshold = 10;
    $itemsLeftText = null;
    $stockBarPercentage = null;

    if ($isAvailable && $product->variants_count == 0 && $currentStock > 0) {
        if ($currentStock <= $lowStockThreshold) {
            $itemsLeftText = $currentStock . ' ' . Str::plural('item', $currentStock) . ' left';
            $stockBarPercentage = ($currentStock / $lowStockThreshold) * 100;
            if ($stockBarPercentage < 10) $stockBarPercentage = 10;
        }
    }
    $isInWishlist = Auth::check() && in_array($product->id, $userWishlistProductIds);
@endphp

{{-- This card is for the main /products listing page, styled like Jumia's grid items --}}
<div class="bg-white rounded shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col overflow-hidden group border border-gray-200 h-full"> {{-- h-full for consistent height in grid --}}
    {{-- Image Section --}}
    <div class="relative">
        <a href="{{ $productUrl }}" class="block aspect-[4/3] bg-gray-100 overflow-hidden">
            <img src="{{ $imageUrl }}" alt="{{ $altText }}" class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105">
        </a>

        @if($discountPercentage > 0)
            <span class="absolute top-1 left-1 bg-pink-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-sm">-{{ $discountPercentage }}%</span>
        @endif

        @auth
        <form action="{{ $isInWishlist ? route('wishlist.remove', $product->id) : route('wishlist.add', $product->id) }}" method="POST" class="absolute top-1.5 right-1.5 z-10 product-card-wishlist-form">
            @csrf
            <button type="submit"
                    title="{{ $isInWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
                    class="p-1.5 bg-white/80 hover:bg-white rounded-full text-gray-500 hover:text-pink-600 shadow-sm transition focus:outline-none focus:ring-1 focus:ring-pink-400">
                @if($isInWishlist)
                    <x-heroicon-s-heart class="w-4 h-4 text-pink-500" />
                @else
                    <x-heroicon-o-heart class="w-4 h-4" />
                @endif
            </button>
        </form>
        @endauth
    </div>

    {{-- Content Section --}}
    <div class="p-2.5 flex flex-col flex-grow">
        <h3 class="text-xs font-normal text-gray-700 leading-tight mb-1 min-h-[32px] line-clamp-2">
            <a href="{{ $productUrl }}" class="hover:text-pink-600">
                {{ $name }}
            </a>
        </h3>

        <div class="mb-1.5">
            <p class="text-base font-semibold text-gray-900">GH₵ {{ number_format($price, 2) }}</p>
            @if($discountPercentage > 0)
                <div class="text-[11px] mt-0 flex items-center">
                    <span class="text-gray-500 line-through">GH₵ {{ number_format($compareAtPrice, 2) }}</span>
                    {{-- Discount percentage is shown on image badge now --}}
                </div>
            @endif
        </div>

        <div class="text-xs text-gray-500 mb-1.5 min-h-[16px]">
            @if($reviewCount > 0)
                <div class="flex items-center">
                    <div class="flex">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star class="w-3 h-3 {{ $i <= round($rating) ? 'text-yellow-400' : 'text-gray-300' }}"/>
                        @endfor
                    </div>
                    <span class="ml-1">({{ $reviewCount }})</span>
                </div>
            @endif
        </div>

        @if($itemsLeftText)
            <div class="mb-1.5">
                <p class="text-[10px] text-orange-600 font-medium">{{ $itemsLeftText }}</p>
                @if($stockBarPercentage !== null)
                <div class="w-full bg-gray-200 rounded-full h-1 mt-0.5 overflow-hidden">
                    <div class="bg-orange-500 h-1 rounded-full" style="width: {{ $stockBarPercentage }}%"></div>
                </div>
                @endif
            </div>
        @else
            <div class="h-[18px] mb-1.5"></div> {{-- Maintain space if no stock info --}}
        @endif

        {{-- Optional: Jumia Express Badge --}}
        {{-- @if($product->is_express_eligible)
            <img src="{{ asset('images/jumia-express-badge.svg') }}" alt="Jumia Express" class="h-3 mb-1.5">
        @endif --}}

        <div class="mt-auto pt-1"> {{-- Pushes button to bottom --}}
            @if($isAvailable)
                 {{-- On listing, "Add to cart" for simple products, "View Options" for variants --}}
                 @if($product->variants_count > 0)
                    <a href="{{ $productUrl }}"
                       class="block w-full text-center rounded bg-pink-600 px-2 py-2 text-xs font-semibold text-white shadow-sm hover:bg-pink-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 transition-colors">
                        View Options
                    </a>
                 @else
                    <form action="{{ route('cart.add') }}" method="POST" class="product-card-add-to-cart-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit"
                                class="block w-full text-center rounded bg-pink-600 px-2 py-2 text-xs font-semibold text-white shadow-sm hover:bg-pink-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 transition-colors">
                            Add to Cart
                        </button>
                    </form>
                 @endif
            @else
                <button type="button" disabled
                        class="block w-full text-center rounded bg-gray-300 px-2 py-2 text-xs font-semibold text-gray-500 cursor-not-allowed">
                    Out of Stock
                </button>
            @endif
        </div>
    </div>
</div>

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.product-card-add-to-cart-form');
    forms.forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const button = this.querySelector('button[type="submit"]');
            const originalButtonText = button.innerHTML;
            button.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg> Adding...`;
            button.disabled = true;

            const formData = new FormData(this);
            const plainFormData = Object.fromEntries(formData.entries());

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(plainFormData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // alert(data.message); // Or a toast notification
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
                    // Optionally show a success state on the button briefly
                    button.innerHTML = 'Added!';
                    setTimeout(() => {
                        button.innerHTML = originalButtonText;
                        button.disabled = false;
                    }, 2000);
                } else {
                    alert(data.message || 'Could not add to cart.');
                    button.innerHTML = originalButtonText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
                button.innerHTML = originalButtonText;
                button.disabled = false;
            });
        });
    });
});
</script>
@endPushOnce