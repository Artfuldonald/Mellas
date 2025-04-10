{{-- resources/views/components/admin-nav-item.blade.php --}}
@props([
    'route',
    'icon',
    'params' => [],
])

@php
    $isActive = request()->routeIs($route) || request()->routeIs($route.'.*');
    $href = '#';
    if (Route::has($route)) { try { $href = route($route, $params); } catch (\Exception $e) {} }
    if ($href === '#' && Route::has($route.'.index')) { try { $href = route($route.'.index', $params); } catch (\Exception $e) {} }
    $titleAttribute = strip_tags($slot);
@endphp

<a href="{{ $href }}"
    @class([
        'sidebar-nav-link', // Marker class
        'flex items-center px-3 py-2 text-sm font-medium rounded-md transition-all duration-150 ease-in-out group',
        'justify-start', // Default state (CSS will override if collapsed)
        'bg-white/10 text-white' => $isActive,
        'text-pink-100 hover:bg-white/5 hover:text-white' => !$isActive,        
    ])
    title="{{ $titleAttribute }}" {{-- Default title --}}
>
    <x-dynamic-component
        :component="'heroicon-o-'.$icon"
        @class([
            'sidebar-icon', // Marker class
            'h-5 w-5 flex-shrink-0',
            'mr-3', // Default state (CSS will override if collapsed)
            'text-white' => $isActive,
           'text-pink-200 group-hover:text-white' => !$isActive,
        ])
    />
    <span class="sidebar-text truncate transition-opacity duration-200"> {{-- Marker class --}}
        {{ $slot }}
    </span>
</a>