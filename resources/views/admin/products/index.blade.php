{{-- resources/views/admin/products/index.blade.php --}}
<x-admin-layout title="Products">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header & Actions --}}
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                 <h1 class="text-2xl font-semibold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Products</h1>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
                 {{-- Filtering Controls (Example: Add Search Later) --}}
                 {{-- <input type="text" name="search" placeholder="Search..." class="rounded-md border-gray-300 shadow-sm..."> --}}
                 <select id="category-filter" name="category_id" class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        {{-- Mark the currently selected category --}}
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Create Button --}}
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                     <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Add Product
                </a>
            </div>
        </div>

        {{-- Session Messages --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                <div class="flex justify-between">
                    <div><span class="font-medium">Success!</span> {{ session('success') }}</div>
                    <button @click="show = false" type="button" class="ml-auto -mx-1.5 -my-1.5 bg-green-100 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex h-8 w-8" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
            </div>
        @endif
        @if(session('error'))
             <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                 <div class="flex justify-between">
                    <div><span class="font-medium">Error!</span> {{ session('error') }}</div>
                    <button @click="show = false" type="button" class="ml-auto -mx-1.5 -my-1.5 bg-red-100 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex h-8 w-8" aria-label="Close">
                       <span class="sr-only">Close</span>
                       <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                   </button>
                </div>
            </div>
        @endif

        {{-- Product Table Card --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
             {{-- Optional Card Header --}}
            {{-- <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900"> Product List </h3>
            </div> --}}

            <div class="overflow-x-auto">
                @if($products->isEmpty())
                    <div class="p-6 text-center text-gray-500">
                        No products found matching your criteria.
                        <a href="{{ route('admin.products.create') }}" class="ml-2 text-indigo-600 hover:underline font-medium">Add a Product</a>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                {{-- Image --}}
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                {{-- Name & Category --}}
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                {{-- Status (Active/Featured) --}}
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                {{-- SKU --}}
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                {{-- Quantity --}}
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                {{-- Price --}}
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                {{-- Actions --}}
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $product)
                            <tr>
                                {{-- Image --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{-- Fetch first image or show placeholder --}}
                                    @php $firstImage = $product->images->sortBy('sort_order')->first(); @endphp
                                    <img src="{{ $firstImage ? Storage::url($firstImage->path) : asset('images/placeholder-image.png') }}" {{-- Use asset() for placeholder in public dir --}}
                                         alt="{{ $product->name }}" class="h-10 w-10 rounded-md object-cover border">
                                </td>
                                {{-- Name & Category --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    {{-- Display Categories (optional) --}}
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ $product->categories->pluck('name')->implode(', ') ?: 'No category' }}
                                    </div>
                                </td>
                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <div>
                                        @if($product->is_active)
                                            <span class="px-2 inline-flex leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                            </span>
                                        @endif
                                    </div>
                                    @if($product->is_featured)
                                         <div class="mt-1">
                                            <span class="px-2 inline-flex leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Featured
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                {{-- SKU --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku ?? '--' }}</td>
                                {{-- Quantity --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->quantity }}</td>
                                {{-- Price --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($product->price, 2) }}</td>
                                {{-- Actions --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                        {{-- Edit Icon (Optional) --}}
                                        <svg class="inline-block w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span class="sr-only">Edit</span>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                            {{-- Delete Icon (Optional) --}}
                                             <svg class="inline-block w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                             <span class="sr-only">Delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                     {{-- Pagination Links --}}
                    @if ($products->hasPages())
                        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            {{ $products->links() }} {{-- Make sure controller uses paginate() and withQueryString() --}}
                        </div>
                    @endif
                 @endif
            </div> {{-- End overflow-x-auto --}}
        </div> {{-- End Card --}}
    </div>

    {{-- Push the filter script to the 'scripts' stack --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoryFilter = document.getElementById('category-filter');
            if (categoryFilter) {
                categoryFilter.addEventListener('change', function() {
                    const categoryId = this.value;
                    const currentUrl = new URL('{{ route('admin.products.index') }}'); // Use route() for base URL

                    // Preserve other existing query parameters (like search, page, etc.)
                    const searchParams = new URLSearchParams(window.location.search);

                    if (categoryId) {
                        searchParams.set('category_id', categoryId);
                    } else {
                        searchParams.delete('category_id');
                    }
                    // Reset page to 1 when filter changes
                    searchParams.delete('page');

                    // Build the new URL
                    currentUrl.search = searchParams.toString();
                    window.location.href = currentUrl.toString();
                });
            }
        });
    </script>
    @endpush

</x-admin-layout>