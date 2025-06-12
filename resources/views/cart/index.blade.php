{{-- resources/views/cart/index.blade.php --}}
<x-app-layout title="Your Shopping Cart">
    <div class="bg-pink-50 py-8 border-b border-pink-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-pink-800">Shopping Cart</h1>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if (session('success'))
            <div class="mb-6 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if(count($cartItems) > 0)
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start xl:gap-x-16">
                <section aria-labelledby="cart-heading" class="lg:col-span-7 bg-white p-6 rounded-lg shadow-xl">
                    <h2 id="cart-heading" class="sr-only">Items in your shopping cart</h2>

                    <ul role="list" class="divide-y divide-gray-200 border-t border-b border-gray-200">
                        @foreach($cartItems as $cartItemId => $item)
                            <li class="flex py-6 sm:py-8">
                                <div class="flex-shrink-0">
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="w-24 h-24 rounded-md object-cover object-center sm:w-32 sm:h-32 border">
                                </div>

                                <div class="ml-4 flex-1 flex flex-col justify-between sm:ml-6">
                                    <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                                        <div>
                                            <div class="flex justify-between">
                                                <h3 class="text-sm">
                                                    <a href="{{ route('products.show', $item['slug']) }}" class="font-medium text-gray-700 hover:text-pink-600">
                                                        {{ $item['display_name'] }}
                                                    </a>
                                                </h3>
                                            </div>
                                            @if(!empty($item['attributes']))
                                                <div class="mt-1 flex text-xs text-gray-500">
                                                    @foreach($item['attributes'] as $attrName => $attrValue)
                                                        <p class="ml-2 pl-2 border-l border-gray-200 first:ml-0 first:pl-0 first:border-l-0">
                                                            {{ $attrName }}: {{ $attrValue }}
                                                        </p>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <p class="mt-1 text-sm font-medium text-gray-900">GH₵ {{ number_format($item['price_at_add'], 2) }}</p>
                                        </div>

                                        <div class="mt-4 sm:mt-0 sm:pr-9">
                                            <form action="{{ route('cart.update') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="cart_item_id" value="{{ $cartItemId }}">
                                                <label for="quantity-{{ $cartItemId }}" class="sr-only">Quantity, {{ $item['name'] }}</label>
                                                <div class="flex items-center">
                                                    <input type="number" name="quantity" id="quantity-{{ $cartItemId }}" value="{{ $item['quantity'] }}" min="1" max="99" {{-- Add max stock later --}}
                                                           class="w-16 text-center border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
                                                    <button type="submit" class="ml-2 text-xs text-pink-600 hover:text-pink-800">Update</button>
                                                </div>
                                            </form>

                                            <div class="absolute top-0 right-0">
                                                <form action="{{ route('cart.remove') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="cart_item_id" value="{{ $cartItemId }}">
                                                    <button type="submit" class="-m-2 p-2 inline-flex text-gray-400 hover:text-pink-500">
                                                        <span class="sr-only">Remove</span>
                                                        <x-heroicon-o-x-mark class="h-5 w-5" aria-hidden="true" />
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-4 flex text-sm text-gray-700 space-x-2">
                                        {{-- <x-heroicon-s-check class="flex-shrink-0 h-5 w-5 text-green-500" aria-hidden="true" /> --}}
                                        {{-- <span>In stock</span> --}}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-6">
                        <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear your cart?');">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-pink-600 hover:text-pink-500">
                                <x-heroicon-o-trash class="w-4 h-4 inline-block mr-1" /> Clear entire cart
                            </button>
                        </form>
                    </div>
                </section>

                <!-- Order summary -->
                <section aria-labelledby="summary-heading" class="mt-16 bg-pink-50 rounded-lg px-4 py-6 sm:p-6 lg:p-8 lg:mt-0 lg:col-span-5 shadow-xl">
                    <h2 id="summary-heading" class="text-lg font-medium text-gray-900">Order summary</h2>

                    <dl class="mt-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-600">Subtotal</dt>
                            <dd class="text-sm font-medium text-gray-900">GH₵ {{ number_format($subtotal, 2) }}</dd>
                        </div>
                        {{-- Shipping and Taxes will be added later --}}
                        {{-- <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                            <dt class="flex items-center text-sm text-gray-600">
                                <span>Shipping estimate</span>
                                <a href="#" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Learn more about how shipping is calculated</span>
                                    <x-heroicon-s-question-mark-circle class="h-5 w-5" aria-hidden="true" />
                                </a>
                            </dt>
                            <dd class="text-sm font-medium text-gray-900">GH₵ 5.00</dd>
                        </div>
                        <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                            <dt class="flex text-sm text-gray-600">
                                <span>Tax estimate</span>
                                <a href="#" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Learn more about how tax is calculated</span>
                                    <x-heroicon-s-question-mark-circle class="h-5 w-5" aria-hidden="true" />
                                </a>
                            </dt>
                            <dd class="text-sm font-medium text-gray-900">GH₵ 8.32</dd>
                        </div> --}}
                        <div class="border-t border-pink-200 pt-4 flex items-center justify-between">
                            <dt class="text-base font-medium text-gray-900">Order total</dt>
                            <dd class="text-base font-medium text-gray-900">GH₵ {{ number_format($subtotal, 2) }}</dd> {{-- Update when shipping/tax added --}}
                        </div>
                    </dl>

                    <div class="mt-6">
                        <a href="#" {{-- TODO: Link to checkout page route('checkout.index') --}}
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
                <p class="mt-2 text-base text-gray-500">Looks like you haven't added anything to your cart yet. <br>Start shopping to fill it up!</p>
                <div class="mt-8">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-base font-medium rounded-lg text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                        <x-heroicon-s-shopping-bag class="-ml-1 mr-2 h-5 w-5" />
                        Start Shopping
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>