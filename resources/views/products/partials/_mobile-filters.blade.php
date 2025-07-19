{{-- This component is controlled by the `mobileFilterManager` in products/index.blade.php --}}
<div x-show="isOpen"
     x-transition:enter="transition ease-in-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:leave="transition ease-in-out duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex lg:hidden" x-cloak>

    <!-- Overlay -->
    <div @click="isOpen = false" class="fixed inset-0 bg-black bg-opacity-25"></div>

    <!-- Filter Drawer -->
    <div class="relative flex w-full max-w-xs flex-col overflow-y-hidden bg-white shadow-xl">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <button @click="navigateBack()" type="button" class="p-2 text-gray-500" x-show="viewHistory.length > 0">
                <x-heroicon-o-arrow-left class="h-6 w-6"/>
            </button>
            <h2 class="text-lg font-medium text-gray-900" x-text="currentTitle"></h2>
            <button @click="isOpen = false" type="button" class="p-2 text-gray-400">
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>
        </div>

        {{-- Main Content - This part scrolls --}}
        <div class="flex-grow overflow-y-auto">
            <div class="relative w-full">
                {{-- Main Filter View --}}
                <div x-show.transition.opacity.duration.200="currentView === 'main'">
                    @include('products.partials.filters._main')
                </div>
                {{-- Category View --}}
                <div x-show.transition.opacity.duration.200="currentView === 'category'">
                    @include('products.partials.filters._category')
                </div>
                {{-- Brand View --}}
                <div x-show.transition.opacity.duration.200="currentView === 'brand'">
                    @include('products.partials.filters._brand')
                </div>
            </div>
        </div>

        {{-- Footer Buttons --}}
        <div class="border-t border-gray-200 px-4 py-3">
            <div class="grid grid-cols-2 gap-4">
                <button @click="resetFilters()" type="button" class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Reset
                </button>
                <button @click="applyFilters()" type="button" class="w-full rounded-md border border-transparent bg-pink-600 px-4 py-2 text-center text-sm font-medium text-white shadow-sm hover:bg-pink-700">
                    Show Results
                </button>
            </div>
        </div>
    </div>
</div>