{{-- resources/views/partials/admin-topnav.blade.php (or wherever your top nav lives) --}}
<header class="bg-gray-800 border-b border-gray-700 sticky top-0 z-30"> {{-- Dark bg, subtle border --}}
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between">

            {{-- Left Side: Mobile Menu Button (if needed) & Logo --}}
            <div class="flex items-center">
                {{-- Mobile menu button (Ensure this ID matches JS in sidebar) --}}
                <button type="button" id="mobileSidebarToggleBtn"
                        class="md:hidden -ml-2 inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                        aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    {{-- Icon when menu is closed. --}}
                    <x-heroicon-o-bars-3 class="block h-6 w-6" />
                    {{-- Icon when menu is open. --}}
                    {{-- <x-heroicon-o-x-mark class="hidden h-6 w-6" /> --}}
                </button>

                {{-- Logo/Brand (Optional - can be in sidebar only) --}}
                <div class="flex-shrink-0 items-center hidden md:flex ml-4"> {{-- Hide on mobile if logo is in sidebar --}}
                     <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-white">
                         {{-- Replace with your actual logo if you have one --}}
                         Mella's Connect Admin
                     </a>
                </div>
            </div>

            {{-- Center: Search Bar --}}
            <div class="flex-1 px-4 flex justify-center lg:justify-start"> {{-- Allow search to take space --}}
                <div class="w-full max-w-xs lg:max-w-md">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative text-gray-400 focus-within:text-gray-300">
                        <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5" aria-hidden="true" />
                        </div>
                        <input id="search" name="search"
                               class="block w-full bg-gray-700/50 border border-transparent rounded-md py-2 pl-10 pr-3 leading-5 text-gray-300 placeholder-gray-500 focus:outline-none focus:bg-gray-700 focus:border-pink-500 focus:ring-pink-500 focus:text-gray-100 sm:text-sm transition duration-150 ease-in-out"
                               placeholder="Search..." type="search">
                    </div>
                </div>
            </div>

            {{-- Right Side: Icons & Profile --}}
            <div class="flex items-center space-x-4">

                {{-- Notification Bell --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="relative rounded-full bg-gray-800 p-1 text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                        <span class="sr-only">View notifications</span>
                        <x-heroicon-o-bell class="h-6 w-6" aria-hidden="true" />
                        {{-- Notification Count Badge --}}
                        @php $notificationCount = Auth::user()->unreadNotifications()->count(); @endphp {{-- Example fetch --}}
                        @if($notificationCount > 0)
                        <span class="absolute -top-1 -right-1 block h-3 w-3 rounded-full bg-red-500 ring-1 ring-gray-800 text-xs"></span>
                        @endif
                    </button>

                    {{-- Notification Dropdown Panel --}}
                    <div x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute z-10 mt-2 w-80 {{-- Keep fixed width --}}
                                left-0 right-auto sm:left-auto sm:right-0 {{-- Left on mobile, Right on sm+ --}}
                                origin-top-left sm:origin-top-right {{-- Adjust origin based on position --}}
                                rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                        role="menu" aria-orientation="vertical" tabindex="-1"
                        style="display: none;"
                        >
                         {{-- Header --}}
                         <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                             <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Notifications</span>
                             {{-- <button class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Mark all read</button> --}}
                         </div>
                         {{-- List --}}
                        <div class="max-h-80 overflow-y-auto">
                            {{-- Loop through notifications here --}}
                            @forelse(Auth::user()->unreadNotifications->take(5) as $notification)
                                <a href="{{ $notification->data['link'] ?? '#' }}"
                                   class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700/50 last:border-b-0"
                                   role="menuitem" tabindex="-1">
                                    <div class="flex items-start space-x-3">
                                        @isset($notification->data['icon'])
                                            <x-dynamic-component :component="$notification->data['icon']" class="h-5 w-5 mt-0.5 {{ ($notification->data['level'] ?? '') === 'error' ? 'text-red-500' : 'text-blue-500' }} flex-shrink-0"/>
                                        @endisset
                                        <div class="flex-1">
                                            <p class="font-medium">{{ $notification->data['message'] ?? 'Notification' }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                 <p class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">No unread notifications.</p>
                            @endforelse
                        </div>
                         {{-- Footer Link --}}
                         {{-- <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                             <a href="#" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline block text-center">View all</a>
                         </div> --}}
                    </div>
                </div>

                {{-- Profile dropdown --}}
                <div class="relative ml-3" x-data="{ open: false }">
                    <div>
                        <button @click="open = !open" type="button" class="relative flex max-w-xs items-center rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                            <span class="sr-only">Open user menu</span>
                            {{-- Placeholder Avatar --}}
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-600">
                              <span class="text-sm font-medium leading-none text-white">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                            </span>
                            {{-- Or use an image:
                            <img class="h-8 w-8 rounded-full" src="{{ Auth::user()->profile_photo_url ?? 'DEFAULT_AVATAR_URL' }}" alt="">
                            --}}
                        </button>
                    </div>

                    {{-- Dropdown Panel --}}
                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                         role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1"
                         style="display: none;" {{-- Hide initially --}}
                         >
                        {{-- Active: "bg-gray-100", Not Active: "" --}}
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-0">
                            <x-heroicon-o-user-circle class="mr-2 h-5 w-5 text-gray-400"/>
                            Your Profile
                        </a>
                        {{-- Logout Form --}}
                        <form method="POST" action="{{ route('logout') }}" role="none">
                            @csrf
                            <button type="submit" class="flex w-full items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-2">
                                <x-heroicon-o-arrow-left-on-rectangle class="mr-2 h-5 w-5 text-gray-400"/>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div> {{-- End Profile Dropdown --}}

            </div> {{-- End Right Side --}}

        </div> {{-- End Main Flex Container --}}
    </div> {{-- End Container --}}
</header>