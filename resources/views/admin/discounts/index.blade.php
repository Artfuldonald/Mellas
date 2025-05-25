<x-admin-layout title="Discount Codes">
    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="md:flex md:items-center md:justify-between md:space-x-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-semibold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Discount Codes</h1>
            </div>
            <div class="flex">
                <a href="{{ route('admin.discounts.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-o-plus class="-ml-0.5 mr-1.5 h-5 w-5" />
                    Add Discount
                </a>
            </div>
        </div>
        @include('admin.partials._session_messages')
        {{-- Add Filters if needed later --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                @if($discounts->isEmpty())
                    <div class="p-6 text-center text-gray-500">
                        No discount codes have been created yet. <a href="{{ route('admin.discounts.create') }}" class="text-indigo-600 hover:underline">Add one now</a>.
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($discounts as $discount)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="{{ route('admin.discounts.edit', $discount) }}" class="text-indigo-600 hover:text-indigo-900 font-mono">{{ $discount->code }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $discount->type)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $discount->formatted_value }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $discount->times_used }} / {{ $discount->max_uses ?: '∞' }}
                                    @if($discount->max_uses_per_user)
                                        <span class="text-xs block text-gray-400">(Max {{ $discount->max_uses_per_user }}/user)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($discount->starts_at) Start: {{ $discount->starts_at->format('M d, Y H:i') }}<br>@endif
                                    @if($discount->expires_at) Expires: {{ $discount->expires_at->format('M d, Y H:i') }} @endif
                                    @if(!$discount->starts_at && !$discount->expires_at) Always active @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $discount->is_active,
                                        'bg-red-100 text-red-800' => !$discount->is_active,
                                    ])>
                                        {{ $discount->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('admin.discounts.edit', $discount) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit Discount">Edit</a>
                                    <form action="{{ route('admin.discounts.destroy', $discount) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this discount code?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete Discount">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($discounts->hasPages())
                        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            {{ $discounts->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>