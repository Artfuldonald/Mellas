<x-admin-layout title="Add Shipping Rate">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6 max-w-4xl mx-auto">
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            {{-- Show which zone we're adding to --}}
            <h1 class="text-2xl font-semibold text-gray-900">Add Rate to Zone: <span class="text-indigo-600">{{ $shippingZone->name }}</span></h1>
             {{-- Link back to the parent zone's show page --}}
            <a href="{{ route('admin.shipping-zones.show', $shippingZone) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Zone Details
            </a>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Create Rate Form Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            {{-- Form posts to the storeRate route for this zone --}}
            <form action="{{ route('admin.shipping-zones.rates.store', $shippingZone) }}" method="POST">
                @csrf

                <div class="px-4 py-5 sm:p-6 space-y-6">
                    {{-- Rate Name --}}
                    <div>
                        <x-input-label for="name" :value="__('Rate Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus placeholder="e.g., Standard Delivery, Express Post"/>
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        <p class="mt-1 text-xs text-gray-500">The name shown to customers (e.g., Standard, Express).</p>
                    </div>

                    {{-- Cost --}}
                    <div>
                        <x-input-label for="cost" :value="__('Cost ($)')" />
                         <div class="relative mt-1 rounded-md shadow-sm">
                             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                 <span class="text-gray-500 sm:text-sm">$</span>
                             </div>
                            <x-text-input id="cost" name="cost" type="number" step="0.01" min="0" class="block w-full rounded-md pl-7 pr-12 sm:text-sm" :value="old('cost', '0.00')" required placeholder="0.00"/>
                             <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                 <span class="text-gray-500 sm:text-sm" id="currency-symbol">USD</span> {{-- Adjust currency if needed --}}
                            </div>
                         </div>
                        <x-input-error class="mt-2" :messages="$errors->get('cost')" />
                         <p class="mt-1 text-xs text-gray-500">Enter 0 for free shipping.</p>
                    </div>

                    {{-- Description --}}
                     <div>
                        <x-input-label for="description" :value="__('Description (Optional)')" />
                         <textarea id="description" name="description" rows="3"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="e.g., Delivery within 3-5 business days"
                         >{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                         <p class="mt-1 text-xs text-gray-500">Optional text shown to the customer during checkout.</p>
                    </div>

                    {{-- Placeholder for Rate Criteria --}}
                     {{--
                    <div class="border-t pt-4 mt-4">
                         <h3 class="text-md font-medium text-gray-800">Rate Conditions (Optional)</h3>
                         <p class="mt-1 text-sm text-gray-500">Set conditions like minimum order value or weight limits. Feature coming soon.</p>
                         {{-- Add inputs for min_order_subtotal etc. here --}}
                    </div>
                    --}}

                     {{-- Active Status --}}
                    <div class="relative flex items-start pt-4 border-t">
                        <div class="flex h-6 items-center">
                            <input id="is_active" name="is_active" type="checkbox" value="1"
                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="is_active" class="font-medium text-gray-900">Active</label>
                            <p class="text-gray-500">Make this shipping rate available for selection.</p>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3">
                     <a href="{{ route('admin.shipping-zones.show', $shippingZone) }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <x-primary-button>
                         {{ __('Add Shipping Rate') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>