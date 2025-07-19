@props(['active'])

@php
$classes = ($active ?? false)
            ? 'bg-pink-50 text-pink-700 font-semibold'
            : 'text-gray-600 hover:bg-pink-50 hover:text-pink-600';
@endphp

<a {{ $attributes->merge(['class' => 'flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors ' . $classes]) }}>
    {{ $slot }}
</a>