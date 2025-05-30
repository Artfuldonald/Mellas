     {{--create.blade.php for admin brand--}}
     {{-- resources/views/admin/brands/create.blade.php --}}
<x-admin-layout title="Create New Brand"> 
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="sm:flex sm:items-center mb-6">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Create New Brand</h1>
                <p class="mt-1 text-sm text-gray-700">Add details for the new brand.</p>
            </div>
        </div>

        <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
            @csrf 
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                <div class="px-4 py-5 sm:p-6">
                    @include('admin.brands._form') 
                </div>
                <div class="flex items-center justify-end gap-x-3 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-6">
                    <a href="{{ route('admin.brands.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-md bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600">
                        Create Brand
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout> 