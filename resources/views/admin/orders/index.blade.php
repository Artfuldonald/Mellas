{{-- resources/views/admin/orders/index.blade.php --}}
<x-admin-layout title="Orders">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header --}}
        <div class="md:flex md:items-center md:justify-between md:space-x-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-semibold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Orders</h1>
            </div>
            {{-- Add Create button if needed later --}}
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')  

        {{-- Filters --}}
        <div class="bg-white shadow sm:rounded-lg p-4">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Order #, Customer..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Order Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                 <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700">Payment Status</label>
                    <select name="payment_status" id="payment_status" class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        @foreach($paymentStatuses as $pStatus)
                            <option value="{{ $pStatus }}" {{ request('payment_status') == $pStatus ? 'selected' : '' }}>{{ ucfirst($pStatus) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Filter</button>
                    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Clear</a>
                </div>
            </form>
        </div>


        {{-- Orders Table Card --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                @if($orders->isEmpty())
                    <div class="p-6 text-center text-gray-500">
                        No orders found matching your criteria.
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($orders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    <a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->user->name ?? 'Guest' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{-- Add badge styling based on status --}}
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @switch($order->status)
                                            @case(App\Models\Order::STATUS_PENDING) bg-yellow-100 text-yellow-800 @break
                                            @case(App\Models\Order::STATUS_PROCESSING) bg-blue-100 text-blue-800 @break
                                            @case(App\Models\Order::STATUS_SHIPPED) bg-cyan-100 text-cyan-800 @break
                                            @case(App\Models\Order::STATUS_DELIVERED) bg-green-100 text-green-800 @break
                                            @case(App\Models\Order::STATUS_CANCELLED)
                                            @case(App\Models\Order::STATUS_REFUNDED) bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @switch($order->payment_status)
                                            @case(App\Models\Order::PAYMENT_PAID) bg-green-100 text-green-800 @break
                                            @case(App\Models\Order::PAYMENT_PENDING) bg-yellow-100 text-yellow-800 @break
                                            @case(App\Models\Order::PAYMENT_FAILED) bg-red-100 text-red-800 @break
                                            @case(App\Models\Order::PAYMENT_REFUNDED) bg-purple-100 text-purple-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900" title="View Details">View</a>
                                    <a href="{{ route('admin.orders.edit', $order) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit Status">Edit</a>
                                    {{-- Add Delete button if needed --}}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Pagination Links --}}
                    @if ($orders->hasPages())
                        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>