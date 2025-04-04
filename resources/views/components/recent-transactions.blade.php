<!-- resources/views/components/recent-transactions.blade.php -->
@props(['transactions' => null])

<x-card title="Recent Transactions" {{ $attributes }}>
    <x-slot name="icon">
        <x-icon-repeat class="h-5 w-5" />
    </x-slot>
    
    <x-slot name="actions">
        <button class="text-sm text-primary hover:text-primary/80">View All</button>
    </x-slot>
    
    <div x-data="{ transactions: [
        { name: 'Netflix Subscription', amount: -12.99, currency: '$', date: '2023-04-12', icon: 'video' },
        { name: 'Salary Deposit', amount: 4750.00, currency: '$', date: '2023-04-10', icon: 'dollar-sign' },
        { name: 'Amazon Purchase', amount: -59.35, currency: '$', date: '2023-04-08', icon: 'shopping-bag' },
        { name: 'Grocery Store', amount: -87.44, currency: '$', date: '2023-04-05', icon: 'shopping-cart' }
    ] }" class="space-y-4">
        {{ $slot }}
        
        <template x-if="!$slots.default">
            <div class="space-y-4">
                <template x-for="(transaction, index) in transactions" :key="index">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-full bg-muted flex items-center justify-center text-muted-foreground">
                                <svg x-show="transaction.icon === 'video'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                </svg>
                                <svg x-show="transaction.icon === 'dollar-sign'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                                <svg x-show="transaction.icon === 'shopping-bag'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                                <svg x-show="transaction.icon === 'shopping-cart'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium" x-text="transaction.name"></h4>
                                <p class="text-sm text-muted-foreground" x-text="new Date(transaction.date).toLocaleDateString()"></p>
                            </div>
                        </div>
                        <div class="font-medium" :class="transaction.amount < 0 ? 'text-red-500' : 'text-green-500'" x-text="transaction.amount < 0 ? '-' + transaction.currency + Math.abs(transaction.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '+' + transaction.currency + transaction.amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</x-card>