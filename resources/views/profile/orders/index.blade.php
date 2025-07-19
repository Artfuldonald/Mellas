<x-account-layout title="My Orders">
    <div class="bg-white rounded-lg shadow-md">
        
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">My Orders</h2>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse($orders as $order)
                <div class="p-6 hover:bg-gray-50/50">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Order #{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500 mt-1">Placed on {{ $order->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="mt-2 sm:mt-0">
                            {{-- This uses the getStatusClassAttribute() from your Order model --}}
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $order->status_class }}">
                                {{ Str::title($order->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-4 text-sm">
                        <p class="text-gray-600">Total: <span class="font-bold text-gray-800">GH₵ {{ number_format($order->total_amount, 2) }}</span></p>
                        <p class="text-gray-500">{{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}</p>
                    </div>
                    <div class="mt-4">
                        {{-- This links to your existing order detail page --}}
                        <a href="{{ route('orders.show', $order) }}" class="font-medium text-pink-600 hover:text-pink-500 text-sm">
                            View Details
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-gray-300"/>
                    <p class="mt-4">You have not placed any orders yet.</p>
                </div>
            @endforelse
        </div>

        @if($orders->hasPages())
            <div class="p-6 bg-gray-50 border-t">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-account-layout>