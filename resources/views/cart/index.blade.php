{{-- views/cart/index.blade.php --}}
<x-app-layout title="Your Shopping Cart">
    <div x-data="cartPage({
            initialItems: {{ Js::from($cartItems) }},
            initialTotals: { subtotal: {{ $subtotal }}, tax: {{ $tax }}, shipping: {{ $shipping }}, grandTotal: {{ $total }} }
        })"
         class="bg-pink-50/50 min-h-screen py-8 md:py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8 text-center">Shopping Cart</h1>

            <template x-if="items.length === 0">
                <div class="bg-white rounded-xl shadow-lg p-8 text-center max-w-lg mx-auto">
                    <x-heroicon-o-shopping-cart class="w-20 h-20 mx-auto text-pink-200" />
                    <h2 class="mt-4 text-2xl font-semibold text-gray-700">Your cart is empty</h2>
                    <p class="mt-2 text-gray-500">Add some lovely items to get started.</p>
                    <a href="{{ route('products.index') }}" class="mt-6 inline-block bg-pink-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-pink-700 transition-all">
                        Continue Shopping
                    </a>
                </div>
            </template>

            <template x-if="items.length > 0">
                <div class="flex flex-col lg:flex-row gap-8 xl:gap-12">
                    <div class="w-full lg:w-[65%]">
                        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                            <div class="flex justify-between items-center border-b border-gray-200 pb-4 mb-4">
                                <h2 class="text-xl font-semibold text-gray-800">Cart Items (<span x-text="items.length"></span>)</h2>
                                <button @click="clearCart()" :disabled="pageIsLoading" class="text-sm font-medium text-gray-500 hover:text-red-600 transition-colors flex items-center gap-1 disabled:opacity-50">
                                    <svg x-show="pageIsLoading" class="animate-spin h-4 w-4 text-pink-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <x-heroicon-o-trash x-show="!pageIsLoading" class="w-4 h-4" />
                                    Clear Cart
                                </button>
                            </div>

                            <div class="divide-y divide-gray-200">
                                <template x-for="item in items" :key="item.id">
                                    <div class="flex flex-col sm:flex-row py-5 gap-4">
                                        <div class="flex-shrink-0">
                                            <a :href="`/products/${item.product.slug}`">
                                                <img :src="(item.product.images && item.product.images.length > 0) ? item.product.images[0].image_url : '{{ asset('images/placeholder.png') }}'" :alt="item.product.name" class="w-24 h-24 sm:w-32 sm:h-32 object-cover rounded-md border">
                                            </a>
                                        </div>
                                        
                                        <div class="flex-1 flex flex-col sm:flex-row justify-between">
                                            <div class="flex-grow">
                                                {{-- This displays the base product name --}}
                                                <a :href="`/products/${item.product.slug}`" class="font-semibold text-gray-800 hover:text-pink-600" x-text="item.product.name"></a>
                                                <div class="text-sm text-gray-500 mt-1" x-show="item.variant_data?.display_name" x-text="item.variant_data.display_name"></div>
                                                <p class="text-sm font-medium mt-1" :class="item.is_in_stock ? 'text-green-600' : 'text-red-600'" x-text="item.is_in_stock ? 'In Stock' : 'Out of Stock'"></p>
                                                <button @click="removeItem(item.id)" class="mt-3 text-sm font-medium text-gray-500 hover:text-red-700 flex items-center gap-1">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                    Remove
                                                </button>
                                            </div>

                                            <div class="flex flex-col items-start sm:items-end mt-4 sm:mt-0">
                                                <div class="text-lg font-bold text-gray-900" x-text="formatCurrency(item.price_at_add * item.quantity)"></div>
                                                
                                                {{-- CORRECTED PRICE & DISCOUNT DISPLAY --}}
                                                <template x-if="item.product.compare_at_price && item.product.compare_at_price > item.price_at_add">
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-sm text-gray-500 line-through" x-text="formatCurrency(item.product.compare_at_price * item.quantity)"></span>
                                                        <span class="bg-pink-100 text-pink-700 text-xs font-semibold px-2 py-0.5 rounded-full"
                                                              x-text="'-' + Math.round(((item.product.compare_at_price - item.price_at_add) / item.product.compare_at_price) * 100) + '%'">
                                                        </span>
                                                    </div>
                                                </template>
                                                
                                                <div class="relative flex items-center mt-3">
                                                    <div x-show="loadingStates[item.id]" x-transition.opacity class="absolute inset-0 bg-white/70 rounded-md flex items-center justify-center z-10">
                                                        <svg class="animate-spin h-5 w-5 text-pink-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </div>
                                                    <div class="flex items-center border border-gray-300 rounded-md">
                                                        <button @click="updateQuantity(item, item.quantity - 1)" :disabled="loadingStates[item.id] || item.quantity <= 1" class="px-3 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50 h-9">-</button>
                                                        <input type="number" x-model.number.debounce.500ms="item.quantity" @change="updateQuantity(item, item.quantity)" :disabled="loadingStates[item.id]" class="w-14 text-center border-l border-r focus:ring-pink-500 focus:border-pink-500 h-9" min="1" :max="item.max_stock">
                                                        <button @click="updateQuantity(item, item.quantity + 1)" :disabled="loadingStates[item.id] || item.quantity >= item.max_stock" class="px-3 py-1 text-gray-600 hover:bg-gray-100 disabled:opacity-50 h-9">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <div class="w-full lg:w-[35%]">
                        <div class="bg-white rounded-xl shadow-lg p-6 sticky top-8">
                            <h2 class="text-xl font-semibold mb-4 border-b pb-4">Order Summary</h2>
                            <div class="space-y-4 text-gray-600">
                                <div class="flex justify-between"><span>Subtotal</span><span class="font-medium" x-text="formatCurrency(totals.subtotal)"></span></div>
                                <div class="flex justify-between"><span>Shipping</span><span class="font-medium" x-text="formatCurrency(totals.shipping)"></span></div>
                                <div class="flex justify-between"><span>Taxes (8%)</span><span class="font-medium" x-text="formatCurrency(totals.tax)"></span></div>
                            </div>
                            <div class="border-t mt-4 pt-4">
                                <div class="flex justify-between items-center text-xl font-bold">
                                    <span>Grand Total</span>
                                    <span class="text-pink-600" x-text="formatCurrency(totals.grandTotal)"></span>
                                </div>
                            </div>                           
                            @auth
                                {{-- If user IS logged in, the button is a direct link to checkout --}}
                                <a href="{{ route('checkout.index') }}" class="mt-6 block w-full text-center bg-pink-600 text-white font-bold py-3 rounded-lg hover:bg-pink-700 transition-all shadow-md hover:shadow-lg">
                                    Proceed to Checkout
                                </a>
                            @else
                                {{-- If user is a GUEST, the button links to the login page with an 'intended' redirect --}}
                                <a href="{{ route('login', ['intended' => route('checkout.index')]) }}" class="mt-6 block w-full text-center bg-pink-600 text-white font-bold py-3 rounded-lg hover:bg-pink-700 transition-all shadow-md hover:shadow-lg">
                                    Login to Checkout
                                </a>
                            @endauth                            
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
                items: [],
                totals: config.initialTotals,
                pageIsLoading: false,
                loadingStates: {},
                debounceTimer: null,

                init() {
                    this.items = config.initialItems.map(item => {
                        this.loadingStates[item.id] = false;
                        return item;
                    });
                },

                formatCurrency(amount) { return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount); },
                setLoading(itemId, status) { this.loadingStates[itemId] = status; },

                updateQuantity(item, newQuantity) {
                    if (this.loadingStates[item.id]) return;
                    if (newQuantity < 1) { this.removeItem(item.id); return; }

                    if (newQuantity > item.max_stock) {
                        window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: 'Not enough items in stock.' } }));
                        item.quantity = item.max_stock;
                        return;
                    }
                    
                    item.quantity = newQuantity;
                    this.setLoading(item.id, true);

                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        // Use your existing, correct route
                        fetch(`{{ route('cart.update-item') }}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ cart_id: item.id, quantity: newQuantity })
                        })
                        .then(res => res.json().then(data => ({ ok: res.ok, data })))
                        .then(({ok, data}) => {
                            if(ok) {
                                this.totals = data.cart_totals;
                                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: data.cart_count } }));
                            } else {
                                window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: data.message || 'Error updating cart.' } }));
                                this.fetchCartItems(); // Re-sync on error
                            }
                        })
                        .catch(() => this.fetchCartItems())
                        .finally(() => this.setLoading(item.id, false));
                    }, 350);
                },

                removeItem(itemId) {
                    this.pageIsLoading = true;
                    fetch(`{{ route('cart.remove-item') }}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ cart_id: itemId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.items = this.items.filter(i => i.id !== itemId);
                        this.totals = data.cart_totals;
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: data.cart_count }}));
                        window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'success', message: data.message }}));
                    })
                    .finally(() => this.pageIsLoading = false);
                },

                clearCart() {
                    if (!confirm('Are you sure?')) return;
                    this.pageIsLoading = true;
                    fetch(`{{ route('cart.clear') }}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
                    .then(() => { 
                        this.items = []; 
                        this.totals = {subtotal:0, tax:0, shipping:0, grandTotal:0}; 
                        window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'success', message: 'Cart cleared.' }}));
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: 0 }}));
                    })
                    .finally(() => this.pageIsLoading = false);
                },

                // Helper to re-sync state with the server if something goes wrong
                fetchCartItems() {
                    // This route needs to be able to return JSON
                    // We will modify the controller's index method for this
                    fetch('{{ route("cart.index") }}', { headers: { 'Accept': 'application/json' }})
                    .then(res => res.json())
                    .then(data => {
                        this.items = data.items.map(item => {
                            this.loadingStates[item.id] = false;
                            return item;
                        });
                        this.totals = data.totals;
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>