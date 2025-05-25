{{-- resources/views/admin/shipping-zones/edit.blade.php --}}
<x-admin-layout :title="'Edit Shipping Zone: ' . $shippingZone->name">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6 max-w-4xl mx-auto">
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Shipping Zone</h1>
            <a href="{{ route('admin.shipping-zones.show', $shippingZone) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Zone Details
            </a>
        </div>

        {{-- Session Messages (Keep outside form) --}}
        @include('admin.partials._session_messages')

        {{-- Edit Form Card --}}
        <div class="bg-white shadow sm:rounded-lg">
            <form action="{{ route('admin.shipping-zones.update', $shippingZone) }}" method="POST">
                @csrf {{-- Keep CSRF in the main form tag --}}
                @method('PUT') {{-- Keep Method Spoofing here --}}

                {{-- Include the form partial --}}
                {{-- Pass the existing $shippingZone object --}}
                @include('admin.shipping-zones._form', ['shippingZone' => $shippingZone])

                {{-- Action Buttons --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3">
                     <a href="{{ route('admin.shipping-zones.show', $shippingZone) }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancel
                    </a>
                    <x-primary-button>
                         {{ __('Update Zone') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>