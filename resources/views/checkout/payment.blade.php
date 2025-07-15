{{-- resources/views/checkout/payment.blade.php --}}
<x-app-layout title="Complete Payment">
    {{-- This outer div creates the gray background and centers the content --}}
    <div class="bg-gray-100 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        
        {{-- This is the white, centered card --}}
        <div class="max-w-md w-full space-y-8 bg-white p-6 sm:p-8 rounded-2xl shadow-lg">
            
            {{-- Order Summary Header --}}
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider text-center">Total to Pay</p>
                <p class="mt-1 text-center text-4xl font-bold text-gray-900">
                    GH₵ {{ number_format($cartState['totals']['grandTotal'], 2) }}
                </p>
                <a href="{{ route('checkout.index') }}" class="mt-2 block text-center text-sm text-blue-600 hover:underline">
                    ← Go back and review order
                </a>
            </div>

            {{-- The Payment Form --}}
            <div class="border-t border-gray-200 pt-5">
                <form id="payment-form" action="{{ route('checkout.process') }}" method="POST">
                    @csrf
                    
                    {{-- This hidden input carries the selected payment method over --}}
                    <input type="hidden" name="payment_method" value="{{ $selectedPaymentMethod }}">

                    <h3 class="text-sm font-medium text-gray-500 mb-2">PAYING WITH</h3>
                    <div class="border rounded-lg p-4 flex items-center justify-between bg-gray-50">
                        <span class="font-semibold text-gray-800">{{ $availablePaymentMethods[$selectedPaymentMethod] ?? 'N/A' }}</span>
                        @if($selectedPaymentMethod === 'mtn_momo')
                            <div class="bg-yellow-400 text-black text-xs font-bold px-2 py-0.5 rounded">MTN</div>
                        @endif
                    </div>

                    {{-- Conditional Input Fields --}}
                    @if($selectedPaymentMethod === 'mtn_momo')
                        <div class="mt-6">
                            <label for="momo_phone" class="block text-sm font-medium text-gray-700">MTN MoMo Phone Number</label>
                            <div class="mt-1">
                                <input type="tel" name="momo_phone" id="momo_phone" required
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                       placeholder="e.g., 0241234567">
                                @error('momo_phone')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif

                    @if($selectedPaymentMethod === 'cash_on_delivery')
                        <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                            <p class="text-sm text-blue-700">You will pay the courier when your order arrives. No further action is needed.</p>
                        </div>
                    @endif

                    {{-- The Final "Pay Now" Button --}}
                    <div class="mt-8">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Pay Now: GH₵ {{ number_format($cartState['totals']['grandTotal'], 2) }}
                        </button>
                    </div>
                </form>
            </div>
            
             <div class="text-center text-xs text-gray-500 mt-4">
                 By tapping "Pay Now" you accept our <a href="#" class="underline">Terms & Conditions</a>.
            </div>
        </div>
    </div>
</x-app-layout>