{{-- resources/views/layouts/app.blade.php --}}
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
        @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Ensure Alpine.js is part of your app.js build --}}

        @stack('styles')
        <style>
            .custom-scrollbar-mobile::-webkit-scrollbar { width: 5px; }
            .custom-scrollbar-mobile::-webkit-scrollbar-track { background: #f9fafb; /* gray-50 */ border-radius: 10px; }
            .custom-scrollbar-mobile::-webkit-scrollbar-thumb { background: #d1d5db; /* gray-300 */ border-radius: 10px; }
            .custom-scrollbar-mobile::-webkit-scrollbar-thumb:hover { background: #9ca3af; /* gray-500 */ }
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-800 bg-gray-50">

        {{-- Data attribute for JS to know current route (used by sidebar active states if any) --}}
        <div class="min-h-screen flex flex-col" data-current-route="{{ Route::currentRouteName() ?? '' }}">

            {{-- Header Component --}}
            <x-header />

            <!-- Page Content -->
            <main class="flex-grow">
                {{-- The $slot will contain the specific page's layout (e.g., 3 columns for homepage) --}}
                {{ $slot }}
            </main>

            {{-- Footer Component --}}
            <x-footer />

            {{-- ***** "ALL CATEGORIES" OFF-CANVAS MENU (Amazon Style) ***** --}}
            <div id="allCategoriesOffcanvasMenu"
                 class="fixed inset-0 z-50 flex" {{-- Removed initial transform, Alpine handles it --}}
                 x-data="amazonCategorySidebar({
                    allCategories: {{ Js::from(
                        // Map your $navCategories (from View Composer) to the structure expected by amazonCategorySidebar
                        // This structure should be an array of L1 category objects.
                        // Each L1 object should have an 'id', 'name', 'slug', 'isLinkOnly' (boolean),
                        // and a 'children' array for L2.
                        // Each L2 object should have 'id', 'name', 'slug', 'isLinkOnly',
                        // and a 'children' array for L3.
                        // L3 objects just need 'id', 'name', 'slug', and 'isLinkOnly' (true).
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
                                            'isLinkOnly' => true // L3 are always direct links
                                        ])->values()->all() // Ensure L3 children is an array
                                    ];
                                })->values()->all() // Ensure L2 children is an array
                            ];
                        })->values()->all() // Ensure top level is an array
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


                // --- The COMPLETE and CORRECTED productDetails component ---
                Alpine.data('productDetails', (config) => ({
                    // --- DATA from Blade (Your existing properties) ---
                    basePrice: config.basePrice,
                    baseQuantity: config.baseQuantity,
                    hasVariants: config.hasVariants,
                    options: config.optionsData,
                    variants: config.variantsData,

                    // --- STATE (Your existing properties + new ones for cart actions) ---
                    currentPrice: 0,
                    stockMessage: '',
                    isInStock: false,
                    isLowStock: false,
                    selectedOptions: {},
                    currentVariant: null,
                    
                    // NEW properties for handling cart actions
                    productId: config.productId, // We need the product ID
                    quantity: 1, // Quantity for simple products
                    isLoading: false, // To show spinner on the button
                    cartActionMessage: '',
                    cartActionMessageType: '',

                    // --- INITIALIZATION ---
                    init() {
                        this.currentPrice = this.basePrice;
                        if (this.hasVariants) {
                            Object.keys(this.options).forEach(attrId => {
                                this.selectedOptions[parseInt(attrId)] = null;
                            });
                        }
                        this.updateDisplay();
                    },

                    // --- LOGIC METHODS (Your existing methods) ---
                    selectOption(attributeId, valueId) {
                        const numericAttrId = parseInt(attributeId);
                        if (this.selectedOptions[numericAttrId] === valueId) {
                            this.selectedOptions[numericAttrId] = null;
                        } else {
                            this.selectedOptions[numericAttrId] = valueId;
                        }
                        this.updateDisplay();
                    },

                    updateDisplay() {
                        if (!this.hasVariants) {
                            this.isInStock = this.baseQuantity > 0;
                            this.isLowStock = this.isInStock && this.baseQuantity <= 10;
                            this.stockMessage = this.isLowStock ? `${this.baseQuantity} items left` : (this.isInStock ? 'In stock' : 'Out of stock');
                            this.currentPrice = this.basePrice;
                            return;
                        }
                        
                        const allOptionsSelected = Object.values(this.selectedOptions).every(v => v !== null);
                        if (allOptionsSelected) {
                            const key = Object.values(this.selectedOptions).sort((a, b) => a - b).join('-');
                            this.currentVariant = this.variants[key] || null;
                            if (this.currentVariant) {
                                this.currentPrice = this.currentVariant.price;
                                this.isInStock = this.currentVariant.quantity > 0;
                                this.isLowStock = this.isInStock && this.currentVariant.quantity <= 10;
                                this.stockMessage = this.isLowStock ? `${this.currentVariant.quantity} items left` : (this.isInStock ? 'In stock' : 'Out of stock');
                            } else {
                                this.currentPrice = this.basePrice;
                                this.isInStock = false; this.isLowStock = false;
                                this.stockMessage = 'This combination is not available';
                            }
                        } else {
                            this.currentPrice = this.basePrice;
                            this.currentVariant = null;
                            this.isInStock = Object.values(this.variants).some(v => v.quantity > 0);
                            this.isLowStock = false;
                            this.stockMessage = this.isInStock ? 'Select options' : 'Out of stock';
                        }
                    },

                    isSelected(attributeId, valueId) {
                        return this.selectedOptions[attributeId] === valueId;
                    },

                    isOptionAvailable(valueId) {
                        for (const key in this.variants) {
                            const variant = this.variants[key];
                            if (variant.quantity > 0 && variant.attributeValueIds.includes(valueId)) {
                                return true;
                            }
                        }
                        return false;
                    },

                    // --- NEW METHODS for Quantity and Cart Actions ---
                    isAnythingPurchasable() {
                        if (!this.hasVariants) return this.baseQuantity > 0;
                        return Object.values(this.variants).some(v => v.quantity > 0);
                    },

                    incrementQuantity() { if (this.quantity < this.baseQuantity) { this.quantity++; } },
                    decrementQuantity() { if (this.quantity > 1) { this.quantity--; } },

                    handleAddToCartAttempt() {
                        if (this.isLoading) return;
                        this.cartActionMessage = '';

                        if (!this.hasVariants) { // Simple Product
                            if (this.baseQuantity > 0 && this.quantity <= this.baseQuantity) {
                                this.performAddToCart(this.productId, null, this.quantity);
                            } else { this.showError(`Only ${this.baseQuantity} items available.`); }
                        } else { // Variant Product
                            if (Object.values(this.selectedOptions).every(v => v !== null)) {
                                if (this.currentVariant && this.currentVariant.quantity > 0) {
                                    this.performAddToCart(this.productId, this.currentVariant.id, 1);
                                } else { this.showError('This combination is not available.'); }
                            } else {
                                this.showError('Please select a variation to add to cart.');
                            }
                        }
                    },

                    performAddToCart(productId, variantId, quantity) {
                        this.isLoading = true;
                        let payload = { product_id: productId, quantity: quantity };
                        if (variantId) { payload.variant_id = variantId; }
                        
                        fetch('{{ route("cart.add") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.ok ? res.json() : res.json().then(err => Promise.reject(err)))
                        .then(data => {
                            if (data.success) {
                                window.dispatchEvent(new CustomEvent('toast-show', { detail: { type: 'success', message: data.message } }));
                                if (data.cart_distinct_items_count !== undefined) {
                                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart_distinct_items_count: data.cart_distinct_items_count } }));
                                }
                            } else {
                                this.showError(data.message);
                            }
                        }).catch(err => {
                            this.showError(err.message || 'An error occurred.');
                        }).finally(() => {
                            this.isLoading = false;
                        });
                    },

                    showError(msg) {
                        this.cartActionMessage = msg;
                        this.cartActionMessageType = 'error';
                        setTimeout(() => { this.cartActionMessage = ''; }, 4000);
                    }
                }));                 

            //cart toggle button 
             Alpine.data('cartToggleButton', (config) => ({
                productId: config.productId,
                isInCart: config.initialIsInCart,
                isLoading: false,

                toggleCart() {
                    if (this.isLoading) return;
                    this.isLoading = true;

                    const endpoint = this.isInCart ? '{{ route("cart.remove-item") }}' : '{{ route("cart.add") }}';
                    const payload = { product_id: this.productId, quantity: 1 };

                    fetch(endpoint, {
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
                            // 1. Update its own state
                            this.isInCart = !this.isInCart;
                            
                            // 2. Dispatch a toast notification
                            window.dispatchEvent(new CustomEvent('toast-show', { 
                                detail: { type: 'success', message: data.message } 
                            }));

                            // 3. Dispatch the 'cart-updated' event for the header to hear
                            if (data.cart_distinct_items_count !== undefined) {
                                window.dispatchEvent(new CustomEvent('cart-updated', { 
                                    detail: { cart_distinct_items_count: data.cart_distinct_items_count } 
                                }));
                            }
                        } else {
                            window.dispatchEvent(new CustomEvent('toast-show', { 
                                detail: { type: 'error', message: data.message } 
                            }));
                        }
                    })
                    .catch(err => {
                        window.dispatchEvent(new CustomEvent('toast-show', { 
                            detail: { type: 'error', message: err.message || 'An error occurred.' } 
                        }));
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
                }           
                }));

            }); // End of alpine:init
        </script>
        
    </body>
</html>