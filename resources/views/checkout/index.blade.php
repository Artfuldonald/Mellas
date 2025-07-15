<x-app-layout title="Confirm Checkout">
    <div class="bg-white min-h-screen">

        <!-- Go back link -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <a href="{{ route('cart.index') }}" class="flex items-center text-blue-600 hover:text-blue-800 text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Go back & continue shopping
            </a>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Checkout Steps -->
                <div class="lg:col-span-2 space-y-4">

                    <!-- Step 1: Customer Address (Dynamic) -->
                    <div class="bg-white border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                 @if($isAddressStepComplete)
                                    <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                @else
                                    <div class="w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center text-white text-xs font-bold">1</div>
                                @endif
                                <span class="text-sm font-normal text-gray-900">1. CUSTOMER ADDRESS</span>
                            </div>
                            <a href="{{ route('checkout.addresses.show') }}" class="text-blue-600 hover:text-blue-800 text-sm">Change ></a>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="text-sm text-gray-900 font-medium">{{ $selectedAddress->first_name }} {{ $selectedAddress->last_name }}</div>
                            <div class="text-xs text-gray-600">{{ $selectedAddress->getFormattedAddressAttribute() }} | {{ $selectedAddress->phone }}</div>
                        </div>
                    </div>

                    <!-- Step 2: Delivery Details (Dynamic) -->
                    {{-- This section is now just a summary of items. The UI implies a multi-step form, but for a single-page checkout, this is the final confirmation. --}}
                    <div class="bg-white border border-gray-200 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center text-white text-xs font-bold">2</div>
                                <span class="text-sm font-normal text-gray-900">2. DELIVERY ITEMS</span>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-3 space-y-3">
                            <!-- Items Loop (Dynamic) -->
                            @forelse($cartState['items'] as $item)
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="flex items-center space-x-2">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->display_name }}" class="w-8 h-8 rounded object-cover">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs text-gray-900 truncate">{{ $item->display_name }}</div>
                                        <div class="text-xs text-gray-600">QTY: {{ $item->quantity }}</div>
                                    </div>
                                    <div class="text-xs text-gray-800 font-medium">
                                        GH₵ {{ number_format($item->line_total, 2) }}
                                    </div>
                                </div>
                            </div>
                            @empty
                                <div class="text-center text-sm text-gray-500 py-4">No items in your cart.</div>
                            @endforelse                          
                            <div class="text-center pt-2">
                                <a href="{{ route('cart.index') }}" class="text-sm font-medium text-pink-600 hover:text-pink-700 no-underline transition-colors">
                                    Edit Cart »
                                </a>
                            </div>
                        </div>
                    </div>
                   
                <!-- Step 3: Payment Method -->
            <div class="bg-white border border-gray-200 rounded-lg" x-data="{ editingPayment: false }">
                {{-- Collapsed View (when not editing) --}}
                <div class="p-3" x-show="!editingPayment" x-transition>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            @if($isPaymentStepComplete)
                                <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            @else
                                <div class="w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center text-white text-xs font-bold">3</div>
                            @endif
                            <span class="text-sm font-normal text-gray-900">3. PAYMENT METHOD</span>
                        </div>
                        <button @click="editingPayment = true" class="text-blue-600 hover:text-blue-800 text-sm">Change »</button>
                    </div>
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-900 font-medium">{{ $availablePaymentMethods[$selectedPaymentMethod] ?? 'Not Selected' }}</span>
                            @if($selectedPaymentMethod === 'mtn_momo')
                                <div class="bg-yellow-400 text-black text-xs font-bold px-2 py-0.5 rounded">MTN</div>
                            @elseif($selectedPaymentMethod === 'cash_on_delivery')
                                <x-heroicon-o-truck class="w-5 h-5 text-gray-500" />
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Expanded/Editing View --}}
                <div class="p-3" x-show="editingPayment" x-transition style="display: none;">
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center text-white text-xs font-bold">3</div>
                        <span class="text-sm font-normal text-gray-900">3. SELECT PAYMENT METHOD</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                        <form action="{{ route('checkout.payment.select') }}" method="POST" id="payment-method-form">
                            @csrf
                            <div class="space-y-4">
                                @foreach($availablePaymentMethods as $key => $name)
                                    <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all has-[:checked]:border-pink-500 has-[:checked]:bg-pink-50 border-gray-200 hover:border-pink-300">
                                        <input type="radio" name="payment_method" value="{{ $key }}" class="h-4 w-4 text-pink-600 focus:ring-pink-500"
                                            {{ $selectedPaymentMethod === $key ? 'checked' : '' }}>
                                        <span class="ml-3 text-sm font-medium text-gray-900">{{ $name }}</span>
                                    </label>
                                @endforeach
                            </div>                
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                                <button type="button" @click="editingPayment = false" class="text-sm text-gray-600 hover:underline">
                                    Cancel
                                </button>
                                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-pink-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-700">
                                    Confirm Payment Method
                                </button>
                            </div>              
                        </form>
                    </div>
                </div>
            </div>

                </div>

                <!-- Right Column - Order Summary (Dynamic) -->
                <div class="lg:col-span-1">
                    <div class="bg-white border border-gray-200 rounded-lg p-4 sticky top-4">
                        <h3 class="text-sm font-normal text-gray-900 mb-4">Order Summary</h3>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal ({{ $cartState['item_count'] }} items)</span>
                                <span class="text-gray-900">GH₵ {{ number_format($cartState['totals']['subtotal'], 2) }}</span>
                            </div>

                            @if($cartState['totals']['discount'] > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Discount ({{ $cartState['totals']['applied_discount']->code }})</span>
                                <span>- GH₵ {{ number_format($cartState['totals']['discount'], 2) }}</span>
                            </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery fees</span>
                                <span class="text-gray-900">GH₵ {{ number_format($cartState['totals']['shipping'], 2) }}</span>
                            </div>

                             <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span class="text-gray-900">GH₵ {{ number_format($cartState['totals']['tax'], 2) }}</span>
                            </div>

                            <div class="border-t border-gray-200 pt-2">
                                <div class="flex justify-between font-bold text-base">
                                    <span class="text-gray-900">Total</span>
                                    <span class="text-gray-900">GH₵ {{ number_format($cartState['totals']['grandTotal'], 2) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- This section can be used to apply a voucher code --}}
                        @if(!session('cart.discount_code'))
                            <div class="mt-4 bg-pink-50 border border-pink-200 rounded p-3">
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-pink-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                    <div class="text-xs text-pink-800">
                                        Have a voucher? Apply it on the cart page.
                                    </div>
                                </div>
                            </div>
                        @endif
                   
                        @if($isCheckoutReady)                           
                            <a href="{{ route('checkout.payment.show') }}" 
                            class="block text-center w-full bg-pink-500 hover:bg-pink-600 text-white text-sm py-2.5 px-4 rounded-lg mt-4 transition-colors">
                                Confirm and Place Order
                            </a>
                        @else                          
                            <button type="button" disabled class="w-full bg-gray-300 ...">
                                Confirm and Place Order
                            </button>
                            <p class="text-xs text-red-600 text-center mt-2">Please complete all steps to continue.</p>
                        @endif

                        <div class="mt-3 text-xs text-gray-600 text-center">
                            By proceeding, you are automatically accepting the
                            <a href="#" class="text-blue-600 hover:text-blue-800">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</x-app-layout>