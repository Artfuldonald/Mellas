<div x-data="productDetails({{ json_encode($productData) }})" class="grid lg:grid-cols-2 gap-6 lg:gap-8">
    <!-- Image Gallery -->
    <div class="space-y-3">
        <div class="aspect-square relative overflow-hidden rounded-lg border border-gray-200 max-w-sm mx-auto">
            <img :src="product.images[selectedImage]" :alt="product.name" class="w-full h-full object-cover">
            @if($productData['discount'] ?? false)
                <span class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-medium">
                    -{{ $productData['discount'] }}%
                </span>
            @endif
        </div>

        <div class="grid grid-cols-5 gap-2 max-w-sm mx-auto">
            <template x-for="(image, index) in product.images" :key="index">
                <button
                    @click="selectedImage = index"
                    :class="selectedImage === index ? 'border-pink-500 border-2' : 'border-gray-200 border hover:border-pink-300'"
                    class="aspect-square relative overflow-hidden rounded-md transition-colors"
                >
                    <img 
                        :src="image" 
                        :alt="`${product.name} view ${index + 1}`"
                        class="w-full h-full object-cover"
                    >
                </button>
            </template>
        </div>
    </div>

    <!-- Product Information -->
    <div class="space-y-4">
        <!-- Header with Wishlist -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm text-gray-600 mb-1" x-text="product.brand"></p>
                <h1 class="text-xl lg:text-2xl font-bold mb-2 text-gray-800" x-text="product.name"></h1>
            </div>
            @auth
                <div x-data="wishlistButton({ productId: {{ $productData['id'] ?? 0 }}, initialIsInWishlist: false })" class="ml-4">
                    <button @click="toggleWishlist" type="button" :disabled="isLoading" 
                            class="p-2 text-gray-400 hover:text-pink-500 disabled:opacity-50 transition-colors">
                        <template x-if="isLoading">
                            <svg class="animate-spin h-5 w-5 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!isLoading">
                            <svg :class="{ 'text-pink-500 fill-current': isInWishlist }" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 0-6.364 0z"></path>
                            </svg>
                        </template>
                    </button>
                </div>
            @endauth
        </div>

        <!-- Rating -->
        <div class="flex items-center gap-2">
            <div class="flex items-center">
                <template x-for="i in 5" :key="i">
                    <svg :class="i <= Math.floor(product.rating) ? 'text-yellow-400 fill-current' : 'text-gray-300'" 
                         class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </template>
                <span class="text-sm font-medium ml-1" x-text="product.rating || '0'"></span>
            </div>
            <span class="text-sm text-gray-600">
                (<span x-text="(product.review_count || 0).toLocaleString()"></span> reviews)
            </span>
        </div>

        <!-- Pricing -->
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-bold text-gray-900">GH₵<span x-text="currentPrice"></span></span>
                <template x-if="product.original_price > currentPrice">
                    <span class="text-lg text-gray-500 line-through">
                        GH₵<span x-text="product.original_price"></span>
                    </span>
                </template>
            </div>
            <!-- Updated stock display logic -->
            <p :class="product.in_stock ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                <template x-if="product.in_stock">
                    <span x-text="product.has_variants ? 'In Stock' : `In Stock (${product.stock_count} available)`"></span>
                </template>
                <template x-if="!product.in_stock">
                    <span>Out of Stock</span>
                </template>
            </p>
        </div>

        <!-- Variants (Inline Display) -->
        <div x-show="hasVariants" class="space-y-3">
            <h3 class="text-sm font-medium text-gray-900 uppercase">Available Options</h3>
            <template x-for="(values, attribute) in product.variants" :key="attribute">
                <div>
                    <label class="text-sm font-medium mb-2 block capitalize text-gray-700">
                        <span x-text="attribute"></span>: 
                        <span class="font-semibold text-pink-600" x-text="selectedVariants[attribute]"></span>
                    </label>
                    <div class="flex gap-2 flex-wrap">
                        <template x-for="value in values" :key="value">
                            <button 
                                @click="selectVariant(attribute, value)" 
                                :class="selectedVariants[attribute] === value ? 'border-pink-500 bg-pink-500 text-white' : 'border-gray-300 hover:border-pink-500'" 
                                class="px-3 py-1 border rounded text-sm uppercase focus:outline-none transition-all" 
                                x-text="value">
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Quantity -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Quantity</label>
            <div class="flex items-center max-w-[120px]">
                <button type="button"
                        @click="quantity > 1 ? quantity-- : null"
                        :disabled="quantity <= 1"
                        class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-l p-2 h-10 disabled:opacity-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <input type="number"
                       x-model.number="quantity"
                       min="1"
                       :max="maxQuantity"
                       class="bg-gray-50 border-t border-b border-gray-300 h-10 text-center text-gray-900 text-sm w-full py-2 focus:ring-pink-500 focus:border-pink-500">
                <button type="button"
                        @click="quantity < maxQuantity ? quantity++ : null"
                        :disabled="quantity >= maxQuantity"
                        class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-r p-2 h-10 flex items-center justify-center disabled:opacity-50">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Add to Cart Button -->
        <button
            @click="handleAddToCart()"
            :disabled="(!product.in_stock) || isLoading"
            class="w-full bg-pink-600 hover:bg-pink-700 disabled:bg-gray-400 text-white font-medium py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5-6m0 0L4 5M7 13h10m0 0v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6z"></path>
            </svg>
            <span x-text="isLoading ? 'Adding...' : (hasVariants && !allVariantsSelected ? 'Select Options' : 'Add to Cart - GH₵' + (currentPrice * quantity).toFixed(2))"></span>
        </button>

        <!-- Shipping Information -->
        <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <div>
                    <p class="font-medium text-sm">Free Shipping</p>
                    <p class="text-xs text-gray-600">Estimated delivery: 2-3 days</p>
                </div>
            </div>

            <hr class="border-gray-200">

            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <div>
                    <p class="font-medium text-sm">Easy Returns</p>
                    <p class="text-xs text-gray-600">30-day returns</p>
                </div>
            </div>

            <hr class="border-gray-200">

            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <div>
                    <p class="font-medium text-sm">Secure Payment</p>
                    <p class="text-xs text-gray-600">Your payment information is protected</p>
                </div>
            </div>
        </div>

        <!-- Product Details Tabs -->
        <div x-data="{ activeTab: 'description' }" class="w-full">
            <div class="flex border-b border-gray-200">
                <button
                    @click="activeTab = 'description'"
                    :class="activeTab === 'description' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-2 px-4 border-b-2 font-medium text-sm transition-colors"
                >
                    Description
                </button>
                <button
                    @click="activeTab = 'features'"
                    :class="activeTab === 'features' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-2 px-4 border-b-2 font-medium text-sm transition-colors"
                >
                    Features
                </button>
                <button
                    @click="activeTab = 'specifications'"
                    :class="activeTab === 'specifications' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-2 px-4 border-b-2 font-medium text-sm transition-colors"
                >
                    Specifications
                </button>
            </div>

            <div class="mt-4">
                <div x-show="activeTab === 'description'" x-cloak>
                    <p class="text-gray-600 leading-relaxed text-sm" x-text="product.description"></p>
                </div>

                <div x-show="activeTab === 'features'" x-cloak>
                    <ul class="space-y-2">
                        <template x-for="feature in product.features" :key="feature">
                            <li class="flex items-start gap-2">
                                <div class="w-1.5 h-1.5 bg-pink-600 rounded-full mt-2 flex-shrink-0"></div>
                                <span class="text-gray-600 text-sm" x-text="feature"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <div x-show="activeTab === 'specifications'" x-cloak>
                    <div class="space-y-2">
                        <template x-for="[key, value] in Object.entries(product.specifications)" :key="key">
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-sm" x-text="key"></span>
                                <span class="text-gray-600 text-sm" x-text="value"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Variation Selection Modal -->
    <div x-show="isModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="variationModalLabel" role="dialog" aria-modal="true" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

            <!-- This element is to trick the browser into centering the modal contents -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="variationModalLabel">
                                Confirm Your Selection
                            </h3>
                            <div class="mt-4">
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Selected Options:</h4>
                                    <div class="space-y-1">
                                        <template x-for="(value, key) in selectedVariants" :key="key">
                                            <div class="flex justify-between text-sm">
                                                <span class="capitalize text-gray-600" x-text="key + ':'"></span>
                                                <span class="font-medium text-gray-900" x-text="value"></span>
                                            </div>
                                        </template>
                                        <div class="flex justify-between text-sm pt-2 border-t">
                                            <span class="text-gray-600">Quantity:</span>
                                            <span class="font-medium text-gray-900" x-text="quantity"></span>
                                        </div>
                                        <div class="flex justify-between text-sm pt-1">
                                            <span class="text-gray-600">Available:</span>
                                            <span class="font-medium text-green-600" x-text="currentStock + ' in stock'"></span>
                                        </div>
                                        <div class="flex justify-between text-lg font-bold pt-2 border-t">
                                            <span class="text-gray-900">Total:</span>
                                            <span class="text-pink-600">GH₵<span x-text="(currentPrice * quantity).toFixed(2)"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="addToCart(true)"
                            :disabled="isLoading || currentStock < quantity"
                            :class="{'opacity-50 cursor-not-allowed': isLoading || currentStock < quantity}"
                    >
                        <span x-text="isLoading ? 'Adding...' : (currentStock < quantity ? 'Insufficient Stock' : 'Add to Cart')"></span>
                    </button>
                    <button type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="closeModal"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
