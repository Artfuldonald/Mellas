{{-- resources/views/admin/products/index.blade.php --}}
<x-admin-layout title="Products">

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Header & Actions (Keep your preferred search/filter implementation here) --}}
        <div class="md:flex md:items-center md:justify-between md:space-x-4">
             <div class="min-w-0 flex-1">
                 <h1 class="text-2xl font-semibold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Products</h1>
            </div>
            {{-- Right Aligned Actions/Filters --}}
            <div class="mt-4 flex flex-col space-y-3 md:flex-row md:items-center md:ml-4 md:mt-0 md:space-y-0 md:space-x-3">
                 {{-- Your Search Form --}}
                 <form action="{{ route('admin.products.index') }}" method="GET" class="flex-grow md:flex-grow-0 md:w-64">
                     <label for="search-product" class="sr-only">Search products</label>
                     <div class="relative">
                         <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                             <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
                         </div>
                         <input type="search" name="search" id="search-product" class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Search name or SKU..." value="{{ request('search', '') }}">
                         {{-- Add clear button if using standard form submit --}}
                         @if(request('search'))
                             <div class="absolute inset-y-0 right-0 flex py-1.5 pr-1.5">
                                 <a href="{{ route('admin.products.index', request()->except('search', 'page')) }}" class="inline-flex items-center rounded border border-gray-200 px-1 font-sans text-xs font-medium text-gray-400 hover:bg-gray-100 hover:text-gray-500" title="Clear search">
                                     <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                 </a>
                             </div>
                         @endif
                     </div>
                     @if(request('category_id'))
                         <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                     @endif
                 </form>

                 {{-- Category Filter --}}
                 <select id="category-filter" name="category_id" ...>
                    <option value="">All Categories</option>
                    @foreach($categoriesForFilter as $category) {{-- Use $categoriesForFilter --}}
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Create Button --}}
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                     <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                    Add Product
                </a>
            </div>
        </div>

        {{-- Session Messages (Keep as before) --}}
        {{-- ... --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
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
             <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
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
        {{-- Add Alpine component to manage modal state --}}
        <div x-data="{ openModalProductId: null }" class="bg-white shadow overflow-hidden sm:rounded-lg">
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $product)
                            <tr>
                                {{-- Image Column --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($product->hasMedia('default'))
                                        {{-- Get the URL of the first image's thumbnail conversion --}}
                                        <img src="{{ $product->getFirstMediaUrl('default', 'cart_thumbnail') }}" 
                                            alt="{{ $product->name }}" 
                                            class="h-12 w-12 object-cover rounded border border-gray-200">
                                    @else
                                         <div class="h-12 w-12 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                </td>
                                {{-- Name & Category Column --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="hover:text-indigo-600">
                                            {{ $product->name }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ $product->categories->pluck('name')->implode(', ') ?: 'No category' }}
                                    </div>
                                </td>
                                {{-- Status Column --}}
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <div>
                                        @if($product->is_active) <span class="px-2 inline-flex leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else <span class="px-2 inline-flex leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span> @endif
                                    </div>
                                    @if($product->is_featured) <div class="mt-1"><span class="px-2 inline-flex leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Featured</span></div> @endif
                                </td>

                                {{-- SKU Column (Simplified) --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if ($product->variants_count > 0)
                                        <span class="italic text-xs text-gray-400">Multiple ({{ $product->variants_count }})</span>
                                    @elseif ($product->sku)
                                        {{ $product->sku }}
                                    @else
                                        <span class="text-xs italic text-gray-400">N/A</span>
                                    @endif
                                </td>

                                {{--  STOCK COLUMN  --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center"> {{-- Centered text --}}
                                {{-- Use the display_stock calculated in the controller --}}
                                <span class="font-medium {{ $product->display_stock <= 0 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $product->display_stock }}
                                </span>
                                {{-- Show variant count if applicable --}}
                                @if($product->variants_count > 0)
                                    <span class="text-xs text-gray-400 block">({{ $product->variants_count }} variants)</span>
                                @endif
                            </td>

                                {{-- Price Column --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($product->price, 2) }}
                                    @if ($product->variants_count > 0 && $product->variants->isNotEmpty())
                                        @php
                                            $minPrice = $product->variants->min('price');
                                            $maxPrice = $product->variants->max('price');
                                        @endphp
                                        @if ($minPrice != $maxPrice)
                                            <div class="text-xs text-gray-500">(${!! number_format($minPrice, 2) !!} - ${!! number_format($maxPrice, 2) !!})</div>
                                        @endif
                                    @endif
                                </td>

                {{-- Actions Column --}}                              
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">

                    {{--  conditional Stock Adjust Link --}}
                    @if ($product->variants_count == 0) {{-- Only for simple products --}}
                        <a href="{{ route('admin.products.stock.adjust.form', $product) }}" class="text-gray-500 hover:text-gray-800" title="Adjust Stock">
                            <x-heroicon-o-adjustments-horizontal class="inline-block w-5 h-5"/>
                            <span class="sr-only">Adjust Stock</span>
                        </a>
                    @endif
                    {{-- End Stock Adjust Link --}}

                    {{-- Details Button (For Variants) --}}
                    @if ($product->variants_count > 0)
                        <button type="button" @click="openModalProductId = {{ $product->id }}" class="text-blue-600 hover:text-blue-900" title="View Variant Details">
                            <x-heroicon-o-eye class="inline-block w-5 h-5"/>
                            <span class="sr-only">Details</span>
                        </button>
                    @endif

                    {{-- Edit Button --}}
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                        <x-heroicon-o-pencil-square class="inline-block w-5 h-5"/>
                        <span class="sr-only">Edit</span>
                    </a>

                    {{-- Delete Button --}}
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                            <x-heroicon-o-trash class="inline-block w-5 h-5"/>
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
                            {{ $products->links() }}
                        </div>
                    @endif
                 @endif
            </div> {{-- End overflow-x-auto --}}
            {{-- **** MODALS DEFINED OUTSIDE THE TABLE **** --}}
            @foreach($products as $product)
                @if ($product->variants_count > 0)
                    {{-- Use x-teleport if available and needed, otherwise this should work --}}
                    <div x-show="openModalProductId === {{ $product->id }}"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 z-50 overflow-y-auto"
                         aria-labelledby="modal-title-{{ $product->id }}"
                         role="dialog"
                         aria-modal="true"
                         style="display: none;" {{-- Keep for initial hide --}}
                         >
                        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                            {{-- Background overlay --}}
                            <div x-show="openModalProductId === {{ $product->id }}" @click="openModalProductId = null"
                                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                            {{-- Modal panel --}}
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
                            <div x-show="openModalProductId === {{ $product->id }}"
                                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                                {{-- Modal Header --}}
                                <div class="flex items-center justify-between pb-3 border-b">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title-{{ $product->id }}">Variant Details: {{ $product->name }}</h3>
                                    <button @click="openModalProductId = null" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        <span class="sr-only">Close modal</span>
                                    </button>
                                </div>
                                {{-- Modal Body --}}
                                <div class="mt-4">
                                    @if($product->variants->isNotEmpty())
                                        <div class="overflow-hidden border border-gray-200 rounded-md">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                {{-- Variant table content --}}
                                                {{-- ... (same as before) ... --}}
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Variant Name</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($product->variants as $variant)
                                                        <tr>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $variant->name }}</td>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $variant->sku ?: '--' }}</td>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">${{ number_format($variant->price, 2) }}</td>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium {{ $variant->quantity <= 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $variant->quantity }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">No variants found for this product.</p>
                                    @endif
                                </div>
                                {{-- Modal Footer --}}
                                <div class="mt-5 sm:mt-6">
                                    <button @click="openModalProductId = null" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            {{-- **** END MODAL DEFINITIONS **** --}}

        </div> {{-- End Card --}}
    </div>

    {{-- Filter Script (Keep as before or use Alpine version) --}}
    @push('scripts')
    <script>
        // Your category filter JS (or the Alpine filter component script if you used that)
        document.addEventListener('DOMContentLoaded', function() {
            const categoryFilter = document.getElementById('category-filter');
            if (categoryFilter) {
                categoryFilter.addEventListener('change', function() {
                    const categoryId = this.value;
                    const currentUrl = new URL(window.location.href);
                    const searchParams = currentUrl.searchParams;

                    if (categoryId) {
                        searchParams.set('category_id', categoryId);
                    } else {
                        searchParams.delete('category_id');
                    }
                    searchParams.delete('page');
                    currentUrl.search = searchParams.toString();
                    window.location.href = currentUrl.toString();
                });
            }
        });
    </script>
    @endpush

</x-admin-layout>