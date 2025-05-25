{{-- resources/views/admin/orders/edit.blade.php --}}
<x-admin-layout :title="'Edit Order #' . $order->order_number">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Order #{{ $order->order_number }}</h1>
            <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Order Details
            </a>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Edit Form Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="px-4 py-5 sm:p-6 space-y-6">
                    {{-- Order Status --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Order Status</label>
                        <select id="status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm @error('status') border-red-500 @enderror">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ old('status', $order->status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tracking Number --}}
                    <div>
                        <label for="tracking_number" class="block text-sm font-medium text-gray-700">Tracking Number</label>
                        <input type="text" name="tracking_number" id="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('tracking_number') border-red-500 @enderror">
                        @error('tracking_number') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Enter tracking number if status is 'Shipped'.</p>
                    </div>

                     {{-- Admin Notes --}}
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Admin Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('notes') border-red-500 @enderror">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Internal notes about this order (not visible to customer).</p>
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <button type="submit" class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Update Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>