{{-- views/products/partials/_sort-modal.blade.php --}}
<div x-show="sortModalOpen"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-end"
     role="dialog"
     aria-modal="true"
     aria-labelledby="sort-modal-title"
     x-cloak
>
    <!-- Overlay -->
    <div @click="sortModalOpen = false" class="fixed inset-0 bg-black bg-opacity-40" aria-hidden="true"></div>

    <!-- Bottom Sheet Panel -->
    <div x-show="sortModalOpen"
         x-transition:enter="transition ease-in-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in-out duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="relative w-full bg-white rounded-t-xl shadow-xl">
        
        <!-- Header -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 id="sort-modal-title" class="text-lg font-medium text-gray-900">Sort by</h2>
                <button @click="sortModalOpen = false" type="button" class="p-2 text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Close menu</span>
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                </button>
            </div>
        </div>

        <!-- Sorting Options -->
        <div class="p-4">
            <ul class="space-y-1">
                @php
                    // Define our sorting options here for easy management
                    $sortOptions = [
                        'latest' => 'Newest Arrivals',
                        'rating_desc' => 'Customer Rating',
                        'price_asc' => 'Price: Low to High',
                        'price_desc' => 'Price: High to Low',
                    ];
                @endphp

                @foreach($sortOptions as $value => $label)
                    <li>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => $value]) }}"
                           class="flex items-center justify-between p-3 rounded-md transition-colors {{ $sortOrder == $value ? 'bg-pink-50' : 'hover:bg-gray-100' }}">
                            <span class="text-sm font-medium {{ $sortOrder == $value ? 'text-pink-700' : 'text-gray-700' }}">
                                {{ $label }}
                            </span>
                            
                            {{-- Custom Radio Button --}}
                            <div class="w-5 h-5 flex items-center justify-center rounded-full border {{ $sortOrder == $value ? 'border-pink-600' : 'border-gray-300' }}">
                                @if($sortOrder == $value)
                                    <div class="w-2.5 h-2.5 bg-pink-600 rounded-full"></div>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>