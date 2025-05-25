@props([
    'product' // Expects an App\Models\Product instance
])

@php
    // Derive necessary properties from the product object
    $id = $product->id;
    $name = $product->name ?? 'Product Name';
    // Get the first image URL or a placeholder
    $imageUrl = $product->images->first()?->path ? Storage::url($product->images->first()->path) : asset('path/to/your/placeholder.jpg'); // Use placeholder
    $altText = $product->images->first()?->alt ?? $name;
    $price = $product->price ?? 0;
    $compareAtPrice = $product->compare_at_price ?? null;
    $productUrl = route('products.show', $product); // Assumes route model binding works (slug or ID)
    $rating = $product->rating ?? 0; // Assuming you have a rating property/accessor later
    $reviewCount = $product->reviews_count ?? 0; // Assuming you have a review count later

    // Unique modal name
    $modalName = 'product-details-' . $id;
@endphp

{{-- Use the Panel component, applying group for hover effects --}}
<x-panel class="flex flex-col group"> {{-- Added flex flex-col --}}
    <div class="relative aspect-square w-full overflow-hidden bg-gray-100"> {{-- Ensure consistent aspect ratio & background --}}
        <a href="{{ $productUrl }}">
            <img src="{{ $imageUrl }}" alt="{{ $altText }}"
                 class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-300 ease-in-out"> {{-- Zoom effect --}}
        </a>
        {{-- Overlay Actions (Optional - Appear on Hover) --}}
        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity duration-300 flex items-center justify-center space-x-3">
            {{-- Details Button --}}
            <button @click="$dispatch('open-modal', '{{ $modalName }}')"
                    class="p-2 rounded-full bg-white text-gray-600 hover:bg-pink-500 hover:text-white opacity-0 group-hover:opacity-100 transition-all duration-300 ease-in-out scale-90 group-hover:scale-100"
                    title="Quick View">
                <x-heroicon-o-eye class="w-5 h-5"/>
                <span class="sr-only">Product Details</span>
            </button>
            {{-- Wishlist Button --}}
            <button class="p-2 rounded-full bg-white text-gray-600 hover:bg-pink-500 hover:text-white opacity-0 group-hover:opacity-100 transition-all duration-300 ease-in-out delay-75 scale-90 group-hover:scale-100"
                    title="Add to Wishlist">
                <x-heroicon-o-heart class="w-5 h-5"/>
                <span class="sr-only">Add to Wishlist</span>
            </button>
             {{-- Add to Cart Button --}}
             {{-- You might want a dedicated cart component here eventually --}}
             <button class="p-2 rounded-full bg-white text-gray-600 hover:bg-pink-500 hover:text-white opacity-0 group-hover:opacity-100 transition-all duration-300 ease-in-out delay-150 scale-90 group-hover:scale-100"
                    title="Add to Cart">
                <x-heroicon-o-shopping-bag class="w-5 h-5"/>
                <span class="sr-only">Add to Cart</span>
             </button>
        </div>
    </div>
    {{-- Product Info Area --}}
    <div class="p-4 flex-1 flex flex-col justify-between"> {{-- Allow info to take remaining space --}}
        <div>
            {{-- Optional Category Link --}}
            {{-- <a href="#" class="text-xs text-gray-500 hover:text-pink-600">Category</a> --}}
            {{-- Product Name --}}
            <h3 class="mt-1 text-sm font-medium text-gray-800 group-hover:text-pink-700 transition-colors duration-200">
                <a href="{{ $productUrl }}">
                    <span aria-hidden="true" class="absolute inset-0"></span> {{-- Invisible link overlay --}}
                    {{ $name }}
                </a>
            </h3>
             {{-- Rating Display (Optional) --}}
            @if($reviewCount > 0)
             <div class="flex items-center mt-1">
                 <div class="flex text-yellow-400 star-rating"> {{-- Add star-rating class --}}
                     @for ($i = 1; $i <= 5; $i++)
                         @if ($i <= round($rating)) <x-heroicon-s-star class="w-4 h-4"/> {{-- Use solid star --}}
                         @else <x-heroicon-o-star class="w-4 h-4"/> {{-- Use outline star --}}
                         @endif
                     @endfor
                 </div>
                 <span class="text-gray-500 text-xs ml-1">({{ $reviewCount }})</span>
             </div>
            @endif
        </div>
         {{-- Price --}}
         <div class="mt-3 flex items-baseline">
            <p class="text-base font-semibold text-gray-900">${{ number_format($price, 2) }}</p>
            @if($compareAtPrice && $compareAtPrice > $price)
                <p class="text-sm text-gray-500 line-through ml-2">${{ number_format($compareAtPrice, 2) }}</p>
            @endif
        </div>
    </div>

    {{-- Modal (Structure remains largely the same, but ensure it uses x-app-layout potentially or style directly) --}}
    <x-modal :name="$modalName" :show="false">
        <div class="p-6 bg-white"> {{-- Ensure modal has its own background --}}
            {{-- Close button for accessibility inside modal --}}
            <div class="flex justify-end">
                <button type="button" x-on:click="$dispatch('close')"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <x-heroicon-o-x-mark class="w-6 h-6"/>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>

            {{-- Modal Content --}}
            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                {{-- Image --}}
                <div class="aspect-square w-full bg-gray-100 rounded-lg overflow-hidden">
                     <img src="{{ $imageUrl }}" alt="{{ $altText }}" class="h-full w-full object-cover object-center">
                </div>
                {{-- Details --}}
                <div class="flex flex-col h-full">
                     <h2 class="text-2xl font-semibold text-gray-900 mb-2">
                         {{ $name }}
                     </h2>
                     {{-- Rating --}}
                     @if($reviewCount > 0)
                     <div class="flex items-center mb-3">
                         <div class="flex text-yellow-400 star-rating">
                             @for ($i = 1; $i <= 5; $i++)
                                 @if ($i <= round($rating)) <x-heroicon-s-star class="w-4 h-4"/>
                                 @else <x-heroicon-o-star class="w-4 h-4"/>
                                 @endif
                             @endfor
                         </div>
                         <span class="text-gray-500 text-xs ml-2">({{ $reviewCount }} reviews)</span> {{-- Use reviewCount --}}
                     </div>
                     @endif
                     {{-- Price --}}
                     <div class="mb-4 flex items-baseline">
                        <span class="text-pink-600 font-bold text-2xl">${{ number_format($price, 2) }}</span>
                         @if($compareAtPrice && $compareAtPrice > $price)
                            <span class="text-base text-gray-500 line-through ml-3">${{ number_format($compareAtPrice, 2) }}</span>
                        @endif
                     </div>
                     {{-- Description --}}
                     <div class="text-sm text-gray-600 mb-4 flex-grow prose prose-sm max-w-none">
                         {{-- Use actual product description if available --}}
                         <p>{{ $product->description ?: 'Detailed description coming soon. Check features and specifications.' }}</p>
                         {{-- Maybe add specifications list here --}}
                     </div>
                     {{-- Action Buttons --}}
                     <div class="mt-auto pt-4 border-t">
                         <x-primary-button class="w-full justify-center !bg-pink-600 hover:!bg-pink-700 focus:!ring-pink-500">
                             <x-heroicon-o-shopping-bag class="w-5 h-5 mr-2"/>
                             Add to Cart
                         </x-primary-button>
                     </div>
                </div>
            </div>
            {{-- Original close button removed, handled by X icon above --}}
        </div>
    </x-modal>

</x-panel>