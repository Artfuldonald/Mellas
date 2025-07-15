<x-app-layout :title="$productData['name'] . ' | Mella\'s Connect'">

    {{-- Breadcrumbs Section --}}
    <div class="bg-gray-100 py-3 border-b border-gray-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-xs text-gray-500" aria-label="Breadcrumb">           
                <ol class="list-none p-0 flex flex-wrap leading-relaxed items-center">                
                    <li class="flex items-center mr-1.5 mb-1">
                        <a href="{{ route('home') }}" class="hover:text-pink-600">Home</a>
                        <x-heroicon-s-chevron-right class="w-3 h-3 ml-1.5 text-gray-400"/>
                    </li>
                    <li class="flex items-center mr-1.5 mb-1">
                        <a href="{{ route('products.index') }}" class="hover:text-pink-600">All Products</a>
                    </li>
                    @if(!empty($breadcrumbs))
                        @foreach($breadcrumbs as $breadcrumb)
                            <li class="flex items-center mr-1.5 mb-1">
                                <x-heroicon-s-chevron-right class="w-3 h-3 mr-1.5 text-gray-400"/>
                                <a href="{{ route('products.index', ['category' => $breadcrumb->slug]) }}" class="hover:text-pink-600">
                                    {{ $breadcrumb->name }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                    <li class="flex items-center mr-1.5 mb-1">
                        <x-heroicon-s-chevron-right class="w-3 h-3 mr-1.5 text-gray-400"/>
                        <span class="text-gray-700 font-medium" aria-current="page">{{ Str::limit($productData['name'], 30) }}</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- This main div now controls the Alpine component for the whole page --}}
    <div x-data="productSelector({ product: {{ Js::from($productData) }} })">
    
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-5xl">
            
            <!-- SECTION 1: Main Product Details (Image vs Info) -->
            <section class="mb-12">
                @include('products.partials.product-details', [
                    'product' => $productData,
                    'userWishlistProductIds' => $userWishlistProductIds
                ])
            </section>
            
            <hr class="my-12 border-gray-200">

            <!-- SECTION 2: Description, Specifications, and Reviews in Tabs -->
            <section class="my-12">
                <div class="max-w-4xl mx-auto">
                    <div x-data="{ activeTab: 'description' }" class="w-full">
                        {{-- Tab Headers --}}
                        <div class="flex border-b border-gray-200">
                            <button @click="activeTab = 'description'" :class="activeTab === 'description' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-2 px-4 border-b-2 font-medium text-sm transition-colors focus:outline-none">
                                Description
                            </button>
                            <button @click="activeTab = 'specifications'" x-show="product.specifications && Object.keys(product.specifications).length > 0" :class="activeTab === 'specifications' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-2 px-4 border-b-2 font-medium text-sm transition-colors focus:outline-none" style="display: none;">
                                Specifications
                            </button>
                             <button @click="activeTab = 'reviews'" :class="activeTab === 'reviews' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-2 px-4 border-b-2 font-medium text-sm transition-colors focus:outline-none">
                                Reviews ({{ $productData['review_count'] }})
                            </button>
                        </div>

                        {{-- Tab Content --}}
                        <div class="mt-6 text-sm text-gray-600 leading-relaxed">
                            <div x-show="activeTab === 'description'" x-cloak class="prose max-w-none">
                                <div x-html="product.description || '<p>No description available.</p>'"></div>
                            </div>
                            <div x-show="activeTab === 'specifications'" x-cloak style="display: none;">
                                 <dl class="divide-y divide-gray-200">
                                    <template x-for="spec in (Array.isArray(product.specifications) ? product.specifications : Object.entries(product.specifications).map(([key, value]) => ({key, value})))" :key="spec.key">
                                        <div class="py-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <dt class="font-medium text-gray-800" x-text="spec.key"></dt>
                                            <dd class="md:col-span-2" x-text="spec.value"></dd>
                                        </div>
                                    </template>
                                </dl>
                            </div>
                             <div x-show="activeTab === 'reviews'" x-cloak style="display: none;">
                                @include('products.partials.product-reviews', [
                                    'product' => $productData,
                                    'reviews' => $reviews,
                                    'ratingDistribution' => $ratingDistribution
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <hr class="my-12 border-gray-200">
            
            <!-- SECTION 3: Related Products -->
            <section>
                @include('products.partials.related-products', [
                    'relatedProducts' => $relatedProducts,
                    'userWishlistProductIds' => $userWishlistProductIds
                ])
            </section>
        </div>

        {{-- The Confirmation Modal --}}
        <div x-show="isModalOpen" x-transition class="fixed z-50 inset-0 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="isModalOpen" x-transition.opacity @click.away="isModalOpen = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div x-show="isModalOpen" x-transition class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 z-10">
                    <h3 class="text-lg font-medium text-gray-900">Confirm Your Selection</h3>
                    <div class="mt-4 bg-gray-50 rounded-lg p-4 space-y-1 text-sm">
                        <template x-for="(value, key) in selectedOptions" :key="key"><div x-show="value" class="flex justify-between"><span class="capitalize text-gray-600" x-text="key + ':'"></span><span class="font-medium" x-text="value"></span></div></template>
                        <div class="flex justify-between"><span class="text-gray-600">Quantity:</span><span class="font-medium" x-text="quantity"></span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Available:</span><span class="font-medium text-green-600" x-text="currentStock + ' in stock'"></span></div>
                        <div class="flex justify-between font-bold text-lg pt-2 border-t mt-2"><span class="text-gray-900">Total:</span><span class="text-pink-600" x-text="formatCurrency(currentPrice * quantity)"></span></div>
                    </div>
                    <div class="mt-5 flex justify-end gap-3">
                        <button @click="isModalOpen = false" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">Cancel</button>
                        <button @click="handleAddToCart()" :disabled="isLoading" class="px-4 py-2 bg-pink-600 text-white rounded-md text-sm font-medium hover:bg-pink-700 disabled:opacity-50">
                            <span x-show="!isLoading">Add to Cart</span><span x-show="isLoading">Adding...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>