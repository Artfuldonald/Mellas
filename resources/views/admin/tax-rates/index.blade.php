<x-admin-layout title="Tax Rates">
    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="md:flex md:items-center md:justify-between md:space-x-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-semibold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Tax Rates</h1>
            </div>
            <div class="flex">
                <a href="{{ route('admin.tax-rates.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-o-plus class="-ml-0.5 mr-1.5 h-5 w-5" />
                    Add Tax Rate
                </a>
            </div>
        </div>
        @include('admin.partials._session_messages')
        {{-- Add Filters if needed later --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                @if($taxRates->isEmpty())
                    <div class="p-6 text-center text-gray-500">
                        No tax rates have been created yet. <a href="{{ route('admin.tax-rates.create') }}" class="text-indigo-600 hover:underline">Add one now</a>.
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate (%)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applies to Shipping</th> --}}
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($taxRates as $rate)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="{{ route('admin.tax-rates.edit', $rate) }}" class="text-indigo-600 hover:text-indigo-900">{{ $rate->name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($rate->rate * 100, 2) }}%</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $rate->priority }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $rate->is_active,
                                        'bg-red-100 text-red-800' => !$rate->is_active,
                                    ])>
                                        {{ $rate->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $rate->apply_to_shipping ? 'Yes' : 'No' }}</td> --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('admin.tax-rates.edit', $rate) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit Rate">Edit</a>
                                    <form action="{{ route('admin.tax-rates.destroy', $rate) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this tax rate?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete Rate">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($taxRates->hasPages())
                        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            {{ $taxRates->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>