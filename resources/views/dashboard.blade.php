<x-admin-layout>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
        
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <x-accounts-overview />
            </div>
            <div class="lg:col-span-1">
                <x-recent-transactions />
            </div>
        </div>

        <x-business-metrics />
    </div>
</x-admin-layout>
