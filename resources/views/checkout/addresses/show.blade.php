{{-- views/checkout/addresses/show.blade.php --}}
<x-account-layout title="Address Book">
    <div class="bg-white rounded-lg shadow-md">
        
        {{-- Card Header --}}
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Address Book</h2>
                {{-- This link will now point to the correct route based on context --}}
                <a href="{{ request()->routeIs('checkout.*') ? route('checkout.addresses.create') : route('profile.addresses.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-pink-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-heroicon-o-plus class="-ml-1 mr-2 h-5 w-5"/>
                    Add New Address
                </a>
            </div>
        </div>

        {{-- Address List --}}
        <div class="p-2 sm:p-4">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <ul role="list" class="divide-y divide-gray-200">
                @forelse($addresses as $address)
                    <li class="p-4 hover:bg-gray-50/50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $address->first_name }} {{ $address->last_name }}</p>
                                    @if($address->is_default)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                            Default
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-gray-500 truncate">{{ $address->getFormattedAddressAttribute() }}</p>
                                <p class="mt-1 text-sm text-gray-500 truncate">{{ $address->phone }}</p>
                            </div>

                            <div class="ml-4 flex-shrink-0 flex items-center space-x-4">
                                <a href="{{ request()->routeIs('checkout.*') ? route('checkout.addresses.edit', $address->id) : route('profile.addresses.edit', $address->id) }}" class="text-sm font-medium text-gray-500 hover:text-pink-600">
                                    Edit
                                </a>
                                @if(session('address_context') === 'checkout')
                                    <form action="{{ route('checkout.addresses.select') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="address_id" value="{{ $address->id }}">
                                        <button type="submit" class="font-medium text-pink-600 hover:text-pink-500">
                                            Use this address
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="p-6 text-center text-gray-500">
                        <p>You have no saved addresses yet.</p>
                    </li>
                @endforelse
            </ul>
            <div class="mt-6">
                @if(session('address_context') === 'checkout')
                    <a href="{{ route('checkout.index') }}" class="text-sm font-medium text-pink-600 hover:text-pink-500">
                        ← Back to Checkout Summary
                    </a>
                @else
                    <a href="{{ route('profile.overview') }}" class="text-sm font-medium text-pink-600 hover:text-pink-500">
                        ← Back to Account Overview
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-account-layout>