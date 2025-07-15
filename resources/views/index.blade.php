{{-- resources/views/index.blade.php --}}
<x-app-layout>
    {{-- This page assumes variables like $topSellingProducts, $allProducts, $electronicsProducts, etc.
         are passed from your HomeController. --}}

    <!-- Main Content Area -->
    <div class="space-y-6">
        <!-- Hero and Category Grid Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Hero Section (e.g., a large banner) -->
                <div class="lg:w-3/4">
                    <div class="bg-pink-100 rounded-lg h-48 lg:h-96 flex items-center justify-center">
                        <span class="text-2xl font-semibold text-pink-700">Your Hero Banner / Slideshow Here</span>
                    </div>
                </div>
                <!-- Side Banners -->
                <div class="lg:w-1/4 space-y-4">
                    <div class="bg-pink-50 rounded-lg h-full flex items-center justify-center p-4">
                        <span class="text-center text-pink-600">Promo 1</span>
                    </div>
                     <div class="bg-pink-50 rounded-lg h-full flex items-center justify-center p-4">
                        <span class="text-center text-pink-600">Promo 2</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Category Links -->
        @if(isset($navCategories) && $navCategories->isNotEmpty())
        <section class="py-8 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                    @foreach($navCategories as $category)
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                           class="block p-2 bg-gray-50 rounded-lg shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
                            @if($category->image)
                                <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="w-16 h-16 mx-auto mb-2 object-contain rounded-md">
                            @else
                                <div class="w-16 h-16 mx-auto mb-2 bg-pink-100 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-tag class="w-8 h-8 text-pink-400"/>
                                </div>
                            @endif
                            <span class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-pink-600">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
        
        {{--  Top Selling Products Section 
        <x-product-slider-section 
            title="Top Selling Products"
            viewAllUrl="{{ route('products.index', ['sort' => 'top_selling']) }}"
            :products="$topSellingProducts"
        /> 

        <!-- All Products Section 
        <x-product-slider-section 
            title="Discover All Products"
            viewAllUrl="{{ route('products.index') }}"
            :products="$allProducts"
        /> 
        
        <!-- Electronics Section -->
        <x-product-slider-section 
            title="Latest in Electronics"
            viewAllUrl="{{ route('products.index', ['category' => 'electronics']) }}"
            :products="$electronicsProducts"
        />

        <!-- Groceries Section -->
        <x-product-slider-section 
            title="Groceries & Essentials"
            viewAllUrl="{{ route('products.index', ['category' => 'groceries']) }}"
            :products="$groceriesProducts"
        /> --}}

    </div>
    @pushOnce('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function handleSmallCardFormSubmit(form, event) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            if (button) button.disabled = true; // Simple loading state

            const formData = new FormData(form);
            const plainFormData = Object.fromEntries(formData.entries());

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token'), // Make sure CSRF token is in your small card forms
                    'Accept': 'application/json',
                },
                body: JSON.stringify(plainFormData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // console.log(data.message);
                    if (form.classList.contains('add-to-cart-form-small')) {
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
                        if(button && button.querySelector('svg')) { // If button has an icon
                            const originalIconHTML = button.innerHTML;
                            button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-green-500"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>`;
                            setTimeout(() => { button.innerHTML = originalIconHTML; }, 1500);
                        }
                    } else if (form.classList.contains('add-to-wishlist-form-small')) {
                        // For wishlist, usually a page reload or more complex state update is needed
                        // to correctly toggle the icon based on server state.
                        // For now, we can dispatch an event for the header and optionally try a visual toggle.
                        window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { productId: plainFormData.product_id, wasAdded: !form.action.includes('remove') } }));
                        // If you want to attempt a visual toggle (might get out of sync without full re-render):
                        if(button && button.querySelector('svg')) {
                            // This logic would need to be smarter or tied to an Alpine component state
                            // For simplicity, we'll assume the action toggles the state visually
                            const isCurrentlyInWishlist = form.action.includes('remove');
                            if(isCurrentlyInWishlist) { // Was remove, so now it's out, show outline
                                // button.innerHTML = `<x-heroicon-o-heart class="w-3.5 h-3.5" />`; // Blade doesn't work here
                            } else { // Was add, so now it's in, show solid
                                // button.innerHTML = `<x-heroicon-s-heart class="w-3.5 h-3.5 text-pink-500" />`;
                            }
                            // A page refresh or navigating away and back will show correct state from server.
                        }
                    }
                    // You might want a more global notification system (Toastr, Noty, etc.)
                    // alert(data.message); // Simple alert for now
                } else {
                    alert(data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred processing your request.');
            })
            .finally(() => {
                if (button) button.disabled = false;
            });
        }

        // Use event delegation for dynamically added cards if sections are loaded via AJAX
        // For now, direct binding is fine if cards are present on initial load.
        document.body.addEventListener('submit', function(event) {
            if (event.target.matches('.add-to-cart-form-small')) {
                handleSmallCardFormSubmit(event.target, event);
            } else if (event.target.matches('.add-to-wishlist-form-small')) {
                handleSmallCardFormSubmit(event.target, event);
            }
        });
    });
    </script>
    @endPushOnce
</x-app-layout>
