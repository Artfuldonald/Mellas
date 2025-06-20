<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Mella\'s Connect') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles & Scripts (Using Vite) -->
        @vite(['resources/css/app.css', 'resources/js/app.js']) 

        @stack('styles')
        <style>
            .custom-scrollbar-mobile::-webkit-scrollbar { width: 5px; }
            .custom-scrollbar-mobile::-webkit-scrollbar-track { background: #f9fafb; /* gray-50 */ border-radius: 10px; }
            .custom-scrollbar-mobile::-webkit-scrollbar-thumb { background: #d1d5db; /* gray-300 */ border-radius: 10px; }
            .custom-scrollbar-mobile::-webkit-scrollbar-thumb:hover { background: #9ca3af; /* gray-500 */ }
            [x-cloak] { display: none !important; }
            input[type=number]::-webkit-inner-spin-button,
            input[type=number]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            /* For Firefox */
            input[type=number] {
                -moz-appearance: textfield;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-800 bg-gray-50">

        {{-- Data attribute for JS to know current route (used by sidebar active states if any) --}}
        <div class="min-h-screen flex flex-col" data-current-route="{{ Route::currentRouteName() ?? '' }}">
          
            <x-header />
           
            <main class="flex-grow">               
                {{ $slot }}
            </main>
           
            <x-footer />

            {{-- ***** "ALL CATEGORIES" OFF-CANVAS MENU (Amazon Style) ***** --}}
            <div id="allCategoriesOffcanvasMenu"
                 class="fixed inset-0 z-50 flex"
                 x-data="amazonCategorySidebar({
                    allCategories: {{ Js::from(                       
                        ($navCategories ?? collect())->map(function ($l1Category) {
                            return [
                                'id' => $l1Category->id,
                                'name' => $l1Category->name,
                                'slug' => $l1Category->slug,
                                'isLinkOnly' => $l1Category->children->isEmpty(),
                                'children' => $l1Category->children->map(function ($l2Category) {
                                    return [
                                        'id' => $l2Category->id,
                                        'name' => $l2Category->name,
                                        'slug' => $l2Category->slug,
                                        'isLinkOnly' => $l2Category->children->isEmpty(),
                                        'children' => $l2Category->children->map(fn($l3Category) => [
                                            'id' => $l3Category->id,
                                            'name' => $l3Category->name,
                                            'slug' => $l3Category->slug,
                                            'isLinkOnly' => true 
                                        ])->values()->all() 
                                    ];
                                })->values()->all() 
                            ];
                        })->values()->all() 
                    ) }}
                 })"
                 x-init="
                    console.log('Alpine Initialized for All Categories Menu.');
                    // console.log('Processed allCategories in Alpine:', JSON.parse(JSON.stringify(allCategoriesData))); // Use allCategoriesData
                 "
                 @open-all-categories-menu.window="open()"
                 @keydown.escape.window="if(isOpen) close();"
                 x-show="isOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak
                 style="display: none;"
                 role="dialog" aria-modal="true" aria-labelledby="all-categories-title"
            >
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-black/50" @click="close()" aria-hidden="true" x-show="isOpen"></div>

                {{-- Menu Panel --}}
                <div class="fixed left-0 top-0 h-full w-80 max-w-[85vw] sm:max-w-xs bg-white shadow-lg flex flex-col
                            transform transition-transform duration-300 ease-in-out"
                     :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
                     @click.outside="close()" {{-- Close if click is outside this panel --}}
                >
                    {{-- Header of the Off-Canvas Menu --}}
                    <div class="bg-pink-600 text-white p-4 flex items-center justify-between flex-shrink-0 sticky top-0 z-10">
                        <div class="flex items-center">
                            <button x-show="history.length > 0" @click="navigateBack()"
                                    class="mr-2 rounded-full p-1 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-white">
                                <x-heroicon-o-arrow-left class="h-5 w-5" />
                                <span class="sr-only">Back</span>
                            </button>
                            <span id="all-categories-title" class="text-lg font-bold truncate" x-text="currentTitle"></span>
                        </div>
                        <button @click="close()"
                                class="rounded-full p-1 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-white">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                            <span class="sr-only">Close sidebar</span>
                        </button>
                    </div>
                    {{-- Parent category title (shown in subcategory views) --}}
                    <div x-show="parentTitleStack.length > 0 && currentView !== 'main'"
                         class="px-4 pt-2 pb-1 text-xs text-gray-500 bg-gray-50 border-b"
                         x-text="parentTitleStack[parentTitleStack.length - 1]">
                    </div>

                    {{-- Content - Main Categories or Subcategories --}}
                    <div class="overflow-y-auto flex-grow custom-scrollbar-mobile p-2">
                        {{-- Level 1 Categories (Main Menu) --}}
                        <ul x-show="currentView === 'main'" class="divide-y divide-gray-100">
                            <li x-show="allCategoriesData.length === 0" class="p-3 text-sm text-gray-500">
                                No categories available.
                            </li>
                            <template x-for="categoryL1 in allCategoriesData" :key="categoryL1.id">
                                <li class="hover:bg-pink-50">
                                    <div @click="handleClick(categoryL1, 1)"
                                         class="flex items-center justify-between p-3 text-sm text-gray-700 cursor-pointer">
                                        <span x-text="categoryL1.name"></span>
                                        <x-heroicon-o-chevron-right class="h-4 w-4 text-gray-400" x-show="!categoryL1.isLinkOnly" />
                                    </div>
                                </li>
                            </template>
                        </ul>

                        {{-- Level 2 Categories --}}
                        <ul x-show="currentView === 'level2'" class="divide-y divide-gray-100">
                            <template x-for="categoryL2 in currentItems" :key="categoryL2.id">
                                <li class="hover:bg-pink-50">
                                     <div @click="handleClick(categoryL2, 2)"
                                         class="flex items-center justify-between p-3 text-sm text-gray-700 cursor-pointer">
                                        <span x-text="categoryL2.name"></span>
                                        <x-heroicon-o-chevron-right class="h-4 w-4 text-gray-400" x-show="!categoryL2.isLinkOnly" />
                                    </div>
                                </li>
                            </template>
                        </ul>

                         {{-- Level 3 Categories --}}
                        <ul x-show="currentView === 'level3'" class="divide-y divide-gray-100">
                            <template x-for="categoryL3 in currentItems" :key="categoryL3.id">
                                <li class="hover:bg-pink-50">
                                    {{-- L3 items are always links --}}
                                    <a :href="'{{ url('/products') }}?category=' + categoryL3.slug"
                                       class="flex items-center justify-between p-3 text-sm text-gray-700">
                                        <span x-text="categoryL3.name"></span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                        <p x-show="currentView !== 'main' && currentItems.length === 0" class="p-3 text-sm text-gray-500 italic">
                            No further subcategories.
                        </p>
                    </div>
                </div>
            </div>
            {{-- ***** END "ALL CATEGORIES" OFF-CANVAS MENU ***** --}}


            {{-- ***** MAIN MOBILE NAVIGATION OFF-CANVAS MENU (for Account, Help, etc.) ***** --}}
            {{-- This remains largely the same as before, triggered by #mainMobileNavToggleBtn --}}
            <div id="mainMobileOffcanvasMenu"
                 class="fixed inset-0 z-50 flex justify-end transform translate-x-full transition-transform duration-300 ease-in-out"
                 x-data="{ open: false }"
                 @open-main-mobile-nav.window="open = true; document.body.style.overflow = 'hidden';"
                 @close-main-mobile-nav.window="open = false; document.body.style.overflow = '';"
                 @keydown.escape.window="if(open){ open = false; document.body.style.overflow = ''; }"
                 x-show="open" x-cloak style="display: none;"
                 role="dialog" aria-modal="true" aria-labelledby="main-mobile-nav-title">
                <div class="fixed inset-0 bg-black/50" @click="open = false; document.body.style.overflow = '';" aria-hidden="true"></div>
                <div class="relative w-4/5 max-w-xs bg-white h-full shadow-xl flex flex-col overflow-y-auto custom-scrollbar-mobile">
                    <div class="flex items-center justify-between p-4 border-b sticky top-0 bg-white z-10">
                        <h2 id="main-mobile-nav-title" class="text-lg font-semibold text-pink-600">Menu</h2>
                        <button @click="open = false; document.body.style.overflow = '';" class="p-1 text-gray-500 hover:text-pink-600">
                            <x-heroicon-o-x-mark class="w-6 w-6"/>
                        </button>
                    </div>
                    <nav class="flex-grow p-4 space-y-2">
                        @guest
                            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-pink-600 hover:bg-pink-50">Sign In</a>
                            <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-pink-600 hover:bg-pink-50">Create Account</a>
                        @else
                            <a href="{{ Auth::user()->is_admin ? route('admin.dashboard') : route('profile.edit') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-pink-600 hover:bg-pink-50">My Account</a>
                            <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure?');"> @csrf <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-pink-600 hover:bg-pink-50">Sign out</button></form>
                        @endguest
                        <div class="pt-4 border-t mt-2">
                            <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-pink-600 hover:bg-pink-50">Help Center</a>
                        </div>
                    </nav>
                </div>
            </div>
            {{-- ***** END MAIN MOBILE NAVIGATION OFF-CANVAS MENU ***** --}}

            <x-toast-notifications />
            
        </div>{{-- End min-h-screen --}}

        @stack('modals')
        @stack('scripts')
       
        <script>
            // Alpine.js component for the Amazon-style category sidebar            
            if (typeof window.amazonCategorySidebar === 'undefined') {
                window.amazonCategorySidebar = function(config) {
                    return {
                        isOpen: false,
                        allCategoriesData: config.allCategories || [], 
                        currentView: 'main',   
                        currentTitle: 'Shop By Department',
                        currentItems: [],      
                        history: [],          
                        parentTitleStack: [], 

                        init() {
                            this.currentItems = this.allCategoriesData; 
                            
                        },
                        open() {
                            this.isOpen = true;
                            document.body.style.overflow = 'hidden';
                        },
                        close() {
                            this.isOpen = false;
                            document.body.style.overflow = '';
                            setTimeout(() => { // Reset state after transition
                                this.currentView = 'main';
                                this.currentTitle = 'Shop By Department';
                                this.currentItems = this.allCategoriesData;
                                this.history = [];
                                this.parentTitleStack = [];
                            }, 300);
                        },
                        navigateTo(itemClicked, currentLevel) {
                            // Save current state to history
                            this.history.push({
                                view: this.currentView,
                                title: this.currentTitle,
                                items: this.currentItems,
                                parentTitles: [...this.parentTitleStack] // Copy stack
                            });
                            this.parentTitleStack.push(this.currentTitle); // Add current title as parent for next view

                            // Set new state
                            this.currentView = (currentLevel === 1) ? 'level2' : 'level3'; // Generic view state
                            this.currentTitle = itemClicked.name;
                            this.currentItems = Object.values(itemClicked.children || {});
                        },
                        navigateBack() {
                            if (this.history.length > 0) {
                                const previousState = this.history.pop();
                                this.currentView = previousState.view;
                                this.currentTitle = previousState.title;
                                this.currentItems = previousState.items;
                                this.parentTitleStack = previousState.parentTitles;
                            }
                        },
                        // This method is called when an item is clicked in the list
                        handleItemClick(item, levelCurrentlyDisplayed) {
                            const targetUrl = '{{ url("/products") }}?category=' + item.slug;
                            if (item.isLinkOnly || (item.children && Object.keys(item.children).length === 0)) {
                                window.location.href = targetUrl; // Navigate if no children or explicitly a link
                            } else {
                                // It has children, so navigate to the next level in the sidebar
                                this.navigateTo(item, levelCurrentlyDisplayed + 1); // Incorrect, navigateTo needs different params
                                // Corrected call:
                                this.navigateTo(item.id, item.name); // Pass item ID and name
                            }
                        },
                        // Corrected handleClick for the templates
                        // item is the category object, currentLevelDisplayed is 1, 2, or 3
                        handleClick(item, currentLevelDisplayed) {
                            const targetUrl = '{{ url("/products") }}?category=' + item.slug;
                            if (item.isLinkOnly || !item.children || Object.keys(item.children).length === 0) {
                                window.location.href = targetUrl;
                            } else {
                                // Save current state to history
                                this.history.push({
                                    view: this.currentView,
                                    title: this.currentTitle,
                                    items: this.currentItems,
                                    parentTitles: [...this.parentTitleStack]
                                });
                                this.parentTitleStack.push(this.currentTitle);

                                // Set new state based on what level we are going to
                                if (currentLevelDisplayed === 1) { // Clicked L1, going to L2
                                    this.currentView = 'level2';
                                    this.currentItems = Object.values(item.children);
                                } else if (currentLevelDisplayed === 2) { // Clicked L2, going to L3
                                    this.currentView = 'level3';
                                    this.currentItems = Object.values(item.children);
                                }
                                this.currentTitle = item.name;
                            }
                        }
                    };
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const allCategoriesBtn = document.getElementById('allCategoriesMenuToggleBtn');
                const mainMobileNavBtn = document.getElementById('mainMobileNavToggleBtn');

                if (allCategoriesBtn) {
                    allCategoriesBtn.addEventListener('click', function(event) {
                        event.preventDefault();
                        window.dispatchEvent(new CustomEvent('open-all-categories-menu'));
                    });
                }
                if (mainMobileNavBtn) {
                    mainMobileNavBtn.addEventListener('click', function(event) {
                        event.preventDefault();
                        window.dispatchEvent(new CustomEvent('open-main-mobile-nav'));
                    });
                }
            });

             // --- ALPINE.JS COMPONENT DEFINITIONS ---
            document.addEventListener('alpine:init', () => {
                // 1. Wishlist Button Component
                Alpine.data('wishlistButton', (config) => ({
                    productId: config.productId,
                    isInWishlist: config.initialIsInWishlist || false,
                    isLoading: false,
                    buttonTitle: '',

                    init() {
                        this.updateTitle();                        
                    },
                    updateTitle() {
                        this.buttonTitle = this.isInWishlist ? 'Remove from wishlist' : 'Add to wishlist';
                    },

                    // Toggle wishlist state
                    toggleWishlist() {
                        if (this.isLoading) return;
                        this.isLoading = true;

                        const endpoint = this.isInWishlist
                            ? `{{ url('/wishlist/remove') }}/${this.productId}`
                            : `{{ url('/wishlist/add') }}/${this.productId}`;

                        fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'Content-Type': 'application/json' // Usually not needed for POST with ID in URL only
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(errData => { throw errData; });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success !== undefined) {
                                this.isInWishlist = data.is_in_wishlist;
                                this.updateTitle();
                                window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: data.wishlist_count } }));
                                window.dispatchEvent(new CustomEvent('toast-show', { // Dispatch toast
                                    detail: { type: data.success ? 'success' : (data.message.includes('already') ? 'info' : 'error'), message: data.message }
                                }));
                            } else {
                                window.dispatchEvent(new CustomEvent('toast-show', {
                                    detail: { type: 'error', message: 'Unexpected response from server.' }
                                }));
                            }
                        })
                        .catch(errorDataOrNetworkError => {
                            console.error('Wishlist toggle AJAX error:', errorDataOrNetworkError);
                            let msg = 'Could not update wishlist. Please try again.';
                            if(errorDataOrNetworkError && errorDataOrNetworkError.message) {
                                msg = errorDataOrNetworkError.message;
                            }
                            window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'error', message: msg } }));
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                    }
                })); // End of wishlistButton

                // 2. Toast Handler Component
                Alpine.data('toastHandler', () => ({
                    toasts: [],
                    toastIdCounter: 0,
                    defaultDuration: 3000, // Increased duration

                    init() {
                        // Listen for toast-show events
                        window.addEventListener('toast-show', (event) => {
                            this.showToast(event.detail);
                        });
                    },

                    showToast(detail) {
                        const id = this.toastIdCounter++;
                        const type = detail.type || 'info';
                        const message = detail.message || 'Notification';
                        const title = detail.title || null;
                        const duration = detail.duration || this.defaultDuration;

                        const newToast = { id, message, title, type, visible: true, hovered: false, duration, timeoutId: null };
                        this.toasts.push(newToast);
                        this.setTimeout(newToast);
                    },
                    setTimeout(toast) {
                        if (toast.timeoutId) clearTimeout(toast.timeoutId);
                        toast.timeoutId = setTimeout(() => {
                            if (!toast.hovered) { this.removeToast(toast.id); }
                        }, toast.duration);
                    },
                    restartTimeout(toast) {
                        if (toast.visible && !toast.hovered) { this.setTimeout(toast); }
                    },
                    removeToast(id) {
                        const toast = this.toasts.find(t => t.id === id);
                        if (toast) {
                            toast.visible = false;
                            setTimeout(() => {
                                this.toasts = this.toasts.filter(t => t.id !== id);
                            }, 300); // Match leave transition
                        }
                    },
                    capitalizeFirst(string) {
                        return string.charAt(0).toUpperCase() + string.slice(1);
                    }
                })); // End of toastHandler

                // 3. Product Details Component (UPDATED WITH VARIANT LOGIC)
                Alpine.data('productDetails', (productData) => ({
                    product: productData,
                    selectedImage: 0,
                    selectedVariants: {},
                    quantity: 1,
                    isLoading: false,
                    isModalOpen: false,

                    init() {
                        // Set default variant selections if variants exist
                        if (this.product.variants && Object.keys(this.product.variants).length > 0) {
                            for (const attribute in this.product.variants) {
                                if (this.product.variants[attribute].length > 0) {
                                    this.selectedVariants[attribute] = this.product.variants[attribute][0];
                                }
                            }
                        }
                    },

                    // Check if product has variants
                    get hasVariants() {
                        return this.product.variants && Object.keys(this.product.variants).length > 0;
                    },

                    // Check if all required variants are selected
                    get allVariantsSelected() {
                        if (!this.hasVariants) return true;
                        return Object.keys(this.product.variants).every(attr => this.selectedVariants[attr]);
                    },

                    // Get current price based on selected variants
                    get currentPrice() {
                        if (!this.hasVariants || !this.allVariantsSelected) {
                            return this.product.price;
                        }
                        
                        // Find the variant price based on selected options
                        for (const [attribute, value] of Object.entries(this.selectedVariants)) {
                            if (this.product.variant_stock && this.product.variant_stock[attribute] && this.product.variant_stock[attribute][value]) {
                                return this.product.variant_stock[attribute][value].price;
                            }
                        }
                        
                        return this.product.price;
                    },

                    // Get current stock based on selected variants
                    get currentStock() {
                        if (!this.hasVariants || !this.allVariantsSelected) {
                            return this.product.stock_count;
                        }
                        
                        // Find the variant stock based on selected options
                        for (const [attribute, value] of Object.entries(this.selectedVariants)) {
                            if (this.product.variant_stock && this.product.variant_stock[attribute] && this.product.variant_stock[attribute][value]) {
                                return this.product.variant_stock[attribute][value].stock;
                            }
                        }
                        
                        return this.product.stock_count;
                    },

                    // Get max quantity available
                    get maxQuantity() {
                        return Math.max(1, this.currentStock);
                    },

                    // Open modal
                    openModal() {
                        this.isModalOpen = true;
                        document.body.style.overflow = 'hidden';
                    },

                    // Close modal
                    closeModal() {
                        this.isModalOpen = false;
                        document.body.style.overflow = '';
                    },

                    // Select variant
                    selectVariant(attribute, value) {
                        this.selectedVariants[attribute] = value;
                        // Reset quantity if it exceeds new stock limit
                        if (this.quantity > this.currentStock) {
                            this.quantity = Math.min(this.quantity, this.currentStock);
                        }
                    },

                    // Handle add to cart button click
                    handleAddToCart() {
                        if (!this.hasVariants) {
                            // No variants, add directly to cart
                            this.addToCart();
                        } else if (!this.allVariantsSelected) {
                            // Has variants but not all selected, show alert
                            window.dispatchEvent(new CustomEvent('toast-show', {
                                detail: { type: 'warning', message: 'Please select all product options first.' }
                            }));
                        } else {
                            // Has variants and all selected, open modal
                            this.openModal();
                        }
                    },
                    
                    addToCart(fromModal = false) {
                        if (!this.product.in_stock || this.isLoading) return;
                        
                        // Check stock availability
                        if (this.quantity > this.currentStock) {
                            window.dispatchEvent(new CustomEvent('toast-show', {
                                detail: { type: 'error', message: 'Insufficient stock available.' }
                            }));
                            return;
                        }
                        
                        this.isLoading = true;
                        
                        // Prepare variant data for the cart
                        let variantData = {};
                        if (this.hasVariants && this.allVariantsSelected) {
                            // Find the specific variant ID
                            for (const [attribute, value] of Object.entries(this.selectedVariants)) {
                                if (this.product.variant_stock && this.product.variant_stock[attribute] && this.product.variant_stock[attribute][value]) {
                                    variantData.variant_id = this.product.variant_stock[attribute][value].variant_id;
                                    break;
                                }
                            }
                            variantData.attributes = this.selectedVariants;
                        }
                        
                        const payload = {
                            product_id: this.product.id,
                            quantity: this.quantity,
                            variant_data: variantData,
                        };
                        
                        fetch('{{ route("cart.add") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.ok ? res.json() : res.json().then(err => Promise.reject(err)))
                        .then(data => {
                            if (data.success) {
                                // Close modal if opened from modal
                                if (fromModal) {
                                    this.closeModal();
                                }
                                
                                // Update header and show toast
                                window.dispatchEvent(new CustomEvent('toast-show', { 
                                    detail: { type: 'success', message: data.message } 
                                }));
                                if (data.cart_count !== undefined) {
                                    window.dispatchEvent(new CustomEvent('cart-updated', { 
                                        detail: { count: data.cart_count } 
                                    }));
                                }
                            } else {
                                window.dispatchEvent(new CustomEvent('toast-show', { 
                                    detail: { type: 'error', message: data.message } 
                                }));
                            }
                        })
                        .catch(err => { 
                            console.error('Cart Error:', err);
                            window.dispatchEvent(new CustomEvent('toast-show', { 
                                detail: { type: 'error', message: err.message || 'Failed to add to cart. Please try again.' } 
                            }));
                        })
                        .finally(() => { 
                            this.isLoading = false; 
                        });
                    },
                })); // End of productDetails               

            
                // Update the cart-updated event listener to work with session-based cart
                window.addEventListener('cart-updated', (event) => {
                    // Update cart count in header if needed
                    const cartCountElement = document.querySelector('[data-cart-count]');
                    if (cartCountElement && event.detail.cart_distinct_items_count !== undefined) {
                        cartCountElement.textContent = event.detail.cart_distinct_items_count;
                    }
                });

            }); // End of alpine:init
        </script>
        
    </body>
</html>
