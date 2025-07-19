{{-- resources/views/checkout/addresses-create.blade.php --}}
<x-account-layout title="Add New Address">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Add a New Shipping Address</h2>
            <p class="mt-1 text-sm text-gray-600">This address will be saved to your address book.</p>
        </div>
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="ml-3"><h3 class="text-sm font-medium text-red-800">Please fix the errors below:</h3></div>
                </div>
            @endif
                    <form action="{{ request()->routeIs('checkout.*') ? route('checkout.addresses.store') : route('profile.addresses.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First name</label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', auth()->user()->first_name) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2"/>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last name</label>
                                <input type="text" name="last_name" id="last_name" value="{{ old('last_name', auth()->user()->last_name) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2"/>
                            </div>

                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                                <div class="mt-1">
                                    <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                             <div class="sm:col-span-2">
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <div class="mt-1">
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="gps_address" class="block text-sm font-medium text-gray-700">Address / GPS Address</label>
                                <div class="mt-1">
                                    <input type="text" name="gps_address" id="gps_address" value="{{ old('gps_address') }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                             <div class="sm:col-span-2">
                                <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                <div class="mt-1">
                                    <input type="text" name="city" id="city" value="{{ old('city') }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="region" class="block text-sm font-medium text-gray-700">State / Region</label>
                                <div class="mt-1">
                                    <input type="text" name="state" id="state" value="{{ old('state') }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="postal_code" class="block text-sm font-medium text-gray-700">ZIP / Postal code</label>
                                <div class="mt-1">
                                    <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            {{-- Hidden country field, assuming Ghana for now --}}
                            <input type="hidden" name="country" value="GH">

                        </div>

                        <div class="flex items-center pt-5 border-t border-gray-200">
                              @if(Str::contains(url()->previous(), 'checkout'))
                                <a href="{{ route('checkout.addresses.show') }}" class="text-sm font-medium text-pink-600 hover:text-pink-500">
                                    <span aria-hidden="true">←</span> Back to Select Address
                                </a>
                            @else
                                <a href="{{ route('profile.addresses.show') }}" class="text-sm font-medium text-pink-600 hover:text-pink-500">
                                    <span aria-hidden="true">←</span> Back to Address Book
                                </a>
                            @endif
                            <button type="submit" class="ml-auto inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save and use this address
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-account-layout>