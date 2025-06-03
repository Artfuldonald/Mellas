<x-app-layout :title="$brand->name . ' Products'">
    <div class="bg-pink-50 py-8 border-b border-pink-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Optional Breadcrumbs for brand page --}}
            <nav class="text-sm mb-2" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex space-x-2 items-center">
                    <li class="flex items-center"><a href="{{ route('home') }}" class="text-gray-500 hover:text-pink-600">Home</a><x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/></li>
                    <li class="flex items-center"><a href="{{ route('brands.index') }}" class="text-gray-500 hover:text-pink-600">Brands</a><x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/></li>
                    <li class="flex items-center"><span class="text-pink-700 font-medium">{{ $brand->name }}</span></li>
                </ol>
            </nav>
            <div class="md:flex md:items-center md:space-x-4">
                @if($brand->logo_url)
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }} Logo" class="h-16 w-auto mb-4 md:mb-0 rounded-sm object-contain">
                @endif
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-pink-800">{{ $brand->name }}</h1>
                    @if($brand->description)
                        <p class="mt-1 text-sm text-gray-600 max-w-2xl">{{ $brand->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($products->isNotEmpty())
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Products from {{ $brand->name }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <x-product-card :product="$product" :userWishlistProductIds="$userWishlistProductIds" />
                @endforeach
            </div>
            @if ($products->hasPages())
                <div class="mt-12">
                    {{ $products->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-16 bg-white rounded-lg shadow-xl">
                 @if($brand->logo_url)
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }} Logo" class="h-20 w-auto mb-4 md:mb-0 rounded-sm object-contain mx-auto">
                @else
                    <x-heroicon-o-tag class="mx-auto h-16 w-16 text-pink-400"/>
                @endif
                <h3 class="mt-4 text-xl font-semibold text-gray-900">No Products Yet</h3>
                <p class="mt-2 text-base text-gray-500">There are currently no products listed for {{ $brand->name }}.<br>Check back soon!</p>
                <div class="mt-8">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-base font-medium rounded-lg text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                        <x-heroicon-s-shopping-bag class="-ml-1 mr-2 h-5 w-5" />
                        Explore Other Products
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>