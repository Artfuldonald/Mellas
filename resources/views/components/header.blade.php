{{-- resources/views/components/header.blade.php --}}
<header class="bg-white shadow-sm sticky top-0 z-40">
    <div class="container mx-auto px-2 sm:px-4">
        <div class="flex h-16 items-center justify-between">

            {{-- Left Side: "All" Hamburger Menu & Logo --}}
            <div class="flex items-center">
                {{-- "All" Hamburger Menu for Categories --}}
                <button type="button" id="allCategoriesMenuToggleBtn"
                        class="mr-1 p-2 rounded-md text-gray-700 hover:bg-pink-50 hover:text-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <span class="sr-only">Open All Categories</span>
                    <x-heroicon-o-bars-3 class="h-6 w-6"/>
                </button>

                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex-shrink-0 ml-1 md:ml-2">
                    <span class="text-xl md:text-2xl font-bold text-pink-600">Mella's Connect</span>
                </a>
            </div>

            <!-- Center: Search Bar -->
            <div class="flex-1 px-2 sm:px-4 lg:px-8 flex justify-center">
                <form action="{{ route('products.index') }}" method="GET" class="w-full max-w-lg xl:max-w-xl flex">
                    <label for="header-main-search" class="sr-only">Search</label>
                    <div class="relative w-full">
                        <input type="search" id="header-main-search" name="search_query"
                               class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-l-lg border-l-gray-50 border-l-2 border border-gray-300 focus:ring-pink-500 focus:border-pink-500 placeholder-gray-400"
                               placeholder="Search products..."
                               value="{{ request('search_query', '') }}">
                        <button type="submit"
                                class="absolute top-0 right-0 p-2.5 text-sm font-medium h-full text-white bg-pink-600 rounded-r-lg border border-pink-600 hover:bg-pink-700 focus:ring-4 focus:outline-none focus:ring-pink-300">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                            <span class="sr-only">Search</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Side: Account, Cart -->
            <div class="flex items-center space-x-3 md:space-x-4">
                {{-- Account Dropdown --}}
                <div class="relative hidden sm:block" x-data="{ open: false }">
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

                {{-- Wishlist --}}
                @auth
                    <a href="{{ route('wishlist.index') }}"
                    class="flex items-center text-sm font-medium text-gray-700 hover:text-pink-600 p-1 sm:p-0"
                    x-data="{ count: {{ $wishlistCountGlobal ?? 0 }} }" {{-- Initialize with View Composer value --}}
                    @wishlist-updated.window="count = $event.detail.count" {{-- Listen for global event --}}
                    >
                        <x-heroicon-o-heart class="w-6 h-6"/>
                        <span class="hidden md:inline ml-1">Wishlist</span>
                        <template x-if="count > 0">
                            <span x-text="count" class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-pink-100 bg-pink-600 rounded-full"></span>
                        </template>
                    </a>
                @endauth
                
                {{-- Cart --}}
                <a href="{{ route('cart.index') }}" class="flex items-center text-sm font-medium text-gray-700 hover:text-pink-600 p-1 sm:p-0">
                    <x-heroicon-o-shopping-cart class="w-6 h-6"/>
                    <span class="hidden md:inline ml-1">Cart</span>
                    @if(isset($cartCountGlobal) && $cartCountGlobal > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-pink-100 bg-pink-600 rounded-full">{{ $cartCountGlobal }}</span>
                    @endif
                </a>

                {{-- Mobile Menu Toggle for MAIN navigation (Account, Help, etc. on mobile) --}}
                <button type="button" id="mainMobileNavToggleBtn"
                        class="lg:hidden p-2 rounded-md text-gray-500 hover:bg-pink-50 hover:text-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <span class="sr-only">Open main menu</span>
                    <x-heroicon-o-ellipsis-vertical class="h-6 w-6" />
                </button>
            </div>
        </div>
    </div>
</header>