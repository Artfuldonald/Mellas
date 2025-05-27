@props([
    'product' // Expects an App\Models\Product instance
])

@php
    $name = $product->name ?? 'Product Name';
    $imageUrl = $product->images->first()?->image_url ?? asset('images/placeholder.png'); // Assumes image_url accessor
    $altText = $product->images->first()?->alt ?? $name;
    $price = (float)($product->price ?? 0);
    $compareAtPrice = (float)($product->compare_at_price ?? 0);
    $productUrl = route('products.show', $product->slug ?? $product->id);

    $reviewCount = $product->reviews_count ?? 0; // Assumes 'reviews_count' loaded by withCount('reviews')
    $rating = $product->reviews_avg_rating ?? ($product->rating ?? 0); // Assumes 'reviews_avg_rating' or 'rating'

    // Stock and Availability
    // This is a simplified stock check. Adapt if you have variants with individual stock.
    $isAvailable = ($product->quantity ?? 0) > 0 || ($product->variants_count > 0); // If variants exist, assume PDP handles stock
    if ($product->variants_count == 0 && property_exists($product, 'quantity')) {
        $isAvailable = $product->quantity > 0;
    }
    $currentStock = $product->quantity ?? 0; // For simple products

    $discountPercentage = 0;
    if ($compareAtPrice > 0 && $compareAtPrice > $price) {
        $discountPercentage = round((($compareAtPrice - $price) / $compareAtPrice) * 100);
    }

    // "Items Left" Logic (Example)
    $lowStockThreshold = 10; // Show "items left" if stock is this or less
    $itemsLeftText = null;
    $stockPercentage = null;
    if ($isAvailable && $product->variants_count == 0 && $currentStock <= $lowStockThreshold && $currentStock > 0) {
        $itemsLeftText = $currentStock . ' ' . Str::plural('item', $currentStock) . ' left';
        // You could also calculate a percentage for a progress bar if you have an "initial stock" concept
        // For simplicity, we'll just show text. For a bar:
        // $stockPercentage = ($currentStock / $lowStockThreshold) * 100;
    }

    // Placeholder for special badges (like "Pay on Delivery" or "Express Shipping")
    $showPayOnDeliveryBadge = false; // Set to true based on product data if needed
    $showExpressShippingBadge = false; // Set to true based on product data if needed

@endphp

<div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 flex flex-col overflow-hidden group">
    {{-- Image Section --}}
    <div class="relative">
        <a href="{{ $productUrl }}" class="block aspect-w-1 aspect-h-1 bg-pink-50"> {{-- aspect-w-1 aspect-h-1 for consistent image box --}}
            <img src="{{ $imageUrl }}" alt="{{ $altText }}" class="w-full h-full object-contain sm:object-cover transition-transform duration-300 group-hover:scale-105">
            {{-- Use object-contain if images vary a lot, object-cover if they are more uniform --}}
        </a>

        {{-- Badges on Image --}}
        @if($showPayOnDeliveryBadge)
            <span class="absolute top-2 left-2 bg-green-100 text-green-700 text-[10px] font-semibold px-1.5 py-0.5 rounded-sm shadow">Pay on Delivery</span>
        @endif

        {{-- Wishlist Icon --}}
        <button aria-label="Add to wishlist" class="absolute top-2 right-2 p-1.5 bg-white/80 hover:bg-white rounded-full text-pink-500 hover:text-pink-600 shadow-sm hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-pink-500">
            <x-heroicon-o-heart class="w-5 h-5" />
        </button>
    </div>

    {{-- Content Section --}}
    <div class="p-3 sm:p-4 flex flex-col flex-grow">
        @if($showExpressShippingBadge)
            {{-- For pink theme, Jumia's orange might be changed to pink --}}
            <span class="text-xs font-bold text-pink-600 mb-1 inline-block">EXPRESS SHIPPING</span>
        @endif

        <h3 class="text-sm font-medium text-gray-700 group-hover:text-pink-700 leading-snug mb-1.5 min-h-[40px]"> {{-- min-height for 2 lines --}}
            <a href="{{ $productUrl }}">
                {{ Str::limit($name, 55) }} {{-- Adjust limit as needed for typical name length --}}
            </a>
        </h3>

        {{-- Price --}}
        <div class="mb-2">
            <p class="text-lg font-bold text-gray-900">GH₵ {{ number_format($price, 2) }}</p>
            @if($discountPercentage > 0)
                <div class="flex items-center text-xs mt-0.5">
                    <span class="text-gray-500 line-through">GH₵ {{ number_format($compareAtPrice, 2) }}</span>
                    <span class="ml-2 bg-pink-100 text-pink-700 font-semibold px-1.5 py-0.5 rounded-sm">-{{ $discountPercentage }}%</span>
                </div>
            @endif
        </div>

        {{-- Rating --}}
        @if($reviewCount > 0)
            <div class="flex items-center text-xs text-gray-500 mb-2">
                <div class="flex">
                    @for ($i = 1; $i <= 5; $i++)
                        <x-heroicon-s-star class="w-3.5 h-3.5 {{ $i <= round($rating) ? 'text-yellow-400' : 'text-gray-300' }}"/>
                    @endfor
                </div>
                <span class="ml-1.5">({{ $reviewCount }})</span>
            </div>
        @else
            <div class="h-[18px] mb-2"></div> {{-- Placeholder for consistent height if no reviews --}}
        @endif

        {{-- Stock Information (Text only) --}}
        @if($itemsLeftText)
            <div class="mb-2">
                <p class="text-xs text-red-600 font-medium">{{ $itemsLeftText }}</p>
                {{-- Progress bar can be added here if $stockPercentage is calculated --}}
                {{-- <div class="w-full bg-gray-200 rounded-full h-1 mt-0.5">
                    <div class="bg-red-500 h-1 rounded-full" style="width: {{ $stockPercentage ?? 0 }}%"></div>
                </div> --}}
            </div>
        @elseif($isAvailable && $product->variants_count == 0 && $currentStock > $lowStockThreshold)
            <div class="h-[18px] mb-2"></div> {{-- Placeholder if in stock but not low --}}
        @else
            <div class="h-[18px] mb-2"></div> {{-- Placeholder if no specific stock info to show --}}
        @endif


        {{-- Action Button --}}
        <div class="mt-auto"> {{-- Pushes button to the bottom --}}
            @if($isAvailable)
                <a href="{{ $productUrl }}"
                   class="block w-full text-center rounded-md bg-pink-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 transition-colors duration-150 ease-in-out">
                    {{ $product->variants_count > 0 ? 'View Options' : 'Add to Cart' }}
                </a>
            @else
                <button type="button" disabled
                        class="block w-full text-center rounded-md bg-gray-300 px-3 py-2.5 text-sm font-semibold text-gray-500 cursor-not-allowed">
                    Sold Out
                </button>
            @endif
        </div>
    </div>
</div>