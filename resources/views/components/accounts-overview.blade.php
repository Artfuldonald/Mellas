<!-- resources/views/components/accounts-overview.blade.php -->
@props(['accounts' => null])

<x-card title="Accounts Overview" {{ $attributes }}>
    <x-slot name="icon">
        <x-icon-credit-card class="h-5 w-5" />
    </x-slot>
    
    <x-slot name="actions">
        <button class="text-sm text-primary hover:text-primary/80">View All</button>
    </x-slot>
    
    <div x-data="{ accounts: [
        { name: 'Main Account', balance: 24563.55, currency: '$', type: 'Checking', change: 2.5, positive: true },
        { name: 'Savings', balance: 12250.00, currency: '$', type: 'Savings', change: 1.2, positive: true },
        { name: 'Investment', balance: 7342.25, currency: '$', type: 'Investment', change: 0.8, positive: false }
    ] }" class="space-y-4">
        {{ $slot }}
        
        <template x-if="!$slots.default">
            <div class="space-y-4">
                <template x-for="(account, index) in accounts" :key="index">
                    <div class="flex items-center justify-between p-4 rounded-md border border-border">
                        <div>
                            <h4 class="font-medium" x-text="account.name"></h4>
                            <p class="text-sm text-muted-foreground" x-text="account.type"></p>
                        </div>
                        <div class="text-right">
                            <div class="font-medium" x-text="account.currency + account.balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></div>
                            <div class="flex items-center text-sm" :class="account.positive ? 'text-green-500' : 'text-red-500'">
                                <svg x-show="account.positive" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="18 15 12 9 6 15"></polyline>
                                </svg>
                                <svg x-show="!account.positive" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                                <span x-text="account.change + '%'"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
        
        <div class="mt-4">
            <button class="w-full py-2 px-4 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors">
                Add New Account
            </button>
        </div>
    </div>
</x-card>