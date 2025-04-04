<header class="sticky top-0 z-40 border-b bg-background">
    <div class="container flex h-16 items-center justify-between px-4 md:px-6">
        <div class="hidden md:block">
            <nav class="flex items-center space-x-2">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium">Home</a>
                
                @php
                    $segments = request()->segments();
                    $path = '';
                @endphp
                
                @foreach($segments as $segment)
                    <span class="text-muted-foreground">/</span>
                    @php $path .= '/'.$segment; @endphp
                    <a href="{{ $path }}" class="text-sm font-medium">
                        {{ ucfirst($segment) }}
                    </a>
                @endforeach
            </nav>
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Notifications -->
            <div x-data="{ isOpen: false }">
                <button
                    @click="isOpen = !isOpen"
                    class="relative p-2 rounded-md hover:bg-accent"
                    aria-label="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                </button>
                
                <div x-show="isOpen" 
                     @click.away="isOpen = false"
                     x-transition
                     class="absolute right-0 mt-2 w-96 z-50 bg-card rounded-md shadow-lg border border-border">
                    <div class="p-4 border-b border-border flex items-center justify-between">
                        <h3 class="text-sm font-medium">Notifications</h3>
                        <button @click="isOpen = false" class="p-1 rounded-md hover:bg-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="max-h-[400px] overflow-y-auto p-4 space-y-4">
                        @foreach([
                            ['title' => 'New Feature', 'message' => 'Check out our new budget tracking tool!', 'date' => '2 hours ago', 'icon' => 'info', 'color' => 'text-blue-500'],
                            ['title' => 'Account Alert', 'message' => 'Unusual activity detected on your account.', 'date' => '1 day ago', 'icon' => 'alert-triangle', 'color' => 'text-yellow-500'],
                            ['title' => 'Payment Due', 'message' => 'Your credit card payment is due in 3 days.', 'date' => '3 days ago', 'icon' => 'credit-card', 'color' => 'text-red-500'],
                            ['title' => 'Investment Update', 'message' => 'Your investment portfolio has grown by 5% this month.', 'date' => '5 days ago', 'icon' => 'trending-up', 'color' => 'text-green-500'],
                        ] as $notification)
                            <div class="p-4 bg-card rounded-md border border-border shadow-sm">
                                <div class="flex items-start space-x-4">
                                    <div class="{{ $notification['color'] }} p-2 rounded-full bg-opacity-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $notification['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="@include('icons.' . $notification['icon'])" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <p class="text-sm font-medium leading-none">{{ $notification['title'] }}</p>
                                        <p class="text-sm text-muted-foreground">{{ $notification['message'] }}</p>
                                        <p class="text-xs text-muted-foreground">{{ $notification['date'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Theme Toggle -->
            <button 
                @click="toggleDarkMode()"
                class="p-2 rounded-md hover:bg-accent"
                aria-label="Toggle theme">
                <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>
            
            <!-- User Menu -->
            <div x-data="{ isOpen: false }">
                <button 
                    @click="isOpen = !isOpen"
                    class="relative h-8 w-8 rounded-full bg-accent"
                    aria-label="User menu">
                    <img 
                        src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/38184074.jpg-M4vCjTSSWVw5RwWvvmrxXBcNVU8MBU.jpeg" 
                        alt="User avatar" 
                        class="h-8 w-8 rounded-full object-cover"
                    />
                </button>
                
                <div x-show="isOpen" 
                     @click.away="isOpen = false"
                     x-transition
                     class="absolute right-0 mt-2 w-56 z-50 bg-card rounded-md shadow-lg border border-border">
                    <div class="p-2 border-b border-border">
                        <div class="flex flex-col space-y-1 p-2">
                            <p class="text-sm font-medium leading-none">Dollar Singh</p>
                            <p class="text-xs leading-none text-muted-foreground">dollar.singh@example.com</p>
                        </div>
                    </div>
                    <div class="p-2">
                        <a href="{{ route('settings') }}" class="flex items-center rounded-md px-2 py-2 text-sm hover:bg-accent">
                            Profile
                        </a>
                        <a href="{{ route('settings') }}" class="flex items-center rounded-md px-2 py-2 text-sm hover:bg-accent">
                            Settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center rounded-md px-2 py-2 text-sm hover:bg-accent">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

