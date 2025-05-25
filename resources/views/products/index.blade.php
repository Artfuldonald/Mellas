{{-- resources/views/products/index.blade.php --}}
<x-app-layout>
    {{-- The header will show its hamburger icon here because Route::is('home') is false --}}

    <div class="bg-pink-50 py-6 sm:py-8 border-b border-pink-100">
         <div class="container mx-auto px-4 sm:px-6 lg:px-8">
              <h1 class="text-3xl font-bold tracking-tight text-pink-800">
                @if(request()->filled('category') && $activeCategory = $categories->firstWhere('slug', request('category')))
                    {{ $activeCategory->name }}
                @else
                    Shop All Products
                @endif
              </h1>
         </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">

            {{-- Optional Filters Sidebar for Product Listing Page --}}
            <aside class="lg:col-span-1">
                <div class="sticky top-24 space-y-8">
                    {{-- You might have different/more filters here than just categories --}}
                    {{-- For example, price range, brand, attributes --}}
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Refine Search</h2>
                        {{-- Placeholder for more advanced filters --}}
                        <p class="text-sm text-gray-500">Price filters, brand filters, etc. will go here.</p>
                    </div>
                    <div>
                         <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Sort By</h2>
                         <select onchange="window.location = this.value;"
                                 class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm">
                             @php
                                 $sortOptions = [ /* ... sort options ... */ ];
                                 $currentSort = request('sort', 'latest');
                             @endphp
                             @foreach($sortOptions as $value => $label)
                                <option value="{{ route('products.index', array_merge(request()->except('page', 'sort'), ['sort' => $value])) }}"
                                        {{ $currentSort == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                             @endforeach
                         </select>
                    </div>
                </div>
            </aside>

            {{-- Product Grid --}}
            <main class="lg:col-span-3">
                {{-- ... (Results Count) ... --}}
                @if($products->isEmpty())
                    {{-- ... empty state message ... --}}
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-6 gap-y-10">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                    @if ($products->hasPages())
                        <div class="mt-12 pt-6 border-t border-gray-200">
                            {{ $products->links() }}
                        </div>
                    @endif
                @endif
            </main>
        </div>
    </div>
</x-app-layout>