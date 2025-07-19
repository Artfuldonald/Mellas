<x-app-layout title="{{ $activeCategory ? $activeCategory->name : 'All Products' }}">
    
    {{-- This main div controls the state for both mobile modals --}}
    <div x-data="mobileFilterManager({
        allCategories: {{ Js::from($navCategories) }},
        allBrands: {{ Js::from($brands) }},
        activeFilters: {{ Js::from($activeFilters) }}
    })">
        
        {{-- Include the Mobile Filter Menu (it's hidden by default) --}}
        @include('products.partials._mobile-filters')

        {{-- Include the Sort By Modal --}}
        @include('products.partials._sort-modal')

        {{-- Breadcrumbs Section --}}
        <div class="bg-gray-100 py-3 border-b border-gray-200">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="text-xs text-gray-500" aria-label="Breadcrumb">
                    <ol class="list-none p-0 flex flex-wrap items-center">
                        <li class="flex items-center mr-1.5 mb-1"><a href="{{ route('home') }}" class="hover:text-pink-600">Home</a><x-heroicon-s-chevron-right class="w-3 h-3 ml-1.5 text-gray-400"/></li>
                        <li class="flex items-center mr-1.5 mb-1">
                            @if($activeCategory || request()->has('brands'))
                                <a href="{{ route('products.index') }}" class="hover:text-pink-600">All Products</a>
                            @else
                                <span class="text-gray-700 font-medium" aria-current="page">All Products</span>
                            @endif
                        </li>
                        @if(!empty($breadcrumbs))
                            @foreach($breadcrumbs as $breadcrumb)
                                <li class="flex items-center mr-1.5 mb-1">
                                    <x-heroicon-s-chevron-right class="w-3 h-3 mr-1.5 text-gray-400"/>
                                    @if(!$loop->last || ($loop->last && $activeCategory && $activeCategory->id !== $breadcrumb->id))
                                        <a href="{{ route('products.index', ['category' => $breadcrumb->slug]) }}" class="hover:text-pink-600">{{ $breadcrumb->name }}</a>
                                    @else
                                        <span class="text-gray-700 font-medium" aria-current="page">{{ $breadcrumb->name }}</span>
                                    @endif
                                </li>
                            @endforeach
                        @endif
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                
                {{-- DESKTOP SIDEBAR (Visible only on large screens) --}}
                <aside class="hidden lg:block lg:col-span-1">
                     <div class="sticky top-24 h-[calc(100vh-7rem)] overflow-y-auto pr-4">
                        @include('products.partials._sidebar-filters')
                    </div>
                </aside>

                {{-- MAIN CONTENT (Product Grid and Header) --}}
                <main class="lg:col-span-3">
                    <div class="bg-white rounded-md shadow p-4 sm:p-5 mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h1 class="text-lg sm:text-xl font-semibold text-gray-800">
                                    {{ $activeCategory->name ?? 'All Products' }}
                                </h1>
                                <p class="text-xs text-gray-500 font-normal">
                                    ({{ $products->total() }} products found)
                                </p>
                            </div>

                            {{-- DESKTOP Sorting Dropdown (Hidden on mobile) --}}
                            <div class="hidden sm:flex items-center space-x-2 flex-shrink-0">
                                <label for="sort-by-desktop" class="text-xs text-gray-600 whitespace-nowrap">Sort By:</label>
                                <select id="sort-by-desktop" name="sort" onchange="window.location = this.value;"
                                        class="block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-xs py-1.5 pr-7">
                                    @php
                                        $sortOptions = [
                                            'latest' => 'Latest Arrivals',
                                            'rating_desc' => 'Product Rating',
                                            'price_asc' => 'Price: Low to High',
                                            'price_desc' => 'Price: High to Low',
                                        ];
                                    @endphp
                                    @foreach($sortOptions as $value => $label)
                                       <option value="{{ request()->fullUrlWithQuery(['sort' => $value]) }}" @if($sortOrder == $value) selected @endif>
                                           {{ $label }}
                                       </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @if($products->isEmpty())
                        <div class="text-center py-16 sm:py-24 bg-white rounded-md shadow">
                            <x-heroicon-o-archive-box-x-mark class="mx-auto h-12 w-12 text-pink-400"/>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">No Products Found</h3>
                            <p class="mt-1 text-sm text-gray-500">We couldn't find any products matching your filters.</p>
                            <div class="mt-6">
                                <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700">
                                    Clear Filters & View All
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                            @foreach($products as $product)
                                <x-product-card :product="$product" :userWishlistProductIds="$userWishlistProductIds" />
                            @endforeach
                        </div>

                        @if ($products->hasPages())
                            <div class="mt-12 pt-8 border-t border-pink-100">
                                {{ $products->links('pagination::tailwind') }}
                            </div>
                        @endif
                    @endif
                </main>
            </div>
        </div>

        {{-- FLOATING MOBILE BAR --}}
       
        <div class="fixed inset-x-0 bottom-0 z-30 flex justify-center p-4 pointer-events-none lg:hidden" x-cloak>
            <div class="flex items-center bg-pink-800 text-white rounded-full shadow-lg overflow-hidden text-sm font-medium pointer-events-auto mb-2">
                
                {{-- Sort by Button --}}
                <button @click="sortModalOpen = true" type="button" class="flex items-center justify-center pl-4 pr-3 py-2.5 hover:bg-pink-700 transition-colors">
                    <x-heroicon-o-arrows-up-down class="w-5 h-5 mr-1.5"/>
                    <span>Sort by</span>
                </button>

                {{-- Divider --}}
                <div class="w-px h-4 bg-white"></div>

                {{-- Filter Button --}}
                <button @click="isOpen = true" type="button" class="flex items-center justify-center pl-3 pr-4 py-2.5 hover:bg-gray-700 transition-colors">
                    <x-heroicon-o-funnel class="w-5 h-5 mr-1.5"/>
                    <span>Filter</span>
                </button>
                
            </div>
        </div>
        
    </div>
    @push('scripts')
    <script>
    function mobileFilterManager(config) {
        return {
            // --- State ---
            isOpen: false,
            sortModalOpen: false,
            currentView: 'main',
            viewHistory: [],
            categoryDrilldownStack: [],
            brandSearch: '',
            allCategories: config.allCategories || [],
            allBrands: config.allBrands || [],
            
            // This holds the currently selected filter values
            filters: {
                category: null,
                brands: [],
                price_min: '', // Now included
                price_max: '', // Now included
                discount_min: null,
                rating_min: null, // Now included
                gender: null,
            },

            // --- Init ---
            init() {
                // Set initial filter state from the URL query params
                this.filters.category = config.activeFilters.category || null;
                this.filters.brands = Array.isArray(config.activeFilters.brands) ? config.activeFilters.brands : [];
                this.filters.price_min = config.activeFilters.price_min || '';
                this.filters.price_max = config.activeFilters.price_max || '';
                this.filters.discount_min = config.activeFilters.discount_min || null;
                this.filters.rating_min = config.activeFilters.rating_min || null;
                this.filters.gender = config.activeFilters.gender || null;
            },

            // --- Computed Properties ---
            get currentTitle() {
                if (this.currentView === 'category') return 'Category';
                if (this.currentView === 'brand') return 'Brand';
                return 'Filters'; // Default title
            },
            get filteredBrands() {
                if (!this.brandSearch.trim()) return this.allBrands;
                return this.allBrands.filter(brand => brand.name.toLowerCase().includes(this.brandSearch.toLowerCase()));
            },
            get currentCategoryList() {
                if (this.categoryDrilldownStack.length === 0) return this.allCategories;
                return this.categoryDrilldownStack[this.categoryDrilldownStack.length - 1].children;
            },

            // --- Methods ---
            navigateTo(viewName) {
                this.viewHistory.push(this.currentView);
                this.currentView = viewName;
            },
            navigateBack() {
                const previousView = this.viewHistory.pop();
                if (previousView) {
                    this.currentView = previousView;
                }
            },
            drillDown(category) {
                this.categoryDrilldownStack.push(category);
            },
            popCategoryDrilldown(index) {
                this.categoryDrilldownStack.splice(index + 1);
            },
            resetCategoryDrilldown() {
                this.categoryDrilldownStack = [];
            },
            selectCategory(category) {
                // If it has children, don't just select it, wait for user to drill down or explicitly select this parent
                if (category.children && category.children.length > 0) {
                    // This is a radio button, so clicking the label/area will also toggle the input
                } else {
                    // It's a leaf node, safe to select
                }
            },
            getCategoryName(slug) {
                if (!slug) return null;
                function find(categories, slug) {
                    for (const cat of categories) {
                        if (cat.slug === slug) return cat.name;
                        if (cat.children) {
                            const found = find(cat.children, slug);
                            if (found) return found;
                        }
                    }
                    return null;
                }
                return find(this.allCategories, slug);
            },
            getBrandNames(slugs) {
                if (!slugs || slugs.length === 0) return [];
                return this.allBrands
                    .filter(brand => slugs.includes(brand.slug))
                    .map(brand => brand.name);
            },

            resetFilters() {
                this.filters = {
                    category: null,
                    brands: [],
                    price_min: '',
                    price_max: '',
                    discount_min: null,
                    rating_min: null,
                    gender: null,
                };
                this.currentView = 'main';
                this.viewHistory = [];
                this.categoryDrilldownStack = [];
                window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'info', message: 'Filters cleared.' }}));
            },

            // Apply filters function now needs to include the new filters
            applyFilters() {
                const params = new URLSearchParams();
                
                for (const key in this.filters) {
                    const value = this.filters[key];
                    if (value !== null && value !== '' && !(Array.isArray(value) && value.length === 0)) {
                        if (Array.isArray(value)) {
                            value.forEach(item => params.append(`${key}[]`, item));
                        } else {
                            params.append(key, value);
                        }
                    }
                }

                // Preserve existing 'sort' parameter
                const currentUrlParams = new URLSearchParams(window.location.search);
                if (currentUrlParams.has('sort')) {
                    params.append('sort', currentUrlParams.get('sort'));
                }
                
                window.location.href = window.location.pathname + '?' + params.toString();
            }
        }
    }
    </script>
    @endpush
</x-app-layout>