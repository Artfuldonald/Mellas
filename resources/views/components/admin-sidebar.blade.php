<!-- resources/views/components/admin-sidebar.blade.php -->
<aside
    x-data="{ open: true }"
    class="bg-card text-card-foreground border-r border-border w-64 flex-shrink-0 h-screen overflow-y-auto transition-all duration-300 ease-in-out"
    :class="{ 'w-64': open, 'w-20': !open }"
>
    <div class="p-4 flex items-center justify-between">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                <path d="M2 17l10 5 10-5"></path>
                <path d="M2 12l10 5 10-5"></path>
            </svg>
            <span x-show="open" class="text-xl font-bold transition-opacity duration-300">Admin</span>
        </a>
        <button @click="open = !open" class="text-muted-foreground hover:text-foreground">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6" x-show="open"></path>
                <path d="M9 18l6-6-6-6" x-show="!open"></path>
            </svg>
        </button>
    </div>

    <nav class="mt-5 px-2">
        <div class="space-y-1">
            @php
                $activeSection = $activeSection ?? '';
            @endphp
            
            <x-admin-nav-item route="admin.dashboard" icon="home" :active="$activeSection === '' || $activeSection === 'dashboard'">
                Dashboard
            </x-admin-nav-item>
            
            <x-admin-nav-item route="admin.products" icon="credit-card" :active="$activeSection === 'products'">
                Products
            </x-admin-nav-item>
            
            <x-admin-nav-item route="admin.transactions" icon="repeat" :active="$activeSection === 'transactions'">
                Transactions
            </x-admin-nav-item>
            
            <x-admin-nav-item route="admin.bills" icon="file-text" :active="$activeSection === 'bills'">
                Bills & Payments
            </x-admin-nav-item>
            
            <x-admin-nav-item route="admin.reports" icon="pie-chart" :active="$activeSection === 'reports'">
                Reports
            </x-admin-nav-item>
            
            <x-admin-nav-item route="admin.users" icon="users" :active="$activeSection === 'users'">
                Users
            </x-admin-nav-item>
            
            <x-admin-nav-item route="admin.settings" icon="settings" :active="$activeSection === 'settings'">
                Settings
            </x-admin-nav-item>            
           
        </div>
    </nav>
</aside>