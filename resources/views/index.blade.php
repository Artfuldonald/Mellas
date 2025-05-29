{{-- resources/views/index.blade.php --}}
<x-app-layout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row lg:space-x-8">
            {{-- Main Content Area (Hero, Promotions, Category Cards) --}}
            <main class="w-full lg:w-4/5 xl:w-3/4 min-w-0 space-y-12">
                <x-hero-section />

                @if(isset($navCategories) && $navCategories->isNotEmpty())
                    <section>
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Shop by Department</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                            @foreach($navCategories->take(12) as $category) {{-- Show some top-level --}}
                                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                                   class="block p-4 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow text-center group">
                                    @if($category->image)
                                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="w-20 h-20 mx-auto mb-3 object-contain rounded-md">
                                    @else
                                        <div class="w-20 h-20 mx-auto mb-3 bg-pink-50 rounded-full flex items-center justify-center">
                                            <x-heroicon-o-tag class="w-10 h-10 text-pink-400"/>
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-pink-600">{{ $category->name }}</span>
                                </a>
                            @endforeach
                        </div>
                        @if($navCategories->count() > 12)
                            <div class="text-center mt-6">
                                <button @click="$dispatch('open-all-categories-menu')" class="text-pink-600 hover:underline font-medium">
                                    View All Departments
                                </button>
                            </div>
                        @endif
                    </section>
                @endif

                @isset($sponsoredProducts)
                <section class="py-8 sm:py-12 bg-pink-50">
                    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center mb-4 sm:mb-6">
                            <h2 class="text-xl sm:text-2xl font-semibold text-pink-800">Sponsored Products</h2>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
                            @php $userWishlistProductIds = Auth::check() ? Auth::user()->wishlistItems()->pluck('product_id')->toArray() : []; @endphp
                            @foreach($sponsoredProducts as $product)
                                <x-product-card-small :product="$product" :userWishlistProductIds="$userWishlistProductIds" />
                            @endforeach
                        </div>
                    </div>
                </section>
    @endisset
            </main>

            {{-- Right Sidebar for "Call to Order", "Flash Sales" --}}
            <aside class="w-full lg:w-1/5 xl:w-1/4 flex-shrink-0 mt-8 lg:mt-0">
               <div class="space-y-6 sticky top-24">
                    <div class="bg-white p-4 shadow rounded-lg text-center">
                        <h3 class="font-semibold text-gray-700 mb-2">CALL TO ORDER</h3>
                        <p class="text-xl font-bold text-pink-600">030 274 0642</p>
                    </div>
                    <div class="bg-white p-4 shadow rounded-lg text-center">
                        <h3 class="font-semibold text-gray-700 mb-2">FLASH SALES</h3>
                        <p class="text-gray-500">Amazing deals coming soon!</p>
                    </div>
                    {{-- Placeholder for Jumia Tech Upgrade style banner --}}
                    <div class="bg-blue-500 text-white p-6 rounded-lg text-center">
                        <h3 class="text-xl font-bold">MELLA'S TECH UPGRADE</h3>
                        <p class="text-sm">UP TO 40% OFF</p>
                    </div>
                </div>
            </aside>
        </div>
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
