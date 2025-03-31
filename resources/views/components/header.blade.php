 <!-- Header/Navigation -->
 <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="#" class="text-2xl font-bold text-pink-500">Mella's Connect</a>                
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex space-x-8">
               <x-nav-link href="/" :active="request()->is('/')">Home</x-nav-link>
               <x-nav-link href="/product" :active="request()->is('/product')">Products</x-nav-link>
               <x-nav-link href="/category" :active="request()->is('/category')">Categories</x-nav-link>
               <x-nav-link href="/about" :active="request()->is('/about')">About</x-nav-link>
               <x-nav-link href="/contact" :active="request()->is('/contact')">Contact</x-nav-link>                
            </nav>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button type="button" class="text-gray-600 hover:text-pink-500 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Icons -->
            <div class="hidden md:flex items-center space-x-4">

                <x-icon name="fas fa-search"></x-icon>
                <x-icon name="fas fa-user"></x-icon>
                <x-icon name="fas fa-shopping-cart">
                    <span class="absolute -top-2 -right-2 bg-pink-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">3</span>
                </x-icon>

            </div>
        </div>
    </div>
</header>
