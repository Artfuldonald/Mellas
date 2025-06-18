<x-app-layout :title="$productData['name'] . ' | ' . $productData['brand']">

    {{-- Breadcrumbs using the transformed array --}}
    <div class="bg-gray-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex space-x-2 items-center">
                    <li class="flex items-center">
                        <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-blue-600">Products</a>
                        <svg class="w-4 h-4 mx-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </li>
                    <li class="flex items-center">
                        <a href="{{ route('products.index', ['category' => $productData['category']['slug'] ?? '']) }}" class="text-gray-500 hover:text-blue-600">
                            {{ $productData['category']['name'] ?? 'Category' }}
                        </a>
                        <svg class="w-4 h-4 mx-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </li>
                    <li class="flex items-center">
                        <span class="text-blue-700 font-medium" aria-current="page">
                            {{ Str::limit($productData['name'], 40) }}
                        </span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="bg-white">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <!-- Main Product Section -->
            <section class="mb-12">
                @include('products.partials.product-details', ['product' => $productData])
            </section>
            
            <hr class="my-12 border-gray-200">
            
            <!-- Product Reviews Section -->
            <section id="reviews" class="mb-12">
                @include('products.partials.product-reviews', [
                    'product' => $productData,
                    'reviews' => $reviews,
                    'ratingDistribution' => $ratingDistribution
                ])
            </section>
            
            <hr class="my-12 border-gray-200">
            
            <!-- Related Products Section -->
            <section>
                @include('products.partials.related-products', [
                    'relatedProducts' => $relatedProducts,
                    'userWishlistProductIds' => $userWishlistProductIds
                ])
            </section>
        </div>
    </div>
</x-app-layout>
