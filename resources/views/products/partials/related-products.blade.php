@if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
    
    <x-slider title="You May Also Like" >
        
        @foreach ($relatedProducts as $relatedProduct)         
            <div class="flex-shrink-0 w-48 sm:w-56">
                <x-product-card-small 
                    :product="$relatedProduct" 
                    :userWishlistProductIds="$userWishlistProductIds ?? []" 
                />
            </div>
        @endforeach

    </x-slider>

@endif