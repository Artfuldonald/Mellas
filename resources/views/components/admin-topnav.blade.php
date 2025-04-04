<!-- resources/views/components/admin-topnav.blade.php -->
<header class="bg-card text-card-foreground border-b border-border sticky top-0 z-10">
    <div class="px-4 sm:px-6 lg:px-8 flex h-16 items-center justify-between">
        <div class="flex items-center">
            <button type="button" class="text-muted-foreground md:hidden" @click="$store.sidebar.toggle()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            
            <div class="ml-4 md:ml-0">
                {{ $search ?? '' }}
            </div>
        </div>
        
        <div class="flex items-center space-x-4">
            <!-- Dark mode toggle -->
            <button @click="toggleDarkMode()" class="p-2 rounded-md text-muted-foreground hover:text-foreground">
                <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
            
            <!-- Notifications -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="p-2 rounded-md text-muted-foreground hover:text-foreground relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-primary"></span>
                </button>
                
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-card border border-border rounded-md shadow-lg py-1 z-10">
                    <div class="px-4 py-2 border-b border-border">
                        <h3 class="text-sm font-medium">Notifications</h3>
                    </div>
                    
                    <div class="max-h-64 overflow-y-auto">
                        {{ $notifications ?? '<div class="px-4 py-2 text-sm text-muted-foreground">No new notifications</div>' }}
                    </div>
                    
                    <div class="px-4 py-2 border-t border-border text-xs">
                        <a href="#" class="text-primary hover:text-primary/80">View all notifications</a>
                    </div>
                </div>
            </div>
            
            <!-- User dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center space-x-2">
                    <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center text-muted-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <span class="text-sm font-medium hidden md:block">Admin User</span>
                </button>
                
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-card border border-border rounded-md shadow-lg py-1 z-10">
                    {{ $userMenu ?? '
                    <a href="#" class="block px-4 py-2 text-sm text-foreground hover:bg-muted">Your Profile</a>
                    <a href="#" class="block px-4 py-2 text-sm text-foreground hover:bg-muted">Settings</a>
                    <div class="border-t border-border"></div>
                    <form method="POST" action="#">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-foreground hover:bg-muted">Sign out</button>
                    </form>
                    ' }}
                </div>
            </div>
        </div>
    </div>
</header>