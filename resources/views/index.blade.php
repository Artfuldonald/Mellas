{{-- resources/views/index.blade.php --}}
<x-app-layout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row lg:space-x-8">
            {{-- Main Content Area (Hero, Promotions, Category Cards) --}}
            <main class="w-full lg:w-4/5 xl:w-3/4 min-w-0 space-y-12">
                <x-hero-section />

                @if(isset($navCategories) && $navCategories->isNotEmpty())
                    <section>
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Shop by Department</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                            @foreach($navCategories->take(12) as $category) {{-- Show some top-level --}}
                                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                                   class="block p-4 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow text-center group">
                                    @if($category->image)
                                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="w-20 h-20 mx-auto mb-3 object-contain rounded-md">
                                    @else
                                        <div class="w-20 h-20 mx-auto mb-3 bg-pink-50 rounded-full flex items-center justify-center">
                                            <x-heroicon-o-tag class="w-10 h-10 text-pink-400"/>
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-pink-600">{{ $category->name }}</span>
                                </a>
                            @endforeach
                        </div>
                        @if($navCategories->count() > 12)
                            <div class="text-center mt-6">
                                <button @click="$dispatch('open-all-categories-menu')" class="text-pink-600 hover:underline font-medium">
                                    View All Departments
                                </button>
                            </div>
                        @endif
                    </section>
                @endif

                @isset($featuredProducts)
                    <section>
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Featured Products</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            @foreach($featuredProducts as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>
                    </section>
                @endisset
            </main>

            {{-- Right Sidebar for "Call to Order", "Flash Sales" --}}
            <aside class="w-full lg:w-1/5 xl:w-1/4 flex-shrink-0 mt-8 lg:mt-0">
               <div class="space-y-6 sticky top-24">
                    <div class="bg-white p-4 shadow rounded-lg text-center">
                        <h3 class="font-semibold text-gray-700 mb-2">CALL TO ORDER</h3>
                        <p class="text-xl font-bold text-pink-600">030 274 0642</p>
                    </div>
                    <div class="bg-white p-4 shadow rounded-lg text-center">
                        <h3 class="font-semibold text-gray-700 mb-2">FLASH SALES</h3>
                        <p class="text-gray-500">Amazing deals coming soon!</p>
                    </div>
                    {{-- Placeholder for Jumia Tech Upgrade style banner --}}
                    <div class="bg-blue-500 text-white p-6 rounded-lg text-center">
                        <h3 class="text-xl font-bold">MELLA'S TECH UPGRADE</h3>
                        <p class="text-sm">UP TO 40% OFF</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>