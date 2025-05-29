{{-- resources/views/products/index.blade.php --}}
<x-app-layout :title="$activeCategory ? $activeCategory->name : 'Shop All Products'">

    {{-- Optional: Breadcrumbs (can be simpler or integrated into the top bar) --}}
    <div class="bg-gray-100 py-3 border-b border-gray-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-xs text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex space-x-1.5 items-center">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:text-pink-600">Home</a>
                        <x-heroicon-s-chevron-right class="w-3 h-3 mx-1 text-gray-400"/>
                    </li>
                    <li class="flex items-center">
                        @if($activeCategory)
                            <a href="{{ route('products.index') }}" class="hover:text-pink-600">All Products</a>
                            <x-heroicon-s-chevron-right class="w-3 h-3 mx-1 text-gray-400"/>
                            <span class="text-gray-700 font-medium" aria-current="page">{{ $activeCategory->name }}</span>
                        @else
                            <span class="text-gray-700 font-medium" aria-current="page">All Products</span>
                        @endif
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        {{-- Main Layout: Filters Sidebar + Product Grid --}}
        {{-- Jumia uses approx. 238px for filters and 712px for content (total ~950px before container padding) --}}
        {{-- We can use Tailwind's fraction/grid classes to approximate this ratio.
             lg:grid-cols-12 -> lg:col-span-3 for filters (25%), lg:col-span-9 for content (75%)
             Or lg:grid-cols-4 -> lg:col-span-1 for filters, lg:col-span-3 for content
        --}}
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-6 xl:gap-x-8">

            {{-- Filters Sidebar (Left Column) --}}
            {{-- Target width: ~238px. lg:col-span-3 on a 12-col grid is 25%. On a 1200px container, this is 300px.
                 We can add a max-w-xs (320px) or specific width if needed, or let it flow.
                 Jumia's filter panel is often quite narrow.
            --}}
            <aside class="lg:col-span-3 mb-8 lg:mb-0">
                <div class="lg:sticky lg:top-20 bg-white rounded-md shadow p-1"> {{-- top-20 assumes header height --}}
                    {{-- Filter sections will go inside this div --}}
                    <div class="space-y-5 p-4">

                        {{-- Category Filter --}}
                        <div class="filter-section">
                            <h3 class="text-sm font-semibold text-gray-800 mb-2 uppercase tracking-wide">Category</h3>
                            <ul class="space-y-1 text-xs">
                                <li>
                                    <a href="{{ route('products.index', request()->except(['category', 'page'])) }}"
                                       class="block py-1.5 px-2 rounded hover:bg-pink-50 {{ !$activeCategory ? 'text-pink-600 font-semibold bg-pink-100' : 'text-gray-600 hover:text-pink-700' }}">
                                        All Products
                                    </a>
                                </li>
                                @foreach($filterCategories->take(10) as $category) {{-- Show a limited number initially --}}
                                    <li>
                                        <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                                           class="block py-1.5 px-2 rounded hover:bg-pink-50 {{ $activeCategory && $activeCategory->slug == $category->slug ? 'text-pink-600 font-semibold bg-pink-100' : 'text-gray-600 hover:text-pink-700' }}">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                                @if($filterCategories->count() > 10)
                                    {{-- TODO: Add a "Show More" for categories with Alpine.js --}}
                                    <li class="pt-1"><a href="#" class="text-pink-600 hover:underline text-xs">Show more</a></li>
                                @endif
                            </ul>
                        </div>
                        <hr class="border-gray-100">

                        {{-- Brand Filter --}}
                        <div class="filter-section" x-data="{ searchBrand: '' }">
                            <h3 class="text-sm font-semibold text-gray-800 mb-2 uppercase tracking-wide">Brand</h3>
                            <div class="relative mb-2">
                                <input type="search" placeholder="Search brand" x-model="searchBrand"
                                       class="w-full border-gray-300 rounded-md shadow-sm text-xs py-1.5 pl-2 pr-7 focus:ring-pink-500 focus:border-pink-500">
                                <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            </div>
                            <ul class="space-y-0.5 text-xs max-h-48 overflow-y-auto custom-scrollbar-mobile pr-1">
                               {{-- @forelse($brands as $brand)
                                    <li x-show="searchBrand === '' || '{{ strtolower($brand->name) }}'.includes(searchBrand.toLowerCase())">
                                        <label class="flex items-center space-x-2 text-gray-600 hover:text-pink-700 cursor-pointer py-1 px-1 rounded hover:bg-pink-50">
                                            <input type="checkbox" name="brands[]" value="{{ $brand->slug }}"
                                                   form="filterForm" onchange="document.getElementById('filterForm').submit()"
                                                   class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500 h-3.5 w-3.5"
                                                   {{ (is_array(request('brands')) && in_array($brand->slug, request('brands'))) ? 'checked' : '' }}>
                                            <span>{{ $brand->name }} <span class="text-gray-400">({{ $brand->products_count }})</span></span>
                                        </label>
                                    </li>
                                @empty
                                    <li class="text-gray-500 italic px-1">No brands available.</li>
                                @endforelse --}}
                            </ul>
                        </div>
                        <hr class="border-gray-100">

                        {{-- Price Range Filter --}}
                        <div class="filter-section">
                            <h3 class="text-sm font-semibold text-gray-800 mb-3 uppercase tracking-wide">Price (GH₵)</h3>
                            <div class="flex items-center space-x-2 mb-2">
                                <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}"
                                       form="filterForm"
                                       class="w-full border-gray-300 rounded-md shadow-sm text-xs py-1.5 px-2 focus:ring-pink-500 focus:border-pink-500">
                                <span class="text-gray-400">-</span>
                                <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}"
                                       form="filterForm"
                                       class="w-full border-gray-300 rounded-md shadow-sm text-xs py-1.5 px-2 focus:ring-pink-500 focus:border-pink-500">
                            </div>
                            <button type="submit" form="filterForm" class="w-full text-xs bg-pink-500 text-white py-1.5 rounded hover:bg-pink-600 transition mt-1">Apply Price</button>
                        </div>
                        <hr class="border-gray-100">

                        {{-- Discount Percentage --}}
                        <div class="filter-section">
                            <h3 class="text-sm font-semibold text-gray-800 mb-2 uppercase tracking-wide">Discount Percentage</h3>
                             <ul class="space-y-0.5 text-xs">
                                @foreach(['80', '70', '50', '20'] as $discount)
                                <li>
                                    <label class="flex items-center space-x-2 text-gray-600 hover:text-pink-700 cursor-pointer py-1 px-1 rounded hover:bg-pink-50">
                                        <input type="radio" name="discount_min" value="{{ $discount }}"
                                               form="filterForm" onchange="document.getElementById('filterForm').submit()"
                                               class="rounded-full border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500 h-3.5 w-3.5"
                                               {{ request('discount_min') == $discount ? 'checked' : '' }}>
                                        <span>{{ $discount }}% or more</span>
                                    </label>
                                </li>
                                @endforeach
                                @if(request('discount_min')) {{-- Add a way to clear discount filter --}}
                                 <li>
                                    <a href="{{ route('products.index', array_merge(request()->except(['discount_min', 'page']), ['sort' => $sortOrder])) }}"
                                       class="block py-1.5 px-2 text-pink-600 hover:underline text-xs mt-1">
                                        Clear Discount
                                    </a>
                                 </li>
                                @endif
                            </ul>
                        </div>

                        {{-- Price Range Filter --}}
                        <div class="filter-section">
                            <h3 class="text-sm font-semibold text-gray-800 mb-3 uppercase tracking-wide">Price (GH₵)</h3>
                            {{-- Basic Min/Max Inputs --}}
                            <div class="flex items-center space-x-2 mb-2">
                                <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}" class="w-full border-gray-300 rounded-md shadow-sm text-xs py-1.5 px-2 focus:ring-pink-500 focus:border-pink-500">
                                <span class="text-gray-400">-</span>
                                <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}" class="w-full border-gray-300 rounded-md shadow-sm text-xs py-1.5 px-2 focus:ring-pink-500 focus:border-pink-500">
                            </div>
                            {{-- TODO: Add a price range slider here using noUISlider or similar --}}
                            <button type="submit" form="filterForm" class="w-full text-xs bg-pink-500 text-white py-1.5 rounded hover:bg-pink-600 transition">Apply</button>
                        </div>
                        <hr class="border-gray-100">

                        {{-- Discount Percentage (Example) --}}
                        <div class="filter-section">
                            <h3 class="text-sm font-semibold text-gray-800 mb-2 uppercase tracking-wide">Discount Percentage</h3>
                             <ul class="space-y-1 text-xs">
                                @foreach(['80', '70', '50', '20'] as $discount)
                                <li>
                                    <label class="flex items-center space-x-2 text-gray-600 hover:text-pink-700 cursor-pointer py-1">
                                        <input type="radio" name="discount_min" value="{{ $discount }}" class="rounded-full border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500 h-3.5 w-3.5" {{ request('discount_min') == $discount ? 'checked' : '' }}>
                                        <span>{{ $discount }}% or more</span>
                                    </label>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        {{-- Add more filters like "Shipped From" here --}}

                    </div>
                    {{-- Hidden form to submit all filters --}}
                    <form id="filterForm" action="{{ route('products.index') }}" method="GET" class="hidden">
                        @foreach(request()->except(['price_min', 'price_max', 'discount_min', 'page', /* add other filter keys here */]) as $key => $val)
                            @if(is_array($val))
                                @foreach($val as $arrayVal)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $arrayVal }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endif
                        @endforeach
                        {{-- Inputs for price_min, price_max, discount_min will be added by JS or their forms directly --}}
                    </form>                   
                </div>
            </aside>

            {{-- Product Grid (Right Column) --}}
            {{-- Target width: ~712px. lg:col-span-9 is 75% --}}
            <main class="lg:col-span-9">
                <div class="bg-white rounded-md shadow p-4 sm:p-5 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-lg sm:text-xl font-semibold text-gray-800">
                                {{ $activeCategory ? $activeCategory->name : 'All Products' }}
                                <span class="text-xs text-gray-500 font-normal">({{ $products->total() }} products found)</span>
                            </h1>
                             @if($activeCategory && $activeCategory->description && Str::length($activeCategory->description) < 150)
                                <p class="mt-1 text-xs text-gray-600 max-w-xl">{{ $activeCategory->description }}</p>
                            @endif
                        </div>

                        {{-- Sorting Dropdown --}}
                        <div class="flex items-center space-x-2 flex-shrink-0">
                             <label for="sort-by-main" class="text-xs text-gray-600 whitespace-nowrap">Sort By:</label>
                             <select id="sort-by-main" name="sort" onchange="document.getElementById('filterForm').submit();"
                                     class="block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-xs py-1.5 pr-7">
                                 @php
                                     $sortOptions = [
                                         'latest' => 'Latest Arrivals', // Jumia often uses 'Popularity' or 'Newest Arrivals'
                                         'price_asc' => 'Price: Low to High',
                                         'price_desc' => 'Price: High to Low',
                                         // 'name_asc' => 'Name: A-Z', // Less common on Jumia's main sort
                                         'rating_desc' => 'Product Rating', // Jumia has this
                                     ];
                                 @endphp
                                 @foreach($sortOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $sortOrder == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                 @endforeach
                             </select>
                             <script>
                                document.getElementById('sort-by-main').addEventListener('change', function() {
                                    let sortForm = document.getElementById('filterForm');
                                    let existingSortInput = sortForm.querySelector('input[name="sort"]');
                                    if(existingSortInput) existingSortInput.remove();

                                    let newSortInput = document.createElement('input');
                                    newSortInput.type = 'hidden';
                                    newSortInput.name = 'sort';
                                    newSortInput.value = this.value;
                                    sortForm.appendChild(newSortInput);
                                    sortForm.submit();
                                });
                             </script>
                        </div>
                    </div>
                     {{-- Active Filters Display --}}
                     @if($activeCategory || request('price_min') || request('price_max') || request('discount_min') || count(request()->except(['category', 'page', 'sort', 'price_min', 'price_max', 'discount_min'])) > 0)
                     <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-2 items-center text-xs">
                        <span class="text-gray-600 font-medium">Active Filters:</span>
                        @if($activeCategory)
                            <span class="inline-flex items-center gap-x-1 rounded-full bg-pink-50 px-2 py-0.5 font-medium text-pink-700 ring-1 ring-inset ring-pink-600/20">
                                {{ $activeCategory->name }}
                                <a href="{{ route('products.index', array_merge(request()->except(['category', 'page']), ['sort' => $sortOrder])) }}" class="group relative -mr-0.5 h-3.5 w-3.5 rounded-sm hover:bg-pink-500/20 flex items-center justify-center">
                                    <x-heroicon-s-x-mark class="h-2.5 w-2.5 text-pink-600 group-hover:text-pink-700"/>
                                </a>
                            </span>
                        @endif
                        @if(request('price_min') || request('price_max'))
                            <span class="inline-flex items-center gap-x-1 rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20">
                                Price: GH₵{{ request('price_min', '*') }} - GH₵{{ request('price_max', '*') }}
                                <a href="{{ route('products.index', array_merge(request()->except(['price_min', 'price_max', 'page']), ['sort' => $sortOrder])) }}" class="group relative -mr-0.5 h-3.5 w-3.5 rounded-sm hover:bg-gray-500/20 flex items-center justify-center">
                                    <x-heroicon-s-x-mark class="h-2.5 w-2.5 text-gray-600 group-hover:text-gray-700"/>
                                </a>
                            </span>
                        @endif
                        {{-- Add more active filter badges here --}}
                        <a href="{{ route('products.index', ['sort' => $sortOrder]) }}" class="text-pink-600 hover:underline ml-auto">Clear all filters</a>
                     </div>
                     @endif
                </div>

                @if($products->isEmpty())
                    <div class="text-center py-16 sm:py-24 bg-white rounded-md shadow">
                        <x-heroicon-o-archive-box-x-mark class="mx-auto h-12 w-12 text-pink-400"/>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No Products Found</h3>
                        <p class="mt-1 text-sm text-gray-500">We couldn't find any products matching your current filters.</p>
                        <div class="mt-6">
                            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                                View All Products
                            </a>
                        </div>
                    </div>
                @else
                    {{-- Product Grid: Aim for ~219px card width.
                         If content area is ~712px:
                         712 / 3 columns = ~237px per column. Minus gaps, this gets close.
                    --}}
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                        @php $userWishlistProductIds = Auth::check() ? Auth::user()->wishlistItems()->pluck('product_id')->toArray() : []; @endphp
                        @foreach($products as $product)
                            {{-- This is your main product card for the listing page --}}
                            <x-product-card :product="$product" :userWishlistProductIds="$userWishlistProductIds" />
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="mt-8 sm:mt-10 pt-6 sm:pt-8 border-t border-gray-200">
                            <div class="pagination-wrapper">
                                {{ $products->links() }}
                            </div>
                        </div>
                    @endif
                @endif
            </main>

        </div>
    </div>
</x-app-layout>