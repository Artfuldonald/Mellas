{{-- resources/views/components/admin-nav-dropdown.blade.php --}}
@props([
    'title',
    'icon', 
    'childRoutes' => '[]', 
])

<div class="admin-nav-dropdown-container">
    <button type="button"
            {{-- Restore passing the actual JSON string prop --}}
            data-child-routes="{{ $childRoutes }}"
            data-dropdown-trigger
            @class([
                'flex items-center w-full px-3 py-2 text-sm font-medium rounded-md group transition-colors duration-150 ease-in-out',
                'justify-between',
                'text-pink-100 hover:bg-white/5 hover:text-white', // Default inactive
            ])
            title="{{ $title }}"
            aria-expanded="false"
    >
        <div class="flex items-center pointer-events-none min-w-0">
            {{-- FIX: Pass component name as a STRING --}}
            {{-- Also, revert to using the $icon prop correctly --}}
            <x-dynamic-component
                :component="'heroicon-o-' . $icon" {{-- <<< CORRECTED: Use quotes and concatenate --}}
                @class([
                    'sidebar-icon',
                    'h-5 w-5 flex-shrink-0',
                    'mr-3',
                    'text-pink-200 group-hover:text-white', // Default inactive
                ])
            />
            <span class="sidebar-text truncate transition-opacity duration-200 pointer-events-none">
                {{ $title }}
            </span>
        </div>
        <x-heroicon-o-chevron-right
            @class([
                 'sidebar-arrow',
                 'h-4 w-4 transition-transform duration-200 flex-shrink-0',
                 'text-pink-200 group-hover:text-white', // Default inactive
            ])
        />
    </button>

    <div
         data-dropdown-content
         @class([
             'sidebar-dropdown-content mt-1 space-y-1 overflow-hidden pl-5',
             'max-h-0 transition-max-height duration-300 ease-in-out'
         ])
         style="max-height: 0px;"
    >
        {{-- Restore the slot --}}
        {{ $slot }}
    </div>
</div>