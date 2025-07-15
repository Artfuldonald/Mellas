<x-app-layout title="Confirming Your Payment">

    @php
        // Determine the initial state based on the order passed from the controller
        $isMomoPending = $order->payment_method === 'mtn_momo' && $order->status === \App\Models\Order::STATUS_PENDING;
    @endphp

    {{-- 
        This Alpine.js component will manage the state of the page.
        - It starts in a 'pending' state if it's a MoMo payment.
        - It polls a route every few seconds to check for status updates.
        - It updates the UI without a page refresh.
    --}}
    <div x-data="paymentStatusChecker({ 
            orderId: {{ $order->id }}, 
            initialStatus: '{{ $isMomoPending ? 'pending' : 'successful' }}' 
         })"
         class="bg-gray-100 min-h-screen flex items-center justify-center py-12 px-4">
        
        <div class="max-w-lg w-full text-center">

            {{-- PENDING STATE (The JumiaPay Screen) --}}
            <template x-if="status === 'pending'">
                <div class="space-y-4">
                    {{-- Animated Spinner --}}
                    <div class="mx-auto w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    
                    <h1 class="text-2xl font-bold text-gray-800">We are waiting for you</h1>
                    <p class="text-gray-600">
                        Please follow the instructions on your phone to authorize the payment.
                        <br>
                        This page will update automatically. This may take up to 2 minutes.
                    </p>

                    {{-- Instructions Box --}}
                    <div class="mt-6 bg-white p-6 rounded-lg shadow-sm border text-left text-sm text-gray-700 space-y-2">
                        <p class="font-semibold">If you do not receive the prompt within 10 seconds, follow the instructions below:</p>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Dial <strong class="text-gray-900">*170#</strong> to see the main MTN USSD menu.</li>
                            <li>If the prompt appears instead, cancel it and dial <strong class="text-gray-900">*170#</strong> again.</li>
                            <li>Choose option <strong class="text-gray-900">6) My Wallet</strong>.</li>
                            <li>Choose option <strong class="text-gray-900">3) My Approvals</strong>.</li>
                            <li>Enter your PIN to proceed.</li>
                            <li>Look for the transaction and follow the prompts to authorize it.</li>
                            <li>You have 5 mins to authorize the transaction so if anything goes wrong, simply dial and try again.</li>
                        </ol>
                    </div>
                </div>
            </template>

            {{-- SUCCESS STATE --}}
            <template x-if="status === 'successful'">
                <div class="space-y-4 text-center">
                    {{-- Animated Checkmark --}}
                    <div class="w-24 h-24 mx-auto">
                        <svg class="w-full h-full text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800">Payment Successful!</h1>
                    <p class="text-gray-600">Your order has been confirmed. Thank you for your purchase.</p>
                    <p class="text-sm text-gray-500" x-text="message"></p>
                </div>
            </template>

            {{-- FAILED STATE --}}
            <template x-if="status === 'failed'">
                <div class="space-y-4 text-center">
                    {{-- Red X Icon --}}
                    <div class="w-24 h-24 mx-auto">
                        <svg class="w-full h-full text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
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
        function paymentStatusChecker(config) {
            return {
                orderId: config.orderId,
                status: config.initialStatus,
                message: 'Redirecting shortly...',
                pollingInterval: null,
                timeout: null,

                init() {
                    // If the payment is not pending (e.g., Cash on Delivery), do nothing.
                    if (this.status !== 'pending') {
                        // For non-pending orders, just show success and redirect.
                        this.status = 'successful';
                        this.message = 'Order Confirmed. Redirecting to your order details...';
                        setTimeout(() => {
                            window.location.href = '{{ route("orders.show", $order) }}';
                        }, 3000); // 3-second delay
                        return;
                    }

                    // Set a timeout to stop polling after 3 minutes (180 seconds)
                    this.timeout = setTimeout(() => {
                        this.stopPolling();
                        this.status = 'failed';
                        this.message = 'The payment request timed out. Please check your phone for an approval request or try again.';
                    }, 180000); // 3 minutes in milliseconds

                    // Start polling every 5 seconds
                    this.pollingInterval = setInterval(() => {
                        this.checkStatus();
                    }, 5000);
                },

                stopPolling() {
                    clearInterval(this.pollingInterval);
                    clearTimeout(this.timeout);
                },

                checkStatus() {
                    // The route needs the order ID, so we build the URL dynamically
                    const url = `{{ route('checkout.payment.status', '') }}/${this.orderId}`;
                    
                    fetch(url, { headers: { 'Accept': 'application/json' } })
                        .then(response => response.json())
                        .then(data => {
                            // Check the status from the server response
                            if (data.status === 'processing' || data.status === 'paid' || data.status === 'successful') {
                                this.status = 'successful';
                                this.message = 'Redirecting to your order details...';
                                this.stopPolling();
                                // Redirect after 2 seconds to let the user see the checkmark
                                setTimeout(() => {
                                    window.location.href = '{{ route("orders.show", $order) }}';
                                }, 2000);
                            } else if (data.status === 'failed' || data.status === 'cancelled') {
                                this.status = 'failed';
                                this.message = 'Your payment was not successful. Please try again.';
                                this.stopPolling();
                            }
                            // If status is still 'pending', do nothing and let the interval run again.
                        })
                        .catch(error => {
                            console.error('Error checking payment status:', error);
                            // Optional: stop polling on network error
                            // this.stopPolling(); 
                        });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>