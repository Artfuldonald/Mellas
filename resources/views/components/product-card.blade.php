<!-- resources/views/components/product-card.blade.php -->
@props([
    'product', // Assume you pass the whole product object or at least its ID
    // Other props like name, image, price etc. can be derived from $product
    // or passed individually as before. Let's assume $product->id exists.
    'name' => $product->name ?? 'Product Name',
    'image' => $product->image_url ?? '/placeholder.svg?height=300&width=300',
    'price' => $product->price ?? 99.99,
    'rating' => $product->rating ?? 4.0,
    'reviewCount' => $product->reviews_count ?? 0,
])

@php
    // Generate a unique name for this specific product's modal
    $modalName = 'product-details-' . $product->id;
@endphp

{{-- Remove x-data for the modal here, it's handled by the modal component itself now --}}
<x-panel>
    <div class="relative">
        <img src="{{ $image }}" alt="{{ $name }}"
            class="w-full h-64 object-cover">
    </div>
    <div class="p-4">
        {{-- ... (Product Name, Rating display - keep the Font Awesome class approach for stars if you like) ... --}}
        <h3 class="text-lg font-semibold mb-2 group-hover:text-pink-500 transition-colors duration-300">{{ $name }}</h3>
         <div class="flex items-center mb-2">
             <div class="flex text-yellow-400">
                 @for ($i = 1; $i <= 5; $i++)
                     @if ($i <= floor($rating)) <i class="fas fa-star w-4 h-4"></i>
                     @elseif ($i - 0.5 <= $rating) <i class="fas fa-star-half-alt w-4 h-4"></i>
                     @else <i class="far fa-star w-4 h-4"></i>
                     @endif
                 @endfor
             </div>
             <span class="text-gray-500 text-sm ml-2">({{ $reviewCount }} reviews)</span>
         </div>

        <div class="flex justify-between items-center">
            <span class="text-pink-500 font-bold">${{ number_format($price, 2) }}</span>
            <div class="flex space-x-2 items-center">

                <!--- Product details icon - triggers modal using $dispatch -->
                <button @click="$dispatch('open-modal', '{{ $modalName }}')" class="text-gray-500 hover:text-pink-500 transition-colors duration-200">
                   <x-heroicon-o-information-circle class="w-5 h-5" />
                   <span class="sr-only">Product Details</span>
                </button>

                <!--- Wishlist icon -->
                <button class="text-gray-500 hover:text-red-500 transition-colors duration-200">
                   <x-heroicon-o-heart class="w-5 h-5" />
                   <span class="sr-only">Add to Wishlist</span>
                </button>

                <x-cart-button></x-cart-button>
            </div>
        </div>
    </div>

    <!-- MODAL using name and event listening (standard approach) -->
    {{-- Removed x-data from parent, using unique name now --}}
    <x-modal :name="$modalName" :show="false" @close.stop=""> {{-- Pass the unique name, initial :show is PHP false --}}
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $name }} Details
            </h2>

            {{-- ... (Modal content: image, description, price, rating etc.) ... --}}
             <div class="mt-4">
                 <div class="mb-4">
                     <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-auto max-h-80 object-contain rounded">
                 </div>
                 <p class="text-sm text-gray-600 dark:text-gray-400">
                     Here you can add a detailed description for {{ $name }}. Include features, benefits, and specifications.
                 </p>
                 <div class="mt-4 flex justify-between items-center">
                      <span class="text-pink-500 font-bold text-xl">${{ number_format($price, 2) }}</span>
                      <div class="flex items-center">
                          <div class="flex text-yellow-400">
                              @for ($i = 1; $i <= 5; $i++)
                                  @if ($i <= floor($rating)) <i class="fas fa-star w-4 h-4"></i>
                                  @elseif ($i - 0.5 <= $rating) <i class="fas fa-star-half-alt w-4 h-4"></i>
                                  @else <i class="far fa-star w-4 h-4"></i>
                                  @endif
                              @endfor
                          </div>
                         <span class="text-gray-500 text-sm ml-2">({{ $reviewCount }} reviews)</span>
                     </div>
                 </div>
             </div>


            <div class="mt-6 flex justify-end">
                {{-- The modal component usually handles its own close button internally --}}
                {{-- Or you could dispatch: @click="$dispatch('close-modal', '{{ $modalName }}')" --}}
                <button type="button" x-on:click="$dispatch('close')" {{-- Use the modal's internal close mechanism --}}
                    class="bg-pink-500 text-white px-4 py-2 rounded-md hover:bg-pink-600 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

</x-panel>