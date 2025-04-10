{{-- Assuming you have a resources/views/components/featured-product.blade.php --}}
@props([
    'products' // Expect a collection of products
])

<section class="py-16 bg-pink-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Featured Products</h2>
        
        @if($products && $products->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Loop through the passed $products collection --}}
                @foreach ($products as $product)
                    {{-- Pass the individual $product object to the card component --}}
                    <x-product-card :product="$product" /> 
                @endforeach
            </div>
            <div class="text-center mt-10">
                <x-primary-button href="#"> {{-- Link to a shop page --}}
                    View All Products
                </x-primary-button>
            </div>
        @else
             <p class="text-center text-gray-500">No featured products available right now.</p>
        @endif
    </div>
</section>