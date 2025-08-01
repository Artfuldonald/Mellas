{{--views/products/partials/related-products.php --}}
@if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
    
    <x-slider title="You May Also Like" >
        
        @foreach ($relatedProducts as $relatedProduct)        
           
            <div class="flex-shrink-0 w-40">
                <x-product-card-small 
                    :product="$relatedProduct" 
                    :userWishlistProductIds="$userWishlistProductIds ?? []" 
                />
            </div>
        @endforeach

    </x-slider>

@endif