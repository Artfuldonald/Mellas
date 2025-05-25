<x-admin-layout :title="'Edit Category: ' . $category->name">
    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6 max-w-4xl mx-auto">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Category</h1>
            <a href="{{ route('admin.categories.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                ← Back to Categories
            </a>
        </div>
        @include('admin.partials._session_messages')

         {{-- Form tag points to the update route --}}
        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
            {{-- Include the form partial --}}
             {{-- Pass existing $category and $parentCategories from controller --}}
            @include('admin.categories._form', [
                'category' => $category,
                'parentCategories' => $parentCategories
            ])
        </form>
    </div>
</x-admin-layout>