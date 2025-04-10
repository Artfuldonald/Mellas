{{-- resources/views/admin/products/edit.blade.php --}}
<x-admin-layout :title="'Edit Product: ' . $product->name"> {{-- Dynamic Title --}}

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
         <h1 class="text-2xl font-semibold text-gray-900">Edit Product: <span class="text-indigo-600">{{ $product->name }}</span></h1>

         {{-- Form points to update route, uses PUT, handles files --}}
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Include the refined form partial, passing necessary data --}}
            @include('admin.products._form', ['product' => $product, 'categories' => $categories])

            {{-- Buttons are now included within _form.blade.php --}}
        </form>
    </div>

</x-admin-layout>