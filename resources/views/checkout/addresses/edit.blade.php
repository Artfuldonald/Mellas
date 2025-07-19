{{-- views/checkout/addresses/edit.blade.php --}}
<x-account-layout title="Edit Address">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b">
             <h2 class="text-xl font-semibold">Edit Shipping Address</h2>
        </div>
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
                     <div class="ml-3"><h3 class="text-sm font-medium text-red-800">Please fix the errors below:</h3></div>
                </div>
            @endif

            <form action="{{ request()->routeIs('checkout.*') ? route('checkout.addresses.update', $address->id) : route('profile.addresses.update', $address->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First name</label>
                                <div class="mt-1">
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $address->first_name) }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last name</label>
                                <div class="mt-1">
                                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $address->last_name) }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                                <div class="mt-1">
                                    <input id="email" name="email" type="email" value="{{ old('email', $address->email) }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                             <div class="sm:col-span-2">
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <div class="mt-1">
                                    <input type="text" name="phone" id="phone" value="{{ old('phone', $address->phone) }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="gps_address" class="block text-sm font-medium text-gray-700">Address / GPS Address</label>
                                <div class="mt-1">
                                    <input type="text" name="gps_address" id="gps_address" value="{{ old('gps_address', $address->gps_address) }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                             <div class="sm:col-span-2">
                                <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                <div class="mt-1">
                                    <input type="text" name="city" id="city" value="{{ old('city', $address->city) }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="state" class="block text-sm font-medium text-gray-700">State / Province</label>
                                <div class="mt-1">
                                    <input type="text" name="state" id="state" value="{{ old('state', $address->state) }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="postal_code" class="block text-sm font-medium text-gray-700">ZIP / Postal code</label>
                                <div class="mt-1">
                                    <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $address->postal_code) }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                            
                            {{-- Hidden country field, assuming Ghana for now --}}
                            <input type="hidden" name="country" value="GH">

                            <div class="sm:col-span-6">
                                <div class="flex items-center">
                                    <input id="is_default" name="is_default" type="checkbox" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="is_default" class="ml-2 block text-sm text-gray-900">Make this my default address</label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-5 mt-4 border-t border-gray-200">
                            <a href="{{ session('address_context') === 'checkout' ? route('checkout.addresses.show') : route('profile.addresses.show') }}" 
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                                    Cancel
                            </a>
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-account-layout>