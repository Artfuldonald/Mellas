{{-- resources/views/products/partials/product-details.blade.php --}}
{{-- This partial inherits its `productSelector` data from show.blade.php --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-x-12 gap-y-12">
    
    <!--  IMAGE GALLERY (Column 1)  -->
    <div class="lg:col-span-1">
        <div x-data="productImageGallery({ 
                images: product.images, 
                initialImage: product.main_image 
             })">
            
            <div class="aspect-square bg-white rounded-lg mb-4 border p-2 sticky top-24">
                {{-- It now uses the `currentImage` from our new component --}}
                <img :src="currentImage" :alt="product.name" class="w-full h-full object-contain rounded-md">
            </div>

            <div class="relative mt-4" x-show="images.length > 1">
                <button x-show="!atStart" @click="prev()" class="absolute top-1/2 -left-3 z-10 -translate-y-1/2 bg-white/80 p-1.5 rounded-full shadow-md hover:bg-white transition"><x-heroicon-o-chevron-left class="w-5 h-5 text-gray-600" /></button>
                
                <div x-ref="slider" @scroll.debounce.100ms="checkScroll()" class="hide-scrollbar flex overflow-x-auto space-x-3 pb-1 scroll-smooth">
                    <template x-for="(image, index) in images" :key="index">
                        <div class="flex-shrink-0 w-20 h-20">
                            {{-- Click now sets `currentImage` to the large version --}}
                            <button @click="currentImage = image.large_url" 
                                    :class="{'ring-2 ring-pink-500': currentImage === image.large_url}" 
                                    class="w-full h-full bg-gray-100 rounded-md overflow-hidden transition-all focus:outline-none border p-1">
                                {{-- The img tag correctly shows the thumbnail version --}}
                                <img :src="image.thumb_url" class="w-full h-full object-contain">
                            </button>
                        </div>
                    </template>
                </div>

                <button x-show="!atEnd" @click="next()" class="absolute top-1/2 -right-3 z-10 -translate-y-1/2 bg-white/80 p-1.5 rounded-full shadow-md hover:bg-white transition"><x-heroicon-o-chevron-right class="w-5 h-5 text-gray-600" /></button>
            </div>
        </div>       
    </div>

    <!--  PRODUCT INFO & ACTIONS (Column 2)  -->
    <div class="lg:col-span-1">
        <div class="flex flex-col h-full space-y-4">
            {{-- Product Header --}}
            <div class="pb-3 border-b border-gray-200">
                <div class="flex items-start justify-between">
                    <h1 class="text-2xl font-bold text-gray-800 leading-tight" x-text="product.name"></h1>
                    @auth
                        <div x-data="wishlistButton({ productId: product.id, initialIsInWishlist: {{ in_array($product['id'], $userWishlistProductIds) ? 'true' : 'false' }}, isAuthenticated: true })" class="ml-4 flex-shrink-0">
                            <button @click="handleClick()" title="Add to Wishlist" class="p-2 rounded-full text-gray-400 hover:text-pink-500 transition-colors">
                                <template x-if="isInWishlist"><x-heroicon-s-heart class="w-6 h-6 text-pink-500"/></template>
                                <template x-if="!isInWishlist"><x-heroicon-o-heart class="w-6 h-6"/></template>
                            </button>
                        </div>
                    @endauth
                </div>
                <p class="text-sm text-gray-500 mt-1">Brand: <a href="#" class="text-pink-600 hover:underline" x-text="product.brand"></a></p>
            </div>                

            {{-- Rating Section --}}
            <div class="flex items-center gap-4 pt-2">
                 <div class="flex items-center">
                    <template x-for="i in 5"><svg class="w-5 h-5" :class="i <= product.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg></template>
                    <span class="ml-2 text-sm text-gray-600 font-medium" x-text="product.rating ? product.rating.toFixed(1) : '0.0'"></span>
                </div>
                <a href="#reviews" class="text-sm text-pink-600 hover:underline -mt-2" x-text="`(${product.review_count} verified ratings)`"></a>
            </div>

            {{-- Pricing Box --}}
            <div class="bg-pink-50/50 rounded-lg p-4 my-2">
                <div class="flex items-baseline gap-3"><span class="text-3xl font-bold text-pink-600" x-text="formatCurrency(currentPrice)"></span><template x-if="product.original_price > currentPrice"><span class="text-lg text-gray-500 line-through" x-text="formatCurrency(product.original_price)"></span></template></div>
                <template x-if="product.discount > 0"><span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full mt-1 inline-block" x-text="'-' + product.discount + '%'"></span></template>
            </div>

            {{-- Variant Selection --}}
           <div x-show="product.has_variants" class="space-y-4">
            <template x-for="(values, attribute) in product.variants" :key="attribute">
                <div>
                    <label class="text-sm font-medium mb-2 block capitalize text-gray-800">
                        <span x-text="attribute"></span>: 
                        <span class="font-semibold text-gray-600" x-text="selectedOptions[attribute] || ''"></span>
                    </label>
                    <div class="flex gap-2 flex-wrap">
                        <template x-for="value in values" :key="value">
                            <button @click="selectOption(attribute, value)"
                                    :disabled="!isOptionAvailable(attribute, value)"
                                    :class="{
                                        'ring-2 ring-pink-500 bg-white border-pink-500': selectedOptions[attribute] === value,
                                        'ring-1 ring-gray-300 bg-white hover:ring-pink-400': selectedOptions[attribute] !== value,
                                        'opacity-40 cursor-not-allowed bg-gray-100 text-gray-400': !isOptionAvailable(attribute, value)
                                    }"
                                    class="px-4 py-1.5 rounded-md text-sm font-medium transition-all border">
                                <span x-text="value"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
            
            {{-- Quantity Selector --}}
            <div class="mt-4">
                <label class="text-sm font-medium mb-2 block text-gray-800">Quantity</label>
                <div class="flex items-center">
                    <div x-data="{ decrement() { if (quantity > 1) quantity--; }, increment() { if (quantity < currentStock) quantity++; } }" class="flex items-center border border-gray-300 rounded-md">
                        <button @click="decrement()" class="p-2.5 text-gray-600 hover:bg-gray-100 disabled:opacity-50" :disabled="quantity <= 1"><x-heroicon-o-minus class="w-4 h-4"/></button>
                        <input type="text" x-model.number="quantity" readonly class="w-12 text-center border-y-0 border-x focus:ring-0 font-semibold text-gray-800">
                        <button @click="increment()" class="p-2.5 text-gray-600 hover:bg-gray-100 disabled:opacity-50" :disabled="quantity >= currentStock"><x-heroicon-o-plus class="w-4 h-4"/></button>
                    </div>
                    <div class="ml-4 text-sm h-5">
                        <p x-show="isSelectionValid() && currentStock > 0" class="text-gray-500" x-text="`${currentStock} pieces available`"></p>
                        <p x-show="errorMessage" class="text-red-600 font-semibold" x-text="errorMessage"></p>
                    </div>
                </div>
            </div>

            <div class="flex-grow"></div>

            {{-- Add to Cart Button --}}
            <div class="mt-6">
                <button @click="openConfirmationModal()" 
                        :disabled="!currentVariant || currentStock < 1"
                        class="w-full bg-pink-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-pink-700 transition-all disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center text-base">
                    <x-heroicon-o-shopping-cart class="w-5 h-5 mr-2"/>
                    <!-- Show prompt if needed, otherwise show 'Add to Cart' -->
                    <span x-text="selectionPrompt || 'Add to Cart'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    
    function productImageGallery(config) {
        return {
            images: config.images,
            currentImage: config.initialImage || (config.images.length > 0 ? config.images[0].large_url : '{{ asset('images/placeholder.png') }}'),
            atStart: true,
            atEnd: false,
            
            init() {
                // Wait for Alpine to initialize and render the DOM
                this.$nextTick(() => {
                    this.checkScroll();
                });
            },
            
            checkScroll() {
                const slider = this.$refs.slider;
                if (!slider) return;
                this.atStart = slider.scrollLeft === 0;
                this.atEnd = Math.abs(slider.scrollWidth - slider.clientWidth - slider.scrollLeft) < 1;
            },
            
            prev() {
                this.$refs.slider.scrollBy({ left: -this.$refs.slider.clientWidth, behavior: 'smooth' });
            },
            
            next() {
                this.$refs.slider.scrollBy({ left: this.$refs.slider.clientWidth, behavior: 'smooth' });
            }
        }
    }
    
    function productSelector(config) {
    return {
        // --- State Properties ---
        product: config.product,
        selectedOptions: {},
        currentVariant: null, // Holds the entire data object for the selected variant
        quantity: 1,
        isModalOpen: false,
        isLoading: false,

        errorMessage: '',        
       
        get currentPrice() {
            return this.currentVariant ? this.currentVariant.price : this.product.price;
        },
        get currentStock() {
            return this.currentVariant ? this.currentVariant.stock : this.product.stock_count;
        },
        get selectionPrompt() {
            if (!this.product.has_variants) return '';
          
            for (const attribute in this.selectedOptions) {
                if (this.selectedOptions[attribute] === null) {
                    return `Please select ${attribute}`;
                }
            }
            return ''; 
        },
        isSelectionValid() {
            if (!this.product.has_variants) {
                return true; // Simple products are always valid
            }
            // Check if any option is still null
            return Object.values(this.selectedOptions).every(v => v !== null);
        },
        // --- Initialization ---
        init() {
            if (this.product.has_variants) {
                // Initialize selectedOptions with null for each attribute
                for (const attribute in this.product.variants) {
                    this.selectedOptions[attribute] = null;
                }
            } else {
                // If no variants, the "currentVariant" is just the base product
                this.currentVariant = { stock: this.product.stock_count, price: this.product.price };
            }
        },
            // Helper function to format currency
            formatCurrency(amount) {
                return new Intl.NumberFormat('en-GH', { style: 'currency', currency: 'GHS' }).format(amount);
            },
            
           selectOption(attribute, value) {
            // If the user clicks the same option again, deselect it
            if (this.selectedOptions[attribute] === value) {
                this.selectedOptions[attribute] = null;
            } else {
                this.selectedOptions[attribute] = value;
            }
            this.updateCurrentVariant();
        },

        updateCurrentVariant() {
            // If any option is not selected, reset the variant info
            if (Object.values(this.selectedOptions).some(v => v === null)) {
                this.currentVariant = null;
                return;
            }
            
            // Create the lookup key (e.g., "Black-XL")
            let lookupKey = Object.values(this.selectedOptions).sort().join('-');
            
            // Find the variant in our new master map
            const variant = this.product.variant_data_map[lookupKey];
            
            if (variant) {
                this.currentVariant = variant;
            } else {
                // This combination is invalid
                this.currentVariant = { stock: 0 }; // Set stock to 0 for invalid combos
            }
        },
        
        // This is the new "brain" for disabling buttons
        isOptionAvailable(attribute, value) {
            // Temporarily select the option to see what combinations it makes
            const tempSelection = { ...this.selectedOptions, [attribute]: value };

            // Check if ANY valid variant in the map can be formed with this temporary selection
            for (const key in this.product.variant_data_map) {
                const variantAttributes = this.product.variant_data_map[key].attributes;
                let isMatch = true;
                // Check if the variant's attributes match our temporary selection
                for (const attr in tempSelection) {
                    if (tempSelection[attr] !== null && variantAttributes[attr] !== tempSelection[attr]) {
                        isMatch = false;
                        break;
                    }
                }
                if (isMatch) return true; // Found a valid variant, so this option is possible
            }
            
            return false; // No valid variant found for this potential selection
        },

        openConfirmationModal() {
            if (!this.currentVariant) return; // Can't add if no valid variant is selected
            if (this.quantity > this.currentVariant.stock) {
                window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: 'Not enough items in stock.' }}));
                return;
            }
            this.isModalOpen = true;
        },

        handleAddToCart() {
            if (!this.currentVariant) return; // Double-check
            this.isLoading = true;
            this.errorMessage = '';
            
            let formData = new FormData();
            formData.append('product_id', this.product.id);           
            // We now send the specific variant_id from our currentVariant object
            formData.append('variant_id', this.currentVariant.id); 
            formData.append('quantity', this.quantity);

                fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                })
                .then(res => res.json().then(data => ({ ok: res.ok, data })))
                .then(({ ok, data }) => {
                    if (ok) {
                        this.isModalOpen = false;
                        window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'success', message: 'Added to cart!' }}));
                        // Dispatch event to update the global cart count in your header
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: data.cart_count } }));
                    } else {
                        // Show the error from the backend (e.g., "Not enough items in stock")
                        this.errorMessage = data.message;
                        window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: data.message }}));
                    }
                })
                .catch(() => {
                    this.errorMessage = 'An unexpected error occurred.';
                })
                .finally(() => {
                    this.isLoading = false;
                });
            }
        }
    }
</script>
@endpush