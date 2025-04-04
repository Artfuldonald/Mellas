<!-- resources/views/components/business-metrics.blade.php -->
@props(['data' => null])

<x-card title="Business Metrics" {{ $attributes }}>
    <x-slot name="icon">
        <x-icon-bar-chart-2 class="h-5 w-5" />
    </x-slot>
    
    <x-slot name="actions">
        <div class="flex items-center space-x-2">
            <select class="text-sm border border-border rounded-md bg-background px-2 py-1">
                <option>This Month</option>
                <option>Last Month</option>
                <option>Last 3 Months</option>
                <option>This Year</option>
            </select>
        </div>
    </x-slot>
    
    <div x-data="{
        metrics: [
            { name: 'Total Revenue', value: 48250, currency: '$', change: 12.5, positive: true, icon: 'dollar-sign' },
            { name: 'New Customers', value: 642, change: 8.2, positive: true, icon: 'users' },
            { name: 'Active Accounts', value: 1423, change: 3.1, positive: true, icon: 'credit-card' },
            { name: 'Conversion Rate', value: 3.2, unit: '%', change: 0.8, positive: false, icon: 'percent' }
        ]
    }" class="space-y-6">
        {{ $slot }}
        
        <template x-if="!$slots.default">
            <div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <template x-for="(metric, index) in metrics" :key="index">
                        <div class="p-4 rounded-md border border-border">
                            <div class="flex items-center justify-between">
                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                    <svg x-show="metric.icon === 'dollar-sign'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                    <svg x-show="metric.icon === 'users'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                    <svg x-show="metric.icon === 'credit-card'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                        <line x1="1" y1="10" x2="23" y2="10"></line>
                                    </svg>
                                    <svg x-show="metric.icon === 'percent'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="19" y1="5" x2="5" y2="19"></line>
                                        <circle cx="6.5" cy="6.5" r="2.5"></circle>
                                        <circle cx="17.5" cy="17.5" r="2.5"></circle>
                                    </svg>
                                </div>
                                <div class="flex items-center text-sm" :class="metric.positive ? 'text-green-500' : 'text-red-500'">
                                    <svg x-show="metric.positive" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="18 15 12 9 6 15"></polyline>
                                    </svg>
                                    <svg x-show="!metric.positive" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                    <span x-text="metric.change + '%'"></span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h4 class="text-sm font-medium text-muted-foreground" x-text="metric.name"></h4>
                                <div class="text-2xl font-bold mt-1" x-text="(metric.currency || '') + metric.value.toLocaleString() + (metric.unit || '')"></div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="mt-6">
                    <div class="border border-border rounded-md p-4">
                        <h4 class="text-sm font-medium mb-4">Revenue Trend</h4>
                        <div class="h-64">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('revenueChart').getContext('2d');
                        
                        const chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                datasets: [{
                                    label: 'Revenue',
                                    data: [30500, 32800, 28600, 33400, 35200, 38500, 42300, 44800, 46200, 48250, 0, 0],
                                    borderColor: 'rgb(99, 102, 241)',
                                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                    tension: 0.3,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            display: true,
                                            color: 'rgba(0, 0, 0, 0.05)'
                                        },
                                        ticks: {
                                            callback: function(value) {
                                                return '$' + value.toLocaleString();
                                            }
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            </div>
        </template>
    </div>
</x-card>