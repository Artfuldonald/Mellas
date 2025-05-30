{{-- resources/views/admin/brands/edit.blade.php --}}
<x-admin-layout :title="'Edit Brand: ' . $brand->name">
    <div class="px-4 sm:px-6 lg:px-8 py-8"> {{-- Removed max-w-4xl mx-auto for now, can be added back if desired --}}
         <div class="sm:flex sm:items-center sm:justify-between mb-6"> {{-- Using justify-between for title and back link --}}
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Edit Brand: <span class="text-pink-600">{{ $brand->name }}</span></h1>
                {{-- <p class="mt-1 text-sm text-gray-700">Update the brand's details.</p> --}} {{-- Optional subtitle --}}
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-4">
                <a href="{{ route('admin.brands.index') }}" class="inline-flex items-center text-sm font-medium text-pink-600 hover:text-pink-800">
                    <x-heroicon-s-arrow-left class="w-4 h-4 mr-1.5" />
                    Back to Brands
                </a>
            </div>
        </div>

        @include('admin.partials._session_messages')

        <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                <div class="px-4 py-5 sm:p-6">
                    @include('admin.brands._form', ['brand' => $brand])
                </div>
                <div class="flex items-center justify-end gap-x-3 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-6">
                    <a href="{{ route('admin.brands.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-md bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600">
                        Update Brand
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>