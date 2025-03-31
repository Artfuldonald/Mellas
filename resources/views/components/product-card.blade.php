<!-- resources/views/components/product-card.blade.php -->
@props([
    'name' => 'Wireless Headphones',
    'image' => '/placeholder.svg?height=300&width=300',
    'price' => 129.99,
    'rating' => 4.5,
    'reviewCount' => 24
])
<x-panel>
    <div class="relative">
        <img src="{{ $image }}" alt="{{ $name }}"
            class="w-full h-64 object-cover">           
    </div>
    <div class="p-4">
        <h3 class="text-lg font-semibold mb-2 group-hover:text-pink-500 transition-colors duration-300">{{ $name }}</h3>
        <div class="flex items-center mb-2">
            <div class="flex text-yellow-400">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= floor($rating))
                        <i class="fas fa-star"></i>
                    @elseif ($i - 0.5 <= $rating)
                        <i class="fas fa-star-half-alt"></i>
                    @else
                        <i class="far fa-star"></i>
                    @endif
                @endfor
            </div>
            <span class="text-gray-500 text-sm ml-2">({{ $reviewCount }} reviews)</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-pink-500 font-bold">${{ number_format($price, 2) }}</span>
            <div class="flex space-x-2">
                <button class="bg-white text-pink-500 p-2 rounded-full hover:bg-pink-500 hover:text-white transition">
                    <i class="fas fa-heart"></i>
                </button>
            <x-cart-button></x-cart-button>
            </div>
        </div>
    </div>
</x-panel>