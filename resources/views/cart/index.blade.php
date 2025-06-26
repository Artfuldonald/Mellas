<x-app-layout title="Your Shopping Cart">

    {{-- This Alpine.js component manages the entire state of the cart page --}}
    <div x-data="cartPage({
            initialItems: {{ Js::from($cartItems) }},
            initialTotals: { 
                subtotal: {{ $subtotal }}, 
                tax: {{ $tax }}, 
                shipping: {{ $shipping }},
                grandTotal: {{ $total }}
            }
        })"
         class="bg-pink-50/50 min-h-screen py-8 md:py-16">
        <div class="container mx-auto px-4">

            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8 text-center">Shopping Cart</h1>

            {{-- Empty Cart State --}}
            <template x-if="items.length === 0">
                <div class="bg-white rounded-xl shadow-lg p-8 text-center max-w-lg mx-auto">
                    <x-heroicon-o-shopping-cart class="w-20 h-20 mx-auto text-pink-200" />
                    <h2 class="mt-4 text-2xl font-semibold text-gray-700">Your cart is empty</h2>
                    <p class="mt-2 text-gray-500">Add some lovely items to get started.</p>
                    <a href="{{ route('products.index') }}"
                       class="mt-6 inline-block bg-pink-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-pink-700 transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                        Continue Shopping
                    </a>
                </div>
            </template>

            {{-- Cart with Items --}}
            <template x-if="items.length > 0">
                <div class="flex flex-col lg:flex-row gap-8 xl:gap-12">

                    {{-- Left Side: Cart Items List --}}
                    <div class="w-full lg:w-[65%]">
                        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                            <div class="flex justify-between items-center border-b border-gray-200 pb-4 mb-4">
                                <h2 class="text-xl font-semibold text-gray-800">
                                   Cart Items (<span x-text="items.length"></span>)
                                </h2>
                                <button @click="clearCart()" :disabled="isLoading"
                                        class="text-sm font-medium text-gray-500 hover:text-red-600 transition-colors flex items-center gap-1 disabled:opacity-50">
                                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-pink-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <x-heroicon-o-trash x-show="!isLoading" class="w-4 h-4" />
                                    Clear Cart
                                </button>
                            </div>

                            <div class="space-y-5">
                                <template x-for="item in items" :key="item.id">
                                    <div class="relative flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border border-gray-100 rounded-lg hover:shadow-md hover:border-pink-200 transition-all duration-300">
                                        <button @click="removeItem(item.id)"
                                                class="absolute top-2 right-2 text-gray-400 hover:text-red-500 p-1 rounded-full hover:bg-red-50 transition-colors"
                                                title="Remove item">
                                            <x-heroicon-o-x-mark class="w-5 h-5"/>
                                        </button>
                                        <div class="flex items-start flex-grow w-full pr-6">
                                            <a :href="`/products/${item.product.slug}`">
                                                <img :src="(item.product.images && item.product.images.length > 0) ? item.product.images[0].image_url : '{{ asset('images/placeholder.png') }}'"
                                                     :alt="item.product.name" class="w-24 h-24 object-cover rounded-md mr-4 border border-gray-200">
                                            </a>
                                            <div class="flex-grow">
                                                <a :href="`/products/${item.product.slug}`" class="font-semibold text-gray-800 hover:text-pink-600 transition-colors text-base" x-text="item.product.name"></a>
                                                <div class="text-sm text-gray-500 mt-1" x-show="item.variant_data && item.variant_data.attributes">
                                                    <template x-for="(value, attribute) in item.variant_data.attributes" :key="attribute">
                                                        <span>
                                                            <span class="capitalize" x-text="attribute"></span>: <span class="font-medium text-gray-700" x-text="value"></span><span class="mr-2"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-2">
                                                    Unit Price: <span x-text="formatCurrency(item.price_at_add)"></span>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="relative flex items-center space-x-6 w-full sm:w-auto justify-between mt-2 sm:mt-0">
                                            <div x-show="item.isUpdating" x-transition.opacity
                                                 class="absolute inset-0 bg-white/70 rounded-md flex items-center justify-center z-10">
                                                <svg class="animate-spin h-5 w-5 text-pink-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </div>

                                            <div class="flex items-center border border-gray-300 rounded-md">
                                                <button @click="updateQuantity(item, item.quantity - 1)" :disabled="item.isUpdating || item.quantity <= 1" class="px-3 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">-</button>
                                                <input type="number" x-model.number.debounce.500ms="item.quantity" @change="updateQuantity(item, item.quantity)" :disabled="item.isUpdating"
                                                       class="w-14 text-center border-l border-r border-gray-300 focus:ring-pink-500 focus:border-pink-500 text-base" min="1" max="10">
                                                <button @click="updateQuantity(item, item.quantity + 1)" :disabled="item.isUpdating || item.quantity >= item.max_stock" class="px-3 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">+</button>
                                            </div>
                                            <p class="font-semibold w-24 text-right text-lg text-gray-800" x-text="formatCurrency(item.price_at_add * item.quantity)"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Right Side: Order Summary --}}
                    <div class="w-full lg:w-[35%]">
                        <div class="bg-white rounded-xl shadow-lg p-6 sticky top-8">
                             <h2 class="text-xl font-semibold text-gray-800 border-b border-gray-200 pb-4 mb-4">Order Summary</h2>
                             <div class="space-y-4 text-gray-600">
                                <div class="flex justify-between"><span>Subtotal</span><span class="font-medium text-gray-800" x-text="formatCurrency(totals.subtotal)"></span></div>
                                <div class="flex justify-between"><span>Shipping</span><span class="font-medium text-gray-800" x-text="formatCurrency(totals.shipping)"></span></div>
                                <div class="flex justify-between"><span>Taxes (8%)</span><span class="font-medium text-gray-800" x-text="formatCurrency(totals.tax)"></span></div>
                             </div>
                             <div class="border-t border-gray-200 mt-4 pt-4">
                                <div class="flex justify-between items-center text-xl font-.bold text-gray-900">
                                    <span>Grand Total</span>
                                    <span class="text-pink-600" x-text="formatCurrency(totals.grandTotal)"></span>
                                </div>
                             </div>
                             <a href="#" {{-- Link to your checkout page --}}
                                class="mt-6 block w-full text-center bg-pink-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-pink-700 transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                                Proceed to Checkout
                             </a>
                             <p class="text-xs text-gray-400 text-center mt-4">Safe & Secure Payments</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @push('scripts')
    <script>
        function cartPage(config) {
            return {
                items: config.initialItems || [],
                totals: config.initialTotals || { subtotal: 0, tax: 0, shipping: 0, grandTotal: 0 },
                isLoading: false,
                debounceTimer: null,

                init() {
                    this.items = config.initialItems.map(item => ({ ...item, isUpdating: false }));
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('en-US', { style: 'currency', 'currency': 'USD' }).format(amount);
                },

                handleResponse(data) {
                    if (data.success) {
                        this.totals = data.cart_totals;
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: data.cart_count }}));
                        if (data.message) {
                            window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'success', message: data.message }}));
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: data.message || 'An unknown error occurred.' }}));
                    }
                },

                updateQuantity(item, newQuantity) {
                if (newQuantity < 1) {
                    this.removeItem(item.id);
                    return;
                }
                
                 if (newQuantity > item.max_stock) {
                    window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: 'Not enough items in stock.' } }));
                    // Revert the visual quantity if it was typed manually
                    item.quantity = item.max_stock;
                    return;
                }

                this.isLoading = true;
                item.quantity = newQuantity; // Optimistic update

                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    fetch(`{{ route('cart.set-quantity') }}`, { // <-- USE THE NEW ROUTE
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ 
                            product_id: item.product_id, // We need product_id for simple products
                            quantity: newQuantity 
                            // Add variant_id here if this item has one
                        })
                    })
                    .then(res => res.ok ? res.json() : Promise.reject(res.json()))
                    .then(data => {
                        if (data.success) {
                            this.totals = data.cart_totals;
                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: data.cart_count }}));
                        }
                    })
                    .catch(errorPromise => {
                        errorPromise.then(error => {
                            window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: error.message || 'Could not update cart.' }}));
                            // Optional: Revert the quantity by re-fetching the cart state
                        })
                    })
                    .finally(() => this.isLoading = false);
                }, 350);
            },

                removeItem(itemId) {
                    this.isLoading = true;
                    const originalItems = [...this.items];
                    this.items = this.items.filter(i => i.id !== itemId);

                    fetch(`{{ route('cart.remove-item') }}`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json'},
                        body: JSON.stringify({ cart_id: itemId })
                    })
                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                    .then(({ ok, data }) => {
                        if (ok) {
                            this.handleResponse(data);
                        } else {
                            this.items = originalItems;
                            this.handleResponse(data);
                        }
                    })
                    .catch(err => { 
                        this.items = originalItems;
                        console.error('Remove Error:', err); 
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
                },

                clearCart() {
                    if (!confirm('Are you sure you want to clear your entire cart?')) return;
                    this.isLoading = true;

                    fetch(`{{ route('cart.clear') }}`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json'}
                    })
                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                    .then(({ ok, data }) => {
                        if (ok) {
                            this.items = [];
                            this.handleResponse(data);
                        } else {
                            this.handleResponse(data);
                        }
                    })
                    .catch(err => console.error(err))
                    .finally(() => {
                        this.isLoading = false;
                    });
                }
            }
        }
    </script>
    @endpush

</x-app-layout>