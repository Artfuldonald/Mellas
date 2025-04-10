<x-admin-layout title="Create Attribute">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <h1 class="text-2xl font-semibold text-gray-900">Create New Attribute</h1>

        <form action="{{ route('admin.attributes.store') }}" method="POST">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                     @include('admin.attributes._form') {{-- Include the simple form partial --}}
                </div>
                <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
                     <a href="{{ route('admin.attributes.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Attribute
                    </button>
                </div>
            </div>
        </form>
    </div>

</x-admin-layout>