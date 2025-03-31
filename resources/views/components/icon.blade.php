@props([
    'name',
    'href' => '#'
])

<a href="{{ $href }}" class="text-gray-600 hover:text-pink-500 transition relative">
    <i class="{{ $name }}"></i>
    {{ $slot }}
</a>