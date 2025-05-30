{{-- resources/views/admin/brands/index.blade.php --}}
<x-admin-layout title="Manage Brands">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header & Actions --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Brands</h1>
                <p class="mt-1 text-sm text-gray-700">A list of all product brands in your store.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <a href="{{ route('admin.brands.create') }}"
                   class="inline-flex items-center justify-center rounded-md border border-transparent bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 sm:w-auto">
                    <x-heroicon-s-plus class="-ml-0.5 mr-1.5 h-5 w-5" />
                    Add Brand
                </a>
            </div>
        </div>

        {{-- Session Messages --}}
        @include('admin.partials._session_messages')

        {{-- Main Content Area for Table or Empty State --}}
        <div class="mt-4 flow-root">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    {{-- Check if $brands is passed and is not empty --}}
                    @if(isset($brands) && $brands->isNotEmpty())
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Logo</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Products</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach($brands as $brand)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                            @if($brand->logo_url)
                                                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="h-10 w-10 object-contain rounded-sm">
                                            @else
                                                <div class="h-10 w-10 bg-gray-100 rounded-sm flex items-center justify-center text-gray-400">
                                                    <x-heroicon-o-photo class="h-6 w-6"/>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 font-medium">{{ $brand->name }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $brand->products_count }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if($brand->is_active)
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Active</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-3">
                                            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="text-pink-600 hover:text-pink-900">Edit</a>
                                            <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this brand? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Pagination Links --}}
                        @if ($brands->hasPages())
                            <div class="mt-6 px-1"> {{-- Added px-1 to align with table potential padding --}}
                                {{ $brands->links() }}
                            </div>
                        @endif
                    @else {{-- This is the "No brands found" block --}}
                        <div class="text-center py-16 bg-white rounded-lg shadow-md"> {{-- Added bg and shadow for consistency --}}
                            <x-heroicon-o-tag class="mx-auto h-12 w-12 text-pink-400" /> {{-- Changed icon color to pink theme --}}
                            <h3 class="mt-4 text-lg font-medium text-gray-900">No Brands Found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new brand.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.brands.create') }}"
                                   class="inline-flex items-center rounded-md border border-transparent bg-pink-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2">
                                    Create Brand
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>