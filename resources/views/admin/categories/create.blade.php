<!-- CREATE PRODUCT --> 
<x-admin-layout title="Create New Category">
    
    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
         <h1 class="text-2xl font-semibold text-gray-900">Create New Category</h1>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
             <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Category Details
                </h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-6">
                    @include('admin.categories._form') {{-- Include the form partial --}}
                </form>
            </div>
        </div>
    </div>

</x-admin-layout>
<!-- END CREATE PRODUCT --> 