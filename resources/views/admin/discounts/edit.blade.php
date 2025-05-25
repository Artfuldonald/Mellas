<x-admin-layout :title="'Edit Discount Code: ' . $discount->code">
    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6 max-w-4xl mx-auto">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Discount Code: <span class="text-indigo-600">{{ $discount->code }}</span></h1>
            <a href="{{ route('admin.discounts.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Discounts
            </a>
        </div>
        @include('admin.partials._session_messages')
        <div class="bg-white shadow sm:rounded-lg">
            <form action="{{ route('admin.discounts.update', $discount) }}" method="POST">
                @method('PUT')
                {{-- Include the partial, passing the existing $discount --}}
                @include('admin.discounts._form', ['discount' => $discount])
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3">
                     <a href="{{ route('admin.discounts.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Cancel</a>
                    <x-primary-button>{{ __('Update Discount') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>