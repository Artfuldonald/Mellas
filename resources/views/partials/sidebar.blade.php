<div x-data="{ 
    isCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    isMobileOpen: false,
    toggleCollapsed() {
        this.isCollapsed = !this.isCollapsed;
        localStorage.setItem('sidebarCollapsed', this.isCollapsed);
    }
}" 
class="fixed inset-y-0 z-20 flex flex-col bg-background transition-all duration-300 ease-in-out lg:static"
:class="isCollapsed ? 'w-[72px]' : 'w-72', isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    
    <!-- Mobile Toggle Button -->
    <button 
        class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-background rounded-md shadow-md"
        @click="isMobileOpen = !isMobileOpen"
        aria-label="Toggle sidebar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
    
    <!-- Sidebar Header -->
    <div class="border-b border-border">
        <div class="flex h-16 items-center gap-2 px-4" :class="isCollapsed && 'justify-center px-2'">
            <template x-if="!isCollapsed">
                <a href="{{ route('dashboard') }}" class="flex items-center font-semibold">
                    <span class="text-lg">Flowers&Saints</span>
                </a>
            </template>
            <button
                class="ml-auto h-8 w-8 rounded-md p-0 hover:bg-accent"
                :class="isCollapsed && 'ml-0'"
                @click="toggleCollapsed"
                aria-label="Toggle sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="isCollapsed && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="sr-only" x-text="isCollapsed ? 'Expand' : 'Collapse'">Collapse Sidebar</span>
            </button>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="flex-1 overflow-auto">
        <nav class="flex-1 space-y-1 px-2 py-4">
            @php
            $navigation = [
                ['name' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
                ['name' => 'Analytics', 'route' => 'analytics', 'icon' => 'bar-chart-2'],
                ['name' => 'Organization', 'route' => 'organization', 'icon' => 'building-2'],
                ['name' => 'Projects', 'route' => 'projects', 'icon' => 'folder'],
                ['name' => 'Transactions', 'route' => 'transactions', 'icon' => 'wallet'],
                ['name' => 'Invoices', 'route' => 'invoices', 'icon' => 'receipt'],
                ['name' => 'Payments', 'route' => 'payments', 'icon' => 'credit-card'],
                ['name' => 'Members', 'route' => 'members', 'icon' => 'users-2'],
                ['name' => 'Permissions', 'route' => 'permissions', 'icon' => 'shield'],
                ['name' => 'Chat', 'route' => 'chat', 'icon' => 'messages-square'],
                ['name' => 'Meetings', 'route' => 'meetings', 'icon' => 'video'],
            ];
            @endphp
            
            @foreach ($navigation as $item)
                <a href="{{ route($item['route']) }}" 
                   class="flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors
                          {{ request()->routeIs($item['route']) 
                             ? 'bg-secondary text-secondary-foreground' 
                             : 'text-muted-foreground hover:bg-secondary hover:text-secondary-foreground' }}"
                   :class="isCollapsed && 'justify-center px-2'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="!isCollapsed && 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="@include('icons.' . $item['icon'])" />
                    </svg>
                    <template x-if="!isCollapsed">
                        <span>{{ $item['name'] }}</span>
                    </template>
                </a>
            @endforeach
        </nav>
    </div>
    
    <!-- Bottom Navigation -->
    <div class="border-t border-border p-2">
        <nav class="space-y-1">
            @php
            $bottomNavigation = [
                ['name' => 'Settings', 'route' => 'settings', 'icon' => 'settings'],
                ['name' => 'Help', 'route' => 'help', 'icon' => 'help-circle'],
            ];
            @endphp
            
            @foreach ($bottomNavigation as $item)
                <a href="{{ route($item['route']) }}" 
                   class="flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors
                          {{ request()->routeIs($item['route']) 
                             ? 'bg-secondary text-secondary-foreground' 
                             : 'text-muted-foreground hover:bg-secondary hover:text-secondary-foreground' }}"
                   :class="isCollapsed && 'justify-center px-2'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="!isCollapsed && 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="@include('icons.' . $item['icon'])" />
                    </svg>
                    <template x-if="!isCollapsed">
                        <span>{{ $item['name'] }}</span>
                    </template>
                </a>
            @endforeach
        </nav>
    </div>
</div>

