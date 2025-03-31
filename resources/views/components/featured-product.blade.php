<!-- Featured Products -->
<section class="py-16 bg-pink-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Featured Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <x-product-card 
                name="Bluetooth Speaker"
                :image="'/speaker.jpg'"
                :price="34.99"
                :rating="2"
                :review-count="42"
            />
            <x-product-card 
                name="Mobile Phone"
                :image="'/speaker.jpg'"
                :price="69.99"
                :rating="5"
                :review-count="42"
            />
            <x-product-card 
                name="Tablet"
                :image="'/speaker.jpg'"
                :price="979.99"
                :rating="2"
                :review-count="42"
            />
            <x-product-card 
                name=" Speaker"
                :image="'/speaker.jpg'"
                :price="39.99"
                :rating="1"
                :review-count="42"
            />
           
            
        </div>
        <div class="text-center mt-10">
            <x-primary-button href="">View All Products</x-primary-button>
        </div>
    </div>
</section>