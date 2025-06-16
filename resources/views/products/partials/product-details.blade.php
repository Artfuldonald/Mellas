<div x-data="productDetails({{ json_encode($product) }})" class="grid lg:grid-cols-2 gap-8 lg:gap-12">
    <!-- Product Images -->
    <div class="space-y-4">
        <div class="aspect-square relative overflow-hidden rounded-lg border">
            <img :src="product.images[selectedImage]" :alt="product.name" class="w-full h-full object-cover">
            <template x-if="product.discount > 0">
                <span class="absolute top-4 left-4 bg-red-500 text-white px-2 py-1 rounded text-sm font-medium">
                    -<span x-text="product.discount"></span>%
                </span>
            </template>
        </div>
        <div class="grid grid-cols-5 gap-2">
            <template x-for="(image, index) in product.images" :key="index">
                <button @click="selectedImage = index" :class="selectedImage === index ? 'border-blue-500 border-2' : 'border-gray-300 hover:border-gray-400'" class="aspect-square relative overflow-hidden rounded-md transition-colors">
                    <img :src="image" :alt="`${product.name} view ${index + 1}`" class="w-full h-full object-cover">
                </button>
            </template>
        </div>
    </div>

    <!-- Product Information -->
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-600 mb-2" x-text="product.brand"></p>
            <h1 class="text-2xl lg:text-3xl font-bold mb-4" x-text="product.name"></h1>
            <div class="flex items-center gap-4 mb-4">
                <div class="flex items-center gap-1">
                    <template x-for="i in 5" :key="i">
                        <i :class="i <= Math.floor(product.rating) ? 'text-yellow-400' : 'text-gray-300'" data-lucide="star" class="w-5 h-5 fill-current"></i>
                    </template>
                    <span class="text-sm font-medium ml-1" x-text="product.rating"></span>
                </div>
                <span class="text-sm text-gray-600">(<span x-text="product.review_count.toLocaleString()"></span> reviews)</span>
            </div>
        </div>

        <!-- Pricing -->
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <span class="text-3xl font-bold">$<span x-text="product.price.toFixed(2)"></span></span>
                <template x-if="product.original_price > product.price">
                    <span class="text-lg text-gray-500 line-through">$<span x-text="product.original_price.toFixed(2)"></span></span>
                </template>
            </div>
            <p :class="product.in_stock ? 'text-green-600' : 'text-red-600'" class="font-medium">
                <span x-text="product.in_stock ? `In Stock (${product.stock_count} available)` : 'Out of Stock'"></span>
            </p>
        </div>

        <!-- Product Variants -->
        <div class="space-y-4">
            <template x-for="(values, attribute) in product.variants" :key="attribute">
                <div>
                    <label class="text-sm font-medium mb-2 block capitalize">
                        <span x-text="attribute"></span>: <span class="font-semibold" x-text="selectedVariants[attribute]"></span>
                    </label>
                    <div class="flex gap-2">
                        <template x-for="value in values" :key="value">
                            <button @click="selectedVariants[attribute] = value" :class="selectedVariants[attribute] === value ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 hover:border-gray-400'" class="px-4 py-2 border rounded-md text-sm transition-colors" x-text="value"></button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- Quantity -->
        <div>
            <label class="text-sm font-medium mb-2 block">Quantity</label>
            <div class="flex items-center gap-3">
                <div class="flex items-center border rounded-md">
                    <button @click="quantity = Math.max(1, quantity - 1)" :disabled="quantity <= 1 || !product.in_stock" class="px-3 py-2 hover:bg-gray-100 disabled:opacity-50"><i data-lucide="minus" class="w-4 h-4"></i></button>
                    <span class="px-4 py-2 min-w-[3rem] text-center" x-text="quantity"></span>
                    <button @click="quantity = Math.min(product.stock_count, quantity + 1)" :disabled="quantity >= product.stock_count || !product.in_stock" class="px-3 py-2 hover:bg-gray-100 disabled:opacity-50"><i data-lucide="plus" class="w-4 h-4"></i></button>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <button @click="addToCart()" :disabled="!product.in_stock" class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i> Add to Cart - $<span x-text="(product.price * quantity).toFixed(2)"></span>
            </button>
            <div class="flex gap-3">
                <button @click="toggleWishlist()" class="flex-1 border border-gray-300 hover:border-gray-400 text-gray-700 font-medium py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors">
                    <i :data-lucide="isWishlisted ? 'heart' : 'heart'" :class="isWishlisted ? 'text-red-500 fill-current' : ''" class="w-5 h-5"></i>
                    <span x-text="isWishlisted ? 'Wishlisted' : 'Add to Wishlist'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- This script tag must be inside this partial file, right at the end --}}
<script>
    function productDetails(productData) {
        return {
            product: productData,
            selectedImage: 0,
            // Dynamically initialize selected variants
            selectedVariants: {},
            quantity: 1,
            isWishlisted: false,

            init() {
                // Set the first option as the default for each variant type
                for (const attribute in this.product.variants) {
                    if (this.product.variants[attribute].length > 0) {
                        this.selectedVariants[attribute] = this.product.variants[attribute][0];
                    }
                }
                // Re-initialize lucide icons after Alpine makes changes
                this.$nextTick(() => lucide.createIcons());
            },
            
            addToCart() {
                if(!this.product.in_stock) return;
                
                // Construct the payload
                let payload = {
                    product_id: this.product.id,
                    quantity: this.quantity,
                    variants: this.selectedVariants, // e.g., { colors: 'Black', sizes: 'Standard' }
                    _token: '{{ csrf_token() }}' // Add CSRF token
                };
                
                console.log('Adding to cart:', payload);
                // In a real app, you would make your fetch call here
                alert(`${this.quantity} x ${this.product.name} (${Object.values(this.selectedVariants).join(', ')}) added to cart!`);
            },
            
            toggleWishlist() {
                this.isWishlisted = !this.isWishlisted;
                // In a real app, you would make your fetch call here
                console.log('Wishlist status changed:', this.isWishlisted);
            }
        }
    }
</script>