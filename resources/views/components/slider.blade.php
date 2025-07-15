@props([
    'title',
    'viewAllUrl' => null, 
    'themeColor' => 'pink'
])

{{-- This style block is the correct way to hide the scrollbar --}}
<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none; 
        scrollbar-width: none; 
    }
</style>

<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="flex items-center justify-between mb-4 sm:mb-6 bg-{{ $themeColor }}-600 text-white p-3 rounded-md">
            <h2 class="text-xl sm:text-2xl font-bold">{{ $title }}</h2>

            {{-- ***** START: OPTIONAL LINK LOGIC ***** --}}
            {{-- This link will only be rendered if you provide a `viewAllUrl` --}}
            @if($viewAllUrl)
                <a href="{{ $viewAllUrl }}" class="text-white hover:underline font-medium flex items-center text-sm">
                    See All <x-heroicon-o-chevron-right class="w-4 h-4 ml-1" />
                </a>
            @endif
            {{-- ***** END: OPTIONAL LINK LOGIC ***** --}}

        </div>

        {{-- Generic Slider with Alpine.js for navigation --}}
        <div x-data="productSlider()" class="relative">
            <!-- Left Arrow -->
            <button x-show="!atStart" @click="prev()"
                    class="absolute top-1/2 -left-0 md:-left-4 z-20 -translate-y-1/2 bg-white/80 backdrop-blur-sm p-2 rounded-full shadow-md hover:bg-white focus:outline-none focus:ring-2 focus:ring-{{ $themeColor }}-500">
                <x-heroicon-o-chevron-left class="w-6 h-6 text-gray-700" />
            </button>
           
            <div x-ref="slider" @scroll="checkScroll()"
                 class="hide-scrollbar flex overflow-x-auto space-x-4 pb-4 scroll-smooth">              
                {{ $slot }}                
            </div>

            <!-- Right Arrow -->
            <button x-show="!atEnd" @click="next()"
                    class="absolute top-1/2 -right-0 md:-right-4 z-20 -translate-y-1/2 bg-white/80 backdrop-blur-sm p-2 rounded-full shadow-md hover:bg-white focus:outline-none focus:ring-2 focus:ring-{{ $themeColor }}-500">
                <x-heroicon-o-chevron-right class="w-6 h-6 text-gray-700" />
            </button>
        </div>
    </div>
</section>