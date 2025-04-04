@props([
    'name' => '',
    'href' => '#'
])

<a href="{{ $href }}" class="text-gray-600 hover:text-pink-500 transition relative mt-1">
    <i class="{{ $name }}"></i>
    {{ $slot }}
</a>