{{--views/products/partials/_sidebar-filters.blade.php --}}
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
            @foreach($filterCategories->take(10) as $category) 
                <li>
                    <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                        class="block py-1.5 px-2 rounded hover:bg-pink-50 {{ $activeCategory && $activeCategory->slug == $category->slug ? 'text-pink-600 font-semibold bg-pink-100' : 'text-gray-600 hover:text-pink-700' }}">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
            @if($filterCategories->count() > 10)               
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
            @forelse($brands as $brand)
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
            @endforelse 
        </ul>
    </div>
    <hr class="border-gray-100">

    {{-- Price Range Filter --}}
    <div class="filter-section" 
     x-data="priceSlider({
        min: 0,
        max: 5000,
        currentMin: {{ request('price_min', 0) }},
        currentMax: {{ request('price_max', 5000) }}
     })">
    <h3 class="text-sm font-semibold text-gray-800 mb-3 uppercase tracking-wide">Price (GH₵)</h3>
    
    <div x-ref="slider" class="mb-4"></div>

    <div class="flex items-center space-x-2 mb-2">
        {{-- Add x-ref and remove .debounce --}}
        <input type="number" name="price_min" x-model="minPrice" x-ref="minInput"
               form="filterForm"
               class="w-full border-gray-300 rounded-md shadow-sm text-xs py-1.5 px-2 focus:ring-pink-500 focus:border-pink-500">
        <span class="text-gray-400">-</span>
        {{-- Add x-ref and remove .debounce --}}
        <input type="number" name="price_max" x-model="maxPrice" x-ref="maxInput"
               form="filterForm"
               class="w-full border-gray-300 rounded-md shadow-sm text-xs py-1.5 px-2 focus:ring-pink-500 focus:border-pink-500">
    </div>
    <button type="submit" form="filterForm" class="w-full text-xs bg-pink-500 text-white py-1.5 rounded hover:bg-pink-600 transition mt-1">
        Apply Price
    </button>
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
    
    {{-- PRODUCT RATING FILTER --}}
    <div class="filter-section">
        <h3 class="text-sm font-semibold text-gray-800 mb-2 uppercase tracking-wide">Product Rating</h3>
        <ul class="space-y-0.5 text-xs">
            @for ($i = 4; $i >= 1; $i--)
            <li>
                <label class="flex items-center space-x-2 text-gray-600 hover:text-pink-700 cursor-pointer py-1 px-1 rounded hover:bg-pink-50">
                    <input type="radio" name="rating_min" value="{{ $i }}"
                           form="filterForm" onchange="document.getElementById('filterForm').submit()"
                           class="rounded-full border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500 h-3.5 w-3.5"
                           {{ request('rating_min') == $i ? 'checked' : '' }}>
                    <span class="flex items-center">
                        @for ($s = 1; $s <= 5; $s++)
                            <svg class="w-3.5 h-3.5 {{ $s <= $i ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                        @endfor
                        <span class="ml-1.5">& above</span>
                    </span>
                </label>
            </li>
            @endfor
            @if(request('rating_min')) {{-- Add a way to clear the rating filter --}}
                <li>
                <a href="{{ route('products.index', array_merge(request()->except(['rating_min', 'page']), ['sort' => $sortOrder])) }}"
                    class="block py-1.5 px-2 text-pink-600 hover:underline text-xs mt-1">
                    Clear Rating
                </a>
                </li>
            @endif
        </ul>
    </div>

    {{-- SIZE FILTER (Placeholder) --}}
    <div class="filter-section">
        <h3 class="text-sm font-semibold text-gray-800 mb-2 uppercase tracking-wide">Size</h3>
        <ul class="space-y-0.5 text-xs max-h-48 overflow-y-auto custom-scrollbar-mobile pr-1">
            @foreach(['S', 'M', 'L', 'XL', 'XXL'] as $size)
                <li>
                    <label class="flex items-center space-x-2 text-gray-600 hover:text-pink-700 cursor-pointer py-1 px-1 rounded hover:bg-pink-50">
                        <input type="checkbox" name="sizes[]" value="{{ $size }}"
                                form="filterForm" onchange="document.getElementById('filterForm').submit()"
                                class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500 h-3.5 w-3.5"
                                {{ (is_array(request('sizes')) && in_array($size, request('sizes'))) ? 'checked' : '' }}>
                        <span>{{ $size }}</span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
    <hr class="border-gray-100">

    {{-- GENDER FILTER (Placeholder) --}}
    <div class="filter-section">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Gender</h3>
            @if(request('gender'))
                <a href="{{ route('products.index', array_merge(request()->except(['gender', 'page']), ['sort' => $sortOrder])) }}"
                   class="text-pink-600 hover:underline text-xs">Clear</a>
            @endif
        </div>
        <ul class="space-y-0.5 text-xs">
            @foreach(['Male', 'Female', 'Unisex'] as $gender)
                <li>
                    <label class="flex items-center space-x-2 text-gray-600 hover:text-pink-700 cursor-pointer py-1 px-1 rounded hover:bg-pink-50">
                        <input type="radio" name="gender" value="{{ $gender }}"
                               form="filterForm" onchange="document.getElementById('filterForm').submit()"
                               class="rounded-full border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500 h-3.5 w-3.5"
                               {{ request('gender') == $gender ? 'checked' : '' }}>
                        <span>{{ $gender }}</span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
    <hr class="border-gray-100">
    
    {{-- Add more filters like "Shipped From" here --}}

</div>
{{-- Hidden form to submit all filters --}}
<form id="filterForm" action="{{ route('products.index') }}" method="GET" class="hidden">
    {{-- This loop now excludes ALL filters that are handled by their own inputs --}}
    @php
        $handledFilters = ['price_min', 'price_max', 'discount_min', 'rating_min', 'page', 'brands', 'sizes', 'gender'];
    @endphp
    @foreach(request()->except($handledFilters) as $key => $val)
        @if(is_array($val))
            @foreach($val as $arrayVal)
                <input type="hidden" name="{{ $key }}[]" value="{{ $arrayVal }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endif
    @endforeach
</form>