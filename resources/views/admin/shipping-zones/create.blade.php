{{-- resources/views/admin/shipping-zones/create.blade.php --}}
<x-admin-layout title="Add Shipping Zone">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6 max-w-4xl mx-auto">
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Add New Shipping Zone</h1>
            <a href="{{ route('admin.shipping-zones.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Shipping Zones
            </a>
        </div>

        {{-- Session Messages (Keep outside form) --}}
        @include('admin.partials._session_messages')

        {{-- Create Form Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <form action="{{ route('admin.shipping-zones.store') }}" method="POST">
                @csrf {{-- Keep CSRF in the main form tag --}}

                {{-- Include the form partial --}}
                {{-- Pass a new ShippingZone instance so $shippingZone exists in the partial --}}
                @include('admin.shipping-zones._form', ['shippingZone' => new \App\Models\ShippingZone()])

                {{-- Action Buttons --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3">
                     <a href="{{ route('admin.shipping-zones.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <x-primary-button>
                         {{ __('Create Zone') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>