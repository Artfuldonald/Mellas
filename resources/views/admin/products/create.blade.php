<!-- resources/views/admin/products/create.blade.php -->
<x-admin-layout>
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.products._form', ['product' => null])
        
    </form>
</x-admin-layout>
