<!-- resources/views/components/admin-nav-item.blade.php -->
@props(['route', 'icon', 'active' => false])

@php
$isActive = $active || request()->routeIs($route);
@endphp

<a href="{{ route($route) }}" 
   class="flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ $isActive ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
>   
    <span class="transition-opacity duration-300">
        {{ $slot }}
    </span>
</a>     