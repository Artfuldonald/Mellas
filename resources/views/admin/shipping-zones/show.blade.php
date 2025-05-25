<x-admin-layout :title="'Shipping Zone: ' . $shippingZone->name">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Shipping Zone: {{ $shippingZone->name }}</h1>
            <a href="{{ route('admin.shipping-zones.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Shipping Zones
            </a>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Zone Details (Read Only) --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
             <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                 <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                    <div class="ml-4 mt-2">
                         <h3 class="text-lg leading-6 font-medium text-gray-900">Zone Details</h3>
                    </div>
                    <div class="ml-4 mt-2 flex-shrink-0">
                        <a href="{{ route('admin.shipping-zones.edit', $shippingZone) }}" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Edit Zone</a>
                    </div>
                 </div>
             </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Zone Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $shippingZone->name }}</dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                             <span @class([
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                'bg-green-100 text-green-800' => $shippingZone->is_active,
                                'bg-red-100 text-red-800' => !$shippingZone->is_active,
                            ])>
                                {{ $shippingZone->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                     {{-- Display Zone Definition info here later --}}
                </dl>
            </div>
        </div>

        {{-- Shipping Rates within this Zone --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                 <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                    <div class="ml-4 mt-2">
                         <h3 class="text-lg leading-6 font-medium text-gray-900">Shipping Rates for {{ $shippingZone->name }}</h3>
                    </div>
                    <div class="ml-4 mt-2 flex-shrink-0">
                        {{-- Button to add a new rate TO THIS ZONE --}}
                        <a href="{{ route('admin.shipping-zones.rates.create', $shippingZone) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <x-heroicon-o-plus class="-ml-0.5 mr-1.5 h-5 w-5" />
                            Add Rate
                        </a>
                    </div>
                 </div>
             </div>
             <div class="overflow-x-auto">
                @if($shippingZone->shippingRates->isEmpty())
                     <div class="p-6 text-center text-gray-500">
                        No shipping rates defined for this zone yet. <a href="{{ route('admin.shipping-zones.rates.create', $shippingZone) }}" class="text-indigo-600 hover:underline">Add one</a>.
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                         <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                {{-- Add columns for other criteria later --}}
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($shippingZone->shippingRates as $rate)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $rate->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($rate->cost, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span @class([
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            'bg-green-100 text-green-800' => $rate->is_active,
                                            'bg-red-100 text-red-800' => !$rate->is_active,
                                        ])>
                                            {{ $rate->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $rate->description ?: '--' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        {{-- Link to edit THIS specific rate for THIS zone --}}
                                        <a href="{{ route('admin.shipping-zones.rates.edit', [$shippingZone, $rate]) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit Rate">Edit</a>
                                        {{-- Form to delete THIS specific rate --}}
                                        <form action="{{ route('admin.shipping-zones.rates.destroy', [$shippingZone, $rate]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this rate?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete Rate">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
             </div>
        </div>

    </div>
</x-admin-layout>