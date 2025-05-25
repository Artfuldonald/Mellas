<x-admin-layout :title="'Customer: ' . $customer->name">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header & Action Buttons --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <h1 class="text-2xl font-semibold text-gray-900">Customer: {{ $customer->name }}</h1>
            <div class="flex items-center space-x-3 flex-wrap">
                 <a href="{{ route('admin.customers.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 whitespace-nowrap">
                    ← Back to Customers
                </a>
                 <a href="{{ route('admin.customers.edit', $customer) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 whitespace-nowrap">
                    Edit Customer
                </a>
                {{-- Password Reset Button/Form --}}
                <form action="{{ route('admin.customers.send_reset_link', $customer) }}" method="POST" class="inline-block" onsubmit="return confirm('Send password reset link to {{ $customer->email }}?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-md bg-yellow-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-600 whitespace-nowrap">
                         <x-heroicon-o-key class="-ml-0.5 mr-1.5 h-5 w-5" />
                        Send Reset Link
                    </button>
                </form>
            </div>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Customer Details Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left Column (Details, Addresses Placeholder) --}}
            <div class="lg:col-span-1 space-y-6">
                 {{-- Customer Info --}}
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Details</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->name }}</dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->email }}</dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Registered</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                             <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $customer->email_verified_at ? $customer->email_verified_at->format('M d, Y H:i') : 'No' }}
                                </dd>
                            </div>
                             <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Total Orders</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->orders_count }}</dd>
                            </div>
                             <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->is_admin ? 'Administrator' : 'Customer' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                 {{-- Addresses Placeholder --}}
                 <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Addresses</h3>
                        {{-- Add link to manage addresses if implemented later --}}
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                        <p class="text-sm text-gray-500">Address management section (requires Address model).</p>
                        {{-- Display addresses from orders or dedicated Address model here --}}
                    </div>
                 </div>

            </div>

            {{-- Right Column (Order History) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Order History --}}
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Order History</h3>
                        <a href="{{ route('admin.orders.index', ['search' => $customer->email]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            View All Orders →
                        </a>
                    </div>
                    <div class="border-t border-gray-200 overflow-x-auto">
                         @if($customer->orders->isEmpty())
                            <p class="p-6 text-sm text-gray-500">This customer has not placed any orders yet.</p>
                         @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($customer->orders as $order)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">{{ $order->order_number }}</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($order->status) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($order->payment_status) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($order->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>