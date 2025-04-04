<div x-data="{
    comparisonPeriod: 'previous_month',
    cards: [
        {
            title: 'Total Revenue',
            icon: 'dollar-sign',
            amount: '$45,231.89',
            description: '+20.1% from last month',
            trend: 'up'
        },
        {
            title: 'New Customers',
            icon: 'users',
            amount: '2,350',
            description: '+180.1% from last month',
            trend: 'up'
        },
        {
            title: 'Active Accounts',
            icon: 'credit-card',
            amount: '12,234',
            description: '+19% from last month',
            trend: 'up'
        },
        {
            title: 'Growth Rate',
            icon: 'trending-up',
            amount: '18.6%',
            description: '+5.4% from last month',
            trend: 'up'
        }
    ]
}">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-2xl font-semibold">Dashboard Overview</h3>
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium">Compare to:</span>
            <x-select x-model="comparisonPeriod">
                <option value="previous_month">Previous Month</option>
                <option value="previous_quarter">Previous Quarter</option>
                <option value="previous_year">Previous Year</option>
            </x-select>
        </div>
    </div>
    
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <template x-for="card in cards" :key="card.title">
            <x-card>
                <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                    <h3 class="text-sm font-medium" x-text="card.title"></h3>
                    <x-icon :name="'dollar-sign'" class="h-4 w-4 text-muted-foreground"></x-icon>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold" x-text="card.amount"></div>
                    <p class="text-xs text-muted-foreground" x-text="card.description"></p>
                    <div class="mt-2 flex items-center text-xs" :class="card.trend === 'up' ? 'text-green-500' : 'text-red-500'">
                        <x-icon name="arrow-up-right" class="mr-1 h-3 w-3" :class="card.trend !== 'up' && 'transform rotate-180'"></x-icon>
                        <span x-text="card.description.split(' ')[0]"></span>
                    </div>
                </div>
            </x-card>
        </template>
    </div>
    
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7 mt-4">
        <div class="bg-card rounded-lg border border-border shadow-sm col-span-4">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-xl font-semibold">Revenue</h3>
            </div>
            <div class="p-6 pt-0 pl-2">
                <x-analytics.revenue-chart></x-analytics.revenue-chart>
            </div>
        </div>
        <div class="bg-card rounded-lg border border-border shadow-sm col-span-3">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-xl font-semibold">Recent Transactions</h3>
            </div>
            <div class="p-6 pt-0">
                <x-analytics.recent-transactions></x-analytics.recent-transactions>
            </div>
        </div>
    </div>
</div>

