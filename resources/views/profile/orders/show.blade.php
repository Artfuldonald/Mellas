<x-account-layout :title="'Order Details #' . $order->order_number">
    <div class="space-y-6">
        {{-- Back Link and Order Header --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('profile.orders.index') }}" class="text-sm font-medium text-pink-600 hover:text-pink-500 flex items-center">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2"/>
                Back to My Orders
            </a>
            <div class="text-sm text-gray-500">
                Placed on: <span class="font-medium text-gray-700">{{ $order->created_at->format('M d, Y') }}</span>
            </div>
        </div>

        {{-- Main Order Details Card --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="text-xl font-semibold">Order #{{ $order->order_number }}</h2>
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $order->status_class }}">
                    {{ Str::title($order->status) }}
                </span>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Shipping Address --}}
                <div>
                    <h3 class="text-base font-semibold text-gray-800 mb-2">Shipping Address</h3>
                    <div class="text-sm text-gray-600 leading-relaxed">
                        @if(is_array($order->shipping_address))
                            <p class="font-medium">{{ $order->shipping_address['first_name'] ?? '' }} {{ $order->shipping_address['last_name'] ?? '' }}</p>
                            <p>{{ $order->shipping_address['gps_address'] ?? '' }}</p>
                            <p>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }}</p>
                            <p>{{ $order->shipping_address['phone'] ?? '' }}</p>
                        @endif
                    </div>
                </div>
                {{-- Payment Information --}}
                <div>
                    <h3 class="text-base font-semibold text-gray-800 mb-2">Payment Information</h3>
                    <div class="text-sm text-gray-600">
                        <p><strong>Method:</strong> {{ Str::title(str_replace('_', ' ', $order->payment_method)) }}</p>
                        <p><strong>Status:</strong> <span class="font-medium">{{ Str::title($order->payment_status) }}</span></p>
                    </div>
                </div>
            </div>

            {{-- Order Items List --}}
            <div class="border-t">
                <h3 class="p-6 text-base font-semibold text-gray-800">Items Ordered</h3>
                <div class="divide-y divide-gray-200">
                    @foreach($order->items as $item)
                        <div class="p-6 flex items-center space-x-4">
                            @if($item->product && $item->product->images->isNotEmpty())
                                <img src="{{ $item->product->images->first()->image_url }}" alt="{{ $item->product_name }}" class="w-16 h-16 object-cover rounded-md border">
                            @else
                                <div class="w-16 h-16 bg-gray-100 rounded-md flex items-center justify-center">
                                    <x-heroicon-o-photo class="w-8 h-8 text-gray-300"/>
                                </div>
                            @endif
                            <div class="flex-grow">
                                <p class="font-semibold text-gray-800">{{ $item->product_name }}</p>
                                @if($item->variant_name)
                                    <p class="text-xs text-gray-500">{{ $item->variant_name }}</p>
                                @endif
                                <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-sm font-semibold text-gray-800">
                                GH₵ {{ number_format($item->price * $item->quantity, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Order Summary --}}
            <div class="bg-gray-50/70 p-6 border-t rounded-b-lg">
                <div class="max-w-sm ml-auto space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-800 font-medium">GH₵ {{ number_format($order->subtotal, 2) }}</span>
                    </div>
                     <div class="flex justify-between">
                        <span class="text-gray-600">Shipping</span>
                        <span class="text-gray-800 font-medium">GH₵ {{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                     <div class="flex justify-between">
                        <span class="text-gray-600">Tax</span>
                        <span class="text-gray-800 font-medium">GH₵ {{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-green-600">
                            <span class="text-green-600">Discount</span>
                            <span class="text-green-600 font-medium">- GH₵ {{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-bold pt-2 border-t mt-2">
                        <span>Grand Total</span>
                        <span class="text-pink-600">GH₵ {{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-account-layout>