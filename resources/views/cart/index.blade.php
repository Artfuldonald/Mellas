{{-- resources/views/cart/index.blade.php --}}
<x-app-layout title="Your Shopping Cart">
    <div class="bg-pink-50 py-8 border-b border-pink-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-pink-800">Shopping Cart</h1>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if(count($cartItems) > 0)
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start xl:gap-x-16">
                <section aria-labelledby="cart-heading" class="lg:col-span-7 bg-white p-6 rounded-lg shadow-xl">
                    <ul role="list" class="divide-y divide-gray-200 border-t border-b border-gray-200">
                        @foreach($cartItems as $cartItemId => $item)
                            <li class="flex py-6 sm:py-8">
                                <div class="flex-shrink-0">
                                    @php $productImage = $item->product?->images?->first(); @endphp
                                    <img src="{{ $productImage?->image_url ?? asset('images/placeholder.png') }}"
                                         alt="{{ $productImage?->alt ?? $item->product?->name }}"
                                         class="w-24 h-24 rounded-md object-cover object-center sm:w-32 sm:h-32 border">
                                </div>
                                <div class="ml-4 flex-1 flex flex-col justify-between sm:ml-6">
                                    <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                                        <div>
                                            <div class="flex justify-between">
                                                <h3 class="text-sm">
                                                   <a href="{{ route('products.show', $item->product->slug) }}" class="font-medium text-gray-700 hover:text-pink-600">
                                                        {{ $item->product->name }}
                                                        @if (!empty($item->variant_data['attributes']))
                                                            <span class="text-xs text-gray-500">
                                                                - {{ implode(' / ', array_values($item->variant_data['attributes'])) }}
                                                            </span>
                                                        @endif
                                                    </a>
                                                </h3>
                                            </div>
                                            <p class="mt-1 text-sm font-medium text-gray-900">GH₵ {{ number_format($item['price_at_add'], 2) }}</p>
                                        </div>

                                        <div class="mt-4 sm:mt-0 sm:pr-9">
                                            <form class="update-cart-quantity" data-product-id="{{ $item->product_id }}">
                                                <div class="flex items-center">
                                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99"
                                                           class="w-16 text-center border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
                                                    <button type="submit" class="ml-2 text-xs text-pink-600 hover:text-pink-800">Update</button>
                                                </div>
                                            </form>

                                            <div class="absolute top-0 right-0">
                                                <button class="remove-cart-item -m-2 p-2 inline-flex text-gray-400 hover:text-pink-500"
                                                        data-product-id="{{ $item->product_id }}">
                                                    <span class="sr-only">Remove</span>
                                                    <x-heroicon-o-x-mark class="h-5 w-5" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-6">
                        <button id="clear-cart" class="text-sm font-medium text-pink-600 hover:text-pink-500">
                            <x-heroicon-o-trash class="w-4 h-4 inline-block mr-1" /> Clear entire cart
                        </button>
                    </div>
                </section>

                <!-- Order Summary -->
                <section class="mt-16 bg-pink-50 rounded-lg px-4 py-6 sm:p-6 lg:p-8 lg:mt-0 lg:col-span-5 shadow-xl">
                    <h2 class="text-lg font-medium text-gray-900">Order summary</h2>
                    <dl class="mt-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-600">Subtotal</dt>
                            <dd class="text-sm font-medium text-gray-900">GH₵ {{ number_format($subtotal, 2) }}</dd>
                        </div>
                        <div class="border-t border-pink-200 pt-4 flex items-center justify-between">
                            <dt class="text-base font-medium text-gray-900">Order total</dt>
                            <dd class="text-base font-medium text-gray-900">GH₵ {{ number_format($subtotal, 2) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <a href="#" {{-- TODO: add checkout route --}}
                           class="w-full bg-pink-600 border border-transparent rounded-lg shadow-sm py-3 px-4 text-base font-medium text-white text-center hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-pink-50 focus:ring-pink-500">
                            Proceed to Checkout
                        </a>
                    </div>
                </section>
            </div>
        @else
            <div class="text-center py-16 bg-white rounded-lg shadow-xl">
                <x-heroicon-o-shopping-cart class="mx-auto h-16 w-16 text-pink-400"/>
                <h3 class="mt-4 text-xl font-semibold text-gray-900">Your Cart is Empty</h3>
                <p class="mt-2 text-base text-gray-500">Looks like you haven't added anything to your cart yet.</p>
                <div class="mt-8">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-base font-medium rounded-lg text-white bg-pink-600 hover:bg-pink-700">
                        <x-heroicon-s-shopping-bag class="-ml-1 mr-2 h-5 w-5" />
                        Start Shopping
                    </a>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Update quantity
            document.querySelectorAll('.update-cart-quantity').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const productId = this.dataset.productId;
                    const quantity = this.querySelector('input[name="quantity"]').value;

                    fetch("{{ route('cart.update-item') }}", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ product_id: productId, quantity })
                    }).then(res => res.json()).then(data => {
                        if (data.success) location.reload();
                        else alert(data.message);
                    });
                });
            });

            // Remove item
            document.querySelectorAll('.remove-cart-item').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!confirm('Remove this item?')) return;

                    const productId = this.dataset.productId;

                    fetch("{{ route('cart.remove-item') }}", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ product_id: productId })
                    }).then(res => res.json()).then(data => {
                        if (data.success) location.reload();
                        else alert(data.message);
                    });
                });
            });

            // Clear cart
            document.querySelector('#clear-cart')?.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Clear the entire cart?')) return;

                fetch("{{ route('cart.clear') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                }).then(res => res.json()).then(data => {
                    if (data.success) location.reload();
                    else alert('Failed to clear cart');
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
