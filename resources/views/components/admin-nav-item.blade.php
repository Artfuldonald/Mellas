{{-- resources/views/components/admin-nav-item.blade.php --}}
@props([
    'route',
    'icon',
    'params' => [],
])

@php    
$href = '#';
if (Route::has($route)) { try { $href = route($route, $params); } catch (\Exception $e) {} }
// Fallback to .index if main route doesn't exist but index does (common for resources)
if ($href === '#' && !Route::has($route) && Route::has($route.'.index')) { try { $href = route($route.'.index', $params); } catch (\Exception $e) {} }

$titleAttribute = strip_tags($slot);
@endphp

<a href="{{ $href }}"
    data-route-name="{{ $route }}"
    @class([
        'sidebar-nav-item', 
        'flex items-center w-full px-3 py-2 text-sm font-medium rounded-md transition-all duration-150 ease-in-out group',
        'justify-start', 
        'text-pink-100 hover:bg-white/5 hover:text-white',
           ])
    title="{{ $titleAttribute }}">
    
<x-dynamic-component
        :component="'heroicon-o-'.$icon"
        @class([
            'sidebar-icon',
            'h-5 w-5 flex-shrink-0',
            'mr-3', 
            'text-pink-200 group-hover:text-white',
        ])
    />
    <span class="sidebar-text truncate transition-opacity duration-200"> 
        {{ $slot }}
    </span>
</a>