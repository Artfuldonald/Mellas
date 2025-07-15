<x-app-layout title="Checkout Cancelled">
    <div class="bg-pink-50/50 min-h-screen py-16">
        <div class="container mx-auto px-4 max-w-2xl text-center">
            <div class="bg-white rounded-xl shadow-lg p-8">
                <!-- Cancel Icon -->
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-800 mb-4">Checkout Cancelled</h1>
                <p class="text-gray-600 mb-6">Your checkout process was cancelled. Your cart items are still saved.</p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('cart.index') }}" class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 transition-colors">
                        Return to Cart
                    </a>
                    <a href="{{ route('products.index') }}" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
