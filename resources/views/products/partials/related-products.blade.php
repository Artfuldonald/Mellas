<div class="space-y-6">
    <h2 class="text-2xl font-bold text-center text-gray-800">You May Also Like</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($relatedProducts as $relatedProduct)
            {{-- Use your existing, working Blade component for consistency --}}
            <x-product-card-small :product="$relatedProduct" :userWishlistProductIds="$userWishlistProductIds ?? []" />
        @endforeach
    </div>
</div>
