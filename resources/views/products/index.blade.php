{{-- resources/views/products/index.blade.php --}}
<x-app-layout :title="$activeCategory ? $activeCategory->name : 'Shop All Products'">

    {{-- Header with Breadcrumbs --}}
    <div class="bg-pink-50 border-b border-pink-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <nav class="text-sm" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex space-x-2 items-center">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-pink-600 transition-colors">Home</a>
                        <x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/>
                    </li>
                    <li class="flex items-center">
                        @if($activeCategory)
                            <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-pink-600 transition-colors">Shop</a>
                            <x-heroicon-s-chevron-right class="w-4 h-4 mx-1 text-gray-400"/>
                            <span class="text-pink-700 font-medium" aria-current="page">{{ $activeCategory->name }}</span>
                        @else
                            <span class="text-pink-700 font-medium" aria-current="page">All Products</span>
                        @endif
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-pink-800 mt-3">
                {{ $activeCategory ? $activeCategory->name : 'Explore Our Collection' }}
            </h1>
             @if($activeCategory && $activeCategory->description)
                <p class="mt-2 text-sm text-pink-700 max-w-2xl">{{ $activeCategory->description }}</p>
            @endif
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="flex flex-col lg:flex-row lg:space-x-8">

            {{-- Filters Sidebar --}}
            <aside class="w-full lg:w-1/4 xl:w-1/5 flex-shrink-0 mb-10 lg:mb-0">
                <div class="sticky top-24 space-y-8"> {{-- top-24 for sticky header --}}

                    {{-- Category Filter --}}
                    <div class="bg-white p-5 rounded-xl shadow-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-pink-100 pb-3">
                            Categories
                        </h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('products.index', request()->except(['category', 'page'])) }}"
                                   @class([
                                       'block px-4 py-2.5 rounded-lg text-sm transition-all duration-150 ease-in-out group',
                                       'bg-pink-600 text-white shadow-md hover:bg-pink-700' => !$activeCategory,
                                       'text-gray-700 hover:bg-pink-50 hover:text-pink-700 hover:pl-5' => $activeCategory
                                   ])>
                                    <span class="font-medium">All Products</span>
                                </a>
                            </li>
                            @foreach($filterCategories as $category)
                                <li>
                                    <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                                       @class([
                                           'block px-4 py-2.5 rounded-lg text-sm transition-all duration-150 ease-in-out group',
                                           'bg-pink-600 text-white shadow-md hover:bg-pink-700' => $activeCategory && $activeCategory->slug == $category->slug,
                                           'text-gray-700 hover:bg-pink-50 hover:text-pink-700 hover:pl-5' => !$activeCategory || $activeCategory->slug != $category->slug
                                       ])>
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Sorting Dropdown --}}
                    <div class="bg-white p-5 rounded-xl shadow-lg">
                         <label for="sort-by" class="block text-lg font-semibold text-gray-900 mb-3">Sort By</label>
                         <form id="sortForm" action="{{ route('products.index') }}" method="GET">
                             @foreach(request()->except(['sort', 'page']) as $key => $val)
                                @if(is_array($val))
                                    @foreach($val as $arrayVal)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $arrayVal }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                @endif
                            @endforeach
                            <select id="sort-by" name="sort" onchange="this.form.submit();"
                                 class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm py-2.5">
                                 @php
                                     $sortOptions = [
                                         'latest' => 'Latest Arrivals',
                                         'price_asc' => 'Price: Low to High',
                                         'price_desc' => 'Price: High to Low',
                                         'name_asc' => 'Name: A-Z',
                                         'name_desc' => 'Name: Z-A',
                                     ];
                                 @endphp
                                 @foreach($sortOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $sortOrder == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                 @endforeach
                            </select>
                         </form>
                    </div>

                    {{-- Placeholder for Price Range Filter --}}
                    {{-- <div class="bg-white p-5 rounded-xl shadow-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Price Range</h3>
                         </div> --}}
                </div>
            </aside>

            {{-- Product Grid --}}
            <main class="w-full lg:col-span-3">
                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                         <p class="text-sm text-gray-600 mb-3 sm:mb-0">
                             Showing <span class="font-semibold text-pink-700">{{ $products->firstItem() ?: 0 }}</span>-<span class="font-semibold text-pink-700">{{ $products->lastItem() ?: 0 }}</span> of <span class="font-semibold text-pink-700">{{ $products->total() }}</span> results
                         </p>
                         @if($activeCategory || count(request()->except(['category', 'page', 'sort'])) > 0) {{-- Show if any filter is active --}}
                         <div class="flex flex-wrap gap-2 items-center">
                            @if($activeCategory)
                                <span class="inline-flex items-center gap-x-1 rounded-full bg-pink-100 px-2.5 py-1 text-xs font-medium text-pink-700 ring-1 ring-inset ring-pink-600/20">
                                    {{ $activeCategory->name }}
                                    <a href="{{ route('products.index', request()->except(['category', 'page'])) }}" class="group relative -mr-1 h-4 w-4 rounded-sm hover:bg-pink-500/20 flex items-center justify-center">
                                        <span class="sr-only">Remove</span>
                                        <x-heroicon-s-x-mark class="h-3 w-3 text-pink-600 group-hover:text-pink-700"/>
                                    </a>
                                </span>
                            @endif
                            {{-- Add other active filter badges here if you implement more filters --}}
                            <a href="{{ route('products.index') }}" class="text-xs text-pink-600 hover:text-pink-800 hover:underline">Clear all filters</a>
                         </div>
                         @endif
                    </div>
                </div>

                @if($products->isEmpty())
                    <div class="text-center py-16 sm:py-24 bg-white rounded-xl shadow-xl">
                        <x-heroicon-o-archive-box-x-mark class="mx-auto h-16 w-16 text-pink-400"/>
                        <h3 class="mt-4 text-xl font-semibold text-gray-900">No Products Found</h3>
                        <p class="mt-2 text-base text-gray-500">Sorry, we couldn't find any products matching your criteria. <br>Try adjusting your filters or browse all our wonderful items!</p>
                        <div class="mt-8">
                            <a href="{{ route('products.index') }}"
                               class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-base font-medium rounded-lg text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                                <x-heroicon-s-shopping-bag class="-ml-1 mr-2 h-5 w-5" />
                                View All Products
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-6 gap-y-10">
                        @foreach($products as $product)
                        <x-product-card :product="$product" 
                        :userWishlistProductIds="$userWishlistProductIds" />
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="mt-12 pt-8 border-t border-pink-100">
                            {{-- You can publish and customize Tailwind's pagination views if needed --}}
                            {{-- To style further, wrap $products->links() and apply styles, or customize the pagination view itself --}}
                            <div class="pagination-wrapper"> {{-- Example wrapper for potential styling --}}
                                {{ $products->links() }}
                            </div>
                        </div>
                    @endif
                @endif
            </main>

        </div>
    </div>
</x-app-layout>

@push('styles')
<style>
    /* Custom styles for pagination to match pink theme (optional) */
    .pagination-wrapper .pagination { /* Assuming 'pagination' is a class in your pagination view */
        display: flex;
        justify-content: center;
        list-style: none;
        padding: 0;
    }
    .pagination-wrapper .page-item .page-link {
        color: #DB2777; /* pink-600 */
        border-radius: 0.375rem; /* rounded-md */
        margin: 0 0.25rem;
        padding: 0.5rem 0.75rem;
    }
    .pagination-wrapper .page-item.active .page-link {
        background-color: #DB2777; /* pink-600 */
        border-color: #DB2777;
        color: white;
        z-index: 1;
    }
    .pagination-wrapper .page-item .page-link:hover {
        background-color: #fdf2f8; /* pink-50 */
        border-color: #fbcfe8; /* pink-200 */
    }
    .pagination-wrapper .page-item.disabled .page-link {
        color: #9ca3af; /* gray-400 */
        pointer-events: none;
        background-color: #f9fafb; /* gray-50 */
        border-color: #e5e7eb; /* gray-200 */
    }
</style>
@endpush