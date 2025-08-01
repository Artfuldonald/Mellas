<x-app-layout :title="$productData['name'] . ' | Mella\'s Connect'">

    {{-- Breadcrumbs Section --}}
    <div class="bg-gray-100 py-3 border-b border-gray-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <x-breadcrumbs :items="$breadcrumbs" />
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

           <!-- SECTION 2: Description, Specifications, and Reviews -->
            <section class="my-12">
                <div class="max-w-4xl mx-auto space-y-12"> {{-- Added space-y-12 for spacing --}}
                    
                    {{-- Description Section --}}
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Description</h2>
                        <div class="prose max-w-none text-sm text-gray-600 leading-relaxed">
                            <div x-html="product.description || '<p>No description available.</p>'"></div>
                        </div>
                    </div>

                    {{-- Specifications Section --}}
                    <div x-show="product.specifications && Object.keys(product.specifications).length > 0" x-cloak>
                        <h2 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Specifications</h2>
                        <dl class="divide-y divide-gray-200 text-sm">
                            <template x-for="spec in (Array.isArray(product.specifications) ? product.specifications : Object.entries(product.specifications).map(([key, value]) => ({key, value})))" :key="spec.key">
                                <div class="py-3 grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <dt class="font-medium text-gray-800" x-text="spec.key"></dt>
                                    <dd class="sm:col-span-2 text-gray-600" x-text="spec.value"></dd>
                                </div>
                            </template>
                        </dl>
                    </div>

                    {{-- Reviews Section --}}
                    <div id="reviews">
                        <h2 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Customer Reviews ({{ $productData['review_count'] }})</h2>
                        @include('products.partials.product-reviews', [
                            'product' => $productData,
                            'reviews' => $reviews,
                            'ratingDistribution' => $ratingDistribution
                        ])
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