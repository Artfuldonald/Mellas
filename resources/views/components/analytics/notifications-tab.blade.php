<div x-data="{
    notifications: {
        account: true,
        security: true,
        performance: false,
        market: false,
        financial: true,
        user: false
    },
    
    toggleNotification(id) {
        this.notifications[id] = !this.notifications[id];
    }
}">
    <div class="space-y-4">
        <x-card>
            <x-slot name="title">Notification Preferences</x-slot>
            <div class="space-y-4">
                <x-notification-toggle id="account" label="Account Activity" icon="bell" :checked="true"></x-notification-toggle>
                <x-notification-toggle id="security" label="Security Alerts" icon="alert-triangle" :checked="true"></x-notification-toggle>
                <x-notification-toggle id="performance" label="Performance Updates" icon="trending-up" :checked="false"></x-notification-toggle>
                <x-notification-toggle id="market" label="Market Trends" icon="trending-down" :checked="false"></x-notification-toggle>
                <x-notification-toggle id="financial" label="Financial Reports" icon="dollar-sign" :checked="true"></x-notification-toggle>
                <x-notification-toggle id="user" label="User Behavior" icon="users" :checked="false"></x-notification-toggle>
            </div>
        </x-card>
        
        <x-card>
            <x-slot name="title">Recent Notifications</x-slot>
            <div class="space-y-4">
                <x-notification-item-simple 
                    icon="alert-triangle" 
                    color="text-yellow-500" 
                    title="Unusual account activity detected" 
                    date="2 hours ago">
                </x-notification-item-simple>
                
                <x-notification-item-simple 
                    icon="trending-up" 
                    color="text-green-500" 
                    title="Your portfolio has grown by 5% this week" 
                    date="1 day ago">
                </x-notification-item-simple>
                
                <x-notification-item-simple 
                    icon="bell" 
                    color="text-blue-500" 
                    title="New feature: Advanced analytics now available" 
                    date="3 days ago">
                </x-notification-item-simple>
                
                <x-notification-item-simple 
                    icon="dollar-sign" 
                    color="text-purple-500" 
                    title="Monthly financial report is ready for review" 
                    date="5 days ago">
                </x-notification-item-simple>
            </div>
        </x-card>
        
        <div class="flex justify-end">
            <x-button variant="outline">View All Notifications</x-button>
        </div>
    </div>
</div>

