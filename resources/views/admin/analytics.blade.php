<x-admin-layout>
    <div class="flex-1 space-y-4 p-8 pt-6">
        <div class="flex items-center justify-between space-y-2">
            <h2 class="text-3xl font-bold tracking-tight">Analytics</h2>
            <div class="flex items-center space-x-2">              
                <x-button variant="primary">
                    <x-icon name="download" class="h-4 w-4 mr-2"></x-icon>
                    Export Data
                </x-button>
            </div>
        </div>
        
        <div x-data="{ activeTab: 'overview' }">
            <div class="border-b border-border">
                <nav class="flex space-x-4" aria-label="Tabs">
                    <button 
                        @click="activeTab = 'overview'" 
                        :class="activeTab === 'overview' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                        class="px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap">
                        Overview
                    </button>
                    <button 
                        @click="activeTab = 'analytics'" 
                        :class="activeTab === 'analytics' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                        class="px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap">
                        Analytics
                    </button>
                    <button 
                        @click="activeTab = 'reports'" 
                        :class="activeTab === 'reports' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                        class="px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap">
                        Reports
                    </button>
                    <button 
                        @click="activeTab = 'notifications'" 
                        :class="activeTab === 'notifications' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                        class="px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap">
                        Notifications
                    </button>
                </nav>
            </div>
            
            <div class="mt-4">
                <div x-show="activeTab === 'overview'" class="space-y-4">
                    <x-analytics.overview-tab></x-analytics.overview-tab>
                </div>
                <div x-show="activeTab === 'analytics'" class="space-y-4">
                    <x-analytics.analytics-tab></x-analytics.analytics-tab>
                </div>
                <div x-show="activeTab === 'reports'" class="space-y-4">
                    <x-analytics.reports-tab></x-analytics.reports-tab>
                </div>
                <div x-show="activeTab === 'notifications'" class="space-y-4">
                    <x-analytics.notifications-tab></x-analytics.notifications-tab>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

