{{-- resources/views/components/admin-nav-dropdown.blade.php --}}
@props([
    'title',
    'icon',
    'childRoutes' => [],
])

@php
    $isActive = false;
    foreach ($childRoutes as $childRoute) {
        // Ensure route names passed are valid before checking
        if (is_string($childRoute) && Route::has($childRoute) && (request()->routeIs($childRoute) || request()->routeIs($childRoute.'.*'))) {
            $isActive = true;
            break;
        }
    }
    $initialDropdownOpen = $isActive;
@endphp

<div>
    <button type="button"
            @class([
                'sidebar-nav-dropdown-trigger', // Keep marker
                'flex items-center w-full px-3 py-2 text-sm font-medium rounded-md group transition-colors duration-150 ease-in-out',
                'justify-between',
                // Update colors for pink background
                'bg-white/10 text-white' => $isActive,
                'text-pink-100 hover:bg-white/5 hover:text-white' => !$isActive,
            ])
            title="{{ $title }}"
            aria-expanded="{{ $initialDropdownOpen ? 'true' : 'false' }}"
    >
        <div class="flex items-center">
            <x-dynamic-component
                :component="'heroicon-o-'.$icon"
                @class([
                    'sidebar-icon', 'h-5 w-5 flex-shrink-0', 'mr-3',
                    // Update icon colors for pink background
                    'text-white' => $isActive,
                    'text-pink-200 group-hover:text-white' => !$isActive,
                ])
            />
            <span class="sidebar-text truncate transition-opacity duration-200">
                {{ $title }}
            </span>
        </div>
        <x-heroicon-o-chevron-right
            @class([
                 'sidebar-arrow', 'h-4 w-4 transition-transform duration-200',
                 'is-open' => $initialDropdownOpen, // PHP sets initial class
                  // Update arrow colors for pink background
                 'text-white' => $isActive,
                 'text-pink-200 group-hover:text-white' => !$isActive,
            ])
        />
    </button>

    <div
         @class([
             'sidebar-dropdown-content mt-1 space-y-1', // Keep marker
             'is-open' => $initialDropdownOpen, // PHP sets initial class
         ])
         {{-- REMOVED @style directive --}}
         {{-- CSS will handle display based on 'is-open' class --}}
    >
        {{ $slot }}
    </div>
</div>