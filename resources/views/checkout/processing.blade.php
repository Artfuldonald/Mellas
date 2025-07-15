{{-- resources/views/checkout/processing.blade.php --}}
<x-app-layout title="Confirming Your Payment">

    @php
        // This check is a safeguard. If someone lands here for a non-pending order,
        // we'll treat it as successful to avoid getting them stuck.
        $isPending = $order->payment_method === 'mtn_momo' && $order->status === \App\Models\Order::STATUS_PENDING;
    @endphp

    <div x-data="paymentStatusChecker({
            orderId: {{ $order->id }},
            initialStatus: '{{ $isPending ? 'pending' : 'successful' }}'
         })"
         class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

        <div class="max-w-md w-full">

            {{-- PENDING STATE (The JumiaPay Screen) --}}
            <template x-if="status === 'pending'">
                <div class="bg-white p-8 rounded-xl shadow-lg text-center space-y-4">
                    {{-- Animated Spinner --}}
                    <div class="mx-auto w-16 h-16 border-4 border-pink-500 border-t-transparent rounded-full animate-spin"></div>

                    <h1 class="text-2xl font-bold text-gray-800">Confirm Payment on Your Phone</h1>
                    <p class="text-gray-600">
                        A prompt has been sent to your phone to approve the payment of
                        <strong class="font-semibold text-gray-900">GH₵ {{ number_format($order->total_amount, 2) }}</strong>.
                        <br>
                        This page will update automatically once you approve.
                    </p>

                    <div class="pt-4">
                        <a href="{{ route('checkout.cancel') }}" class="text-sm text-gray-500 hover:underline">Cancel Payment</a>
                    </div>
                </div>
            </template>

            {{-- SUCCESS STATE --}}
            <template x-if="status === 'successful'">
                <div class="bg-white p-8 rounded-xl shadow-lg text-center space-y-4">
                    <div class="w-20 h-20 mx-auto flex items-center justify-center bg-green-100 rounded-full">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800">Payment Successful!</h1>
                    <p class="text-gray-600" x-text="message"></p>
                    <a href="{{ route('orders.show', $order) }}" class="inline-block mt-4 px-6 py-2 text-sm font-medium text-white bg-pink-600 rounded-md hover:bg-pink-700">
                        View Your Order
                    </a>
                </div>
            </template>

            {{-- FAILED STATE --}}
            <template x-if="status === 'failed'">
                <div class="bg-white p-8 rounded-xl shadow-lg text-center space-y-4">
                    <div class="w-20 h-20 mx-auto flex items-center justify-center bg-red-100 rounded-full">
                        <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800">Payment Failed</h1>
                    <p class="text-gray-600" x-text="message"></p>
                    <a href="{{ route('checkout.index') }}" class="inline-block mt-4 px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Try Again
                    </a>
                </div>
            </template>

        </div>
    </div>

    @push('scripts')
    <script>
        // This is our Alpine.js component for managing the page state
        function paymentStatusChecker(config) {
            return {
                orderId: config.orderId,
                status: config.initialStatus,
                message: 'Your order has been confirmed. Redirecting...',
                pollingInterval: null,
                timeout: null,

                init() {
                    // Only start polling if the initial status is 'pending'
                    if (this.status !== 'pending') {
                        this.status = 'successful'; // Ensure it shows success for COD orders that land here
                        setTimeout(() => window.location.href = '{{ route("orders.show", $order) }}', 2000);
                        return;
                    }

                    // Stop polling after 3 minutes
                    this.timeout = setTimeout(() => {
                        this.stopPolling();
                        this.status = 'failed';
                        this.message = 'The payment request timed out. Please try again.';
                    }, 180000);

                    // Start polling every 3 seconds
                    this.pollingInterval = setInterval(() => this.checkStatus(), 3000);
                },

                stopPolling() {
                    clearInterval(this.pollingInterval);
                    clearTimeout(this.timeout);
                },

                checkStatus() {
                    const url = `{{ route('checkout.status', $order) }}`;
                    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(response => response.json())
                        .then(data => {
                            // Check the order status, which is updated by the webhook
                            if (data.status === '{{ \App\Models\Order::STATUS_PROCESSING }}') {
                                this.status = 'successful';
                                this.message = 'Payment confirmed! Redirecting to your order...';
                                this.stopPolling();
                                setTimeout(() => window.location.href = '{{ route("orders.show", $order) }}', 2000);
                            } else if (data.status === '{{ \App\Models\Order::STATUS_FAILED }}' || data.status === '{{ \App\Models\Order::STATUS_CANCELLED }}') {
                                this.status = 'failed';
                                this.message = 'Your payment could not be completed. Please try again.';
                                this.stopPolling();
                            }
                        })
                        .catch(error => console.error('Error checking payment status:', error));
                }
            }
        }
    </script>
    @endpush
</x-app-layout>