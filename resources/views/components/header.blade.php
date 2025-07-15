{{-- resources/views/components/header.blade.php --}}
<header class="bg-white shadow-sm sticky top-0 z-40">
    <div class="container mx-auto px-2 sm:px-4">
        <!-- Main Header Row -->
        <div class="flex h-16 items-center justify-between">
            {{-- Left Side: Hamburger Menu & Logo --}}
            <div class="flex items-center min-w-0 flex-1 sm:flex-none">
                {{-- "All" Hamburger Menu for Categories --}}
                <button type="button" id="allCategoriesMenuToggleBtn"
                        class="mr-2 p-2 rounded-md text-gray-700 hover:bg-pink-50 hover:text-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500 flex-shrink-0">
                    <span class="sr-only">Open All Categories</span>
                    <x-heroicon-o-bars-3 class="h-5 w-5 sm:h-6 sm:w-6"/>
                </button>

                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}">
                        <div class="bg-pink-500 text-white px-3 py-1 rounded-md font-bold text-lg sm:text-xl">
                            Mella's Connect
                        </div>
                    </a>
                </div>
            </div>

            <!-- Center: Search Bar (Hidden on mobile, shown below header) -->
           <div class="hidden md:flex flex-1 px-4 lg:px-8 justify-center">
            <form action="{{ route('products.index') }}" method="GET" class="w-full max-w-lg xl:max-w-xl">
                <label for="header-main-search" class="sr-only">Search</label>
                
                <div class="relative w-full">                       
                      
                       <input type="search" id="header-main-search" name="search_query" 
                            placeholder="Search products..."
                            value="{{ request('search_query', '') }}"
                            class="block w-full pl-4 pr-12 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent placeholder-gray-400 text-sm">
                       
                        <button type="submit" 
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-pink-600 p-1"
                                aria-label="Submit search">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5"/>
                        </button>

                    </div>
                </form>
            </div>         

            <!-- Right Side: Icons & Mobile Menu -->
            <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                {{-- Account Dropdown (Desktop Only) --}}
                <div class="relative hidden md:block" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-pink-600">
                        <x-heroicon-o-user class="w-5 h-5 mr-1" /> Account
                        <x-heroicon-o-chevron-down class="w-4 h-4 ml-1" />
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-20 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" style="display:none;">
                        @guest
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign In</a>
                            <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Create Account</a>
                        @else
                            <span class="block px-4 py-2 text-sm text-gray-500">Hello, {{ Str::words(Auth::user()->name, 1, '') }}</span>
                            <a href="{{ Auth::user()->is_admin ? route('admin.dashboard') : route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Account</a>
                            <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure?');"> @csrf <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign Out</button></form>
                        @endguest
                    </div>
                </div>

                {{-- Wishlist (Desktop & Mobile) --}}
                @auth
                    <a href="{{ route('wishlist.index') }}"
                    class="flex items-center text-sm font-medium text-gray-700 hover:text-pink-600 p-1"
                    x-data="{ count: {{ $wishlistCountGlobal ?? 0 }} }"
                    @wishlist-updated.window="count = $event.detail.count"
                    >
                        <div class="relative">
                            <x-heroicon-o-heart class="w-5 h-5 sm:w-6 sm:h-6"/>
                            <template x-if="count > 0">
                                <span x-text="count" class="absolute -top-2 -right-2 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-pink-100 bg-pink-600 rounded-full min-w-[18px] h-[18px]"></span>
                            </template>
                        </div>
                        <span class="hidden lg:inline ml-1">Wishlist</span>
                    </a>
                @endauth
                
                {{-- Cart (Desktop & Mobile) --}}
                <a href="{{ route('cart.index') }}"
                   class="flex items-center text-sm font-medium text-gray-700 hover:text-pink-600 p-1"
                    x-data="cartCounter()"
                    x-init="$nextTick(() => refreshCount())"

                   @cart-updated.window="updateCount($event.detail.cart_distinct_items_count)"
                   @page-loaded.window="refreshCount()"
                   @page-restored-from-cache.window="refreshCount()"
                   >
                    <div class="relative">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 sm:w-6 sm:h-6"/>
                        <template x-if="count > 0">
                            <span x-text="count" class="absolute -top-2 -right-2 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-pink-100 bg-pink-600 rounded-full min-w-[18px] h-[18px]"></span>
                        </template>
                    </div>
                    <span class="hidden lg:inline ml-1">Cart</span>
                </a>

                {{-- Mobile Menu Toggle --}}
                <button type="button" id="mainMobileNavToggleBtn"
                        class="md:hidden p-2 rounded-md text-gray-500 hover:bg-pink-50 hover:text-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500"
                        @click="$dispatch('open-main-mobile-nav')">
                    <span class="sr-only">Open main menu</span>
                    <x-heroicon-o-ellipsis-vertical class="h-5 w-5" />
                </button>
            </div>
        </div>

        <!-- Mobile Search Bar (Below main header on mobile) -->
        <div class="md:hidden pb-3 pt-2 border-t border-gray-100">
            <form action="{{ route('products.index') }}" method="GET" class="flex">
                <label for="mobile-search" class="sr-only">Search</label>
                <div class="relative w-full">
                    <input type="search" id="mobile-search" name="search_query"
                           class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-l-lg border border-gray-300 focus:ring-pink-500 focus:border-pink-500 placeholder-gray-400"
                           placeholder="Search products..."
                           value="{{ request('search_query', '') }}">
                    <button type="submit"
                            class="absolute top-0 right-0 p-2.5 text-sm font-medium h-full text-white bg-pink-600 rounded-r-lg border border-pink-600 hover:bg-pink-700">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        <span class="sr-only">Search</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</header>

<script>
// Enhanced Cart Counter Component
function cartCounter() {
    return {
        count: 0, // start from 0 — let refreshCount load the real value

        updateCount(newCount) {
            if (typeof newCount === 'number') {
                this.count = newCount;
            }
        },

        async refreshCount() {
            try {
                const response = await fetch('/api/cart/count', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.count = data.count || 0;
            } catch (e) {
                console.error('Cart count fetch failed:', e);
            }
        }
    }
}

// Dispatch page loaded event when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.dispatchEvent(new CustomEvent('page-loaded'));
});
</script>
