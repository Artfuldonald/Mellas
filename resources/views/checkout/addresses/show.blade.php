<x-app-layout title="Select Shipping Address">
    <div class="bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Select a Shipping Address</h1>
                <a href="{{ route('checkout.addresses.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add New Address
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($addresses as $address)
                        <li class="p-4 sm:p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $address->first_name }} {{ $address->last_name }}</p>
                                        @if($address->is_default)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Default
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 truncate">{{ $address->getFormattedAddressAttribute() }}</p>
                                    <p class="mt-1 text-sm text-gray-500 truncate">{{ $address->phone }}</p>
                                </div>

                                {{-- ACTION BUTTONS --}}
                                <div class="ml-4 flex-shrink-0 flex items-center space-x-4">
                                    {{-- EDIT LINK (NEW) --}}
                                    <a href="{{ route('checkout.addresses.edit', $address->id) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                                        Edit
                                    </a>

                                    {{-- SELECTION FORM --}}
                                    <form action="{{ route('checkout.addresses.select') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="address_id" value="{{ $address->id }}">
                                        <button type="submit" class="font-medium text-indigo-600 hover:text-indigo-500">
                                            Use this address
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="p-6 text-center text-gray-500">
                            <p>You have not saved any addresses yet.</p>
                            <p class="mt-2">
                                <a href="{{ route('checkout.addresses.create') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                    Click here to add your first address.
                                </a>
                            </p>
                        </li>
                    @endforelse
                </ul>
            </div>
             <div class="mt-6">
                <a href="{{ route('checkout.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    <span aria-hidden="true">←</span>
                    Back to checkout
                </a>
            </div>
        </div>
    </div>
</x-app-layout>