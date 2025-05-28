{{-- resources/views/wishlist/index.blade.php --}}
<x-app-layout title="My Wishlist">
    <div class="bg-pink-50 py-8 border-b border-pink-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-pink-800">My Wishlist</h1>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if (session('success'))
            <div class="mb-6 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('info'))
            <div class="mb-6 p-4 text-sm text-blue-700 bg-blue-100 rounded-lg" role="alert">
                {{ session('info') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if($wishlistItems->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($wishlistItems as $item)
                    {{-- Pass an empty array for userWishlistProductIds or true since we know it's in wishlist here --}}
                    {{-- Or, modify product card to accept a simple $isInWishlist boolean --}}
                    <div class="relative"> {{-- Wrapper to position remove button easily over the card --}}
                        <x-product-card :product="$item->product" :userWishlistProductIds="[$item->product_id]" /> {{-- Indicate this item is in wishlist --}}
                        <form action="{{ route('wishlist.remove', $item->product_id) }}" method="POST" class="absolute top-3 right-3 z-10">
                             @csrf
                             <button type="submit" title="Remove from wishlist" class="p-2 bg-red-500 text-white rounded-full hover:bg-red-600 shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                                 <x-heroicon-o-x-mark class="w-5 h-5" />
                             </button>
                         </form>
                    </div>
                @endforeach
            </div>

            @if ($wishlistItems->hasPages())
                <div class="mt-12">
                    {{ $wishlistItems->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-16 bg-white rounded-lg shadow-xl">
                <x-heroicon-o-heart class="mx-auto h-16 w-16 text-pink-400"/>
                <h3 class="mt-4 text-xl font-semibold text-gray-900">Your Wishlist is Empty</h3>
                <p class="mt-2 text-base text-gray-500">Looks like you haven't added any of your favorite items yet. <br>Start exploring and add products you love!</p>
                <div class="mt-8">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-base font-medium rounded-lg text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                        <x-heroicon-s-shopping-bag class="-ml-1 mr-2 h-5 w-5" />
                        Continue Shopping
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>