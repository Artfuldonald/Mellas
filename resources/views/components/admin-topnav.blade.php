<!-- resources/views/components/admin-topnav.blade.php -->
{{-- Simplified header classes - Mobile pink, Desktop white --}}
<header class="bg-pink-600 text-white md:bg-gray-100 md:text-gray-700 sticky top-0 z-30">
    <div class="px-4 sm:px-6 lg:px-8 flex h-16 items-center justify-between">
        <div class="flex items-center">
            {{-- Mobile Toggle Button - Text white on mobile --}}
            <button id="mobileSidebarToggleBtn" type="button" class="text-white md:hidden mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="white">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="sr-only">Toggle sidebar</span>
            </button>
            <div class="ml-4 md:ml-0">
                {{ $search ?? '' }}
            </div>
        </div>

        <div class="flex items-center space-x-4">
            
            {{-- Notifications Dropdown - Adjust colors --}}
            <div x-data="{ open: false }" class="relative">
                 <button @click="open = !open"
                         class="p-2 rounded-md relative
                                text-pink-100 hover:bg-white/10 hover:text-white {{-- Mobile --}}
                                md:text-gray-500 md:hover:text-gray-700 md:hover:bg-gray-100" {{-- Desktop --}}
                 >
                     {{-- Bell icon --}}
                     <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>

                     <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"></span>
                 </button>
                 {{-- Dropdown panel - Simplified, no dark: classes --}}
                 <div x-show="open" @click.away="open = false"
                      class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-md shadow-lg py-1 z-40">
                     <div class="px-4 py-2 border-b border-gray-200"> <h3 class="text-sm font-medium text-gray-900">Notifications</h3> </div>
                     <div class="max-h-64 overflow-y-auto"> {{ $notifications ?? '<div class="px-4 py-2 text-sm text-gray-500">No new notifications</div>' }} </div>
                     <div class="px-4 py-2 border-t border-gray-200 text-xs"> <a href="#" class="text-primary-600 hover:underline">View all</a> </div>
                 </div>
            </div>

            {{-- User Dropdown - Adjust colors --}}
            <div x-data="{ open: false }" class="relative">
                 <button @click="open = !open" class="flex items-center space-x-2 md:text-gray-700">
                     {{-- User avatar placeholder - Adjust colors --}}
                     <div class="h-8 w-8 rounded-full bg-white/20 md:bg-gray-200 flex items-center justify-center text-white md:text-gray-500">
                         <svg class="h-5 w-5" ...>...</svg>
                     </div>
                     <span class="text-sm font-medium hidden md:block">Admin User</span>
                 </button>
                  {{-- Dropdown panel - Simplified, no dark: classes --}}
                 <div x-show="open" @click.away="open = false"
                      class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg py-1 z-40">
                     {{ $userMenu ?? '
                     <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                     <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                     <div class="border-t border-gray-200 my-1"></div>
                     <form method="POST" action="#"> @csrf <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</button> </form>
                     ' }}
                 </div>
            </div>
        </div>
    </div>
</header>