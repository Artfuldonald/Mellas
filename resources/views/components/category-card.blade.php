@props([
    'name',
    'image' => '/placeholder.svg?height=300&width=300'
])

<div class="bg-white rounded-lg shadow-md overflow-hidden group">
    <div class="relative">
        <img src="{{ $image }}" alt="{{ $name }}"
            class="w-full h-64 object-cover group-hover:scale-105 transition duration-300">
        <div
            class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-40 transition duration-300">
        </div>
    </div>
    <div class="p-4 text-center">
        <h3 class="text-xl font-semibold mb-1">{{ $name }}</h3>
        <a href="#" class="text-pink-500 hover:underline">Shop Now</a>
    </div>
</div>