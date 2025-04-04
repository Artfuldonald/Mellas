<div x-data="{
    timeFrame: 'last_30_days',
    customerSegmentationData: [
        { segment: 'High Value', count: 1200 },
        { segment: 'Medium Value', count: 5300 },
        { segment: 'Low Value', count: 8500 },
        { segment: 'At Risk', count: 1700 },
        { segment: 'Lost', count: 800 }
    ],
    retentionRateData: [
        { month: 'Jan', rate: 95 },
        { month: 'Feb', rate: 93 },
        { month: 'Mar', rate: 94 },
        { month: 'Apr', rate: 95 },
        { month: 'May', rate: 97 },
        { month: 'Jun', rate: 98 }
    ],
    channelPerformanceData: [
        { channel: 'Direct', acquisitions: 1200, revenue: 50000 },
        { channel: 'Organic Search', acquisitions: 2500, revenue: 75000 },
        { channel: 'Paid Search', acquisitions: 1800, revenue: 60000 },
        { channel: 'Social Media', acquisitions: 1500, revenue: 45000 },
        { channel: 'Email', acquisitions: 900, revenue: 30000 }
    ]
}" x-init="
    (() => {
        // Customer Segmentation Chart
        const segmentationCtx = document.getElementById('customer-segmentation-chart').getContext('2d');
        new Chart(segmentationCtx, {
            type: 'bar',
            data: {
                labels: customerSegmentationData.map(item => item.segment),
                datasets: [{
                    label: 'Customer Count',
                    data: customerSegmentationData.map(item => item.count),
                    backgroundColor: document.documentElement.classList.contains('dark') ? '#adfa1d' : '#0ea5e9',
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Retention Rate Chart
        const retentionCtx = document.getElementById('retention-rate-chart').getContext('2d');
        new Chart(retentionCtx, {
            type: 'line',
            data: {
                labels: retentionRateData.map(item => item.month),
                datasets: [{
                    label: 'Retention Rate',
                    data: retentionRateData.map(item => item.rate),
                    borderColor: document.documentElement.classList.contains('dark') ? '#adfa1d' : '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 80,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
        
        // Channel Performance Chart
        const channelCtx = document.getElementById('channel-performance-chart').getContext('2d');
        new Chart(channelCtx, {
            type: 'bar',
            data: {
                labels: channelPerformanceData.map(item => item.channel),
                datasets: [
                    {
                        label: 'Acquisitions',
                        data: channelPerformanceData.map(item => item.acquisitions),
                        backgroundColor: document.documentElement.classList.contains('dark') ? '#adfa1d' : '#0ea5e9',
                        borderWidth: 0,
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue',
                        data: channelPerformanceData.map(item => item.revenue),
                        backgroundColor: document.documentElement.classList.contains('dark') ? '#1e40af' : '#3b82f6',
                        borderWidth: 0,
                        borderRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Acquisitions'
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Revenue'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    })()
">
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-2xl font-semibold">Detailed Analytics</h3>
            <x-select x-model="timeFrame" class="w-[180px]">
                <option value="last_7_days">Last 7 Days</option>
                <option value="last_30_days">Last 30 Days</option>
                <option value="last_90_days">Last 90 Days</option>
                <option value="last_12_months">Last 12 Months</option>
            </x-select>
        </div>
        
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
            <x-card class="col-span-4">
                <x-slot name="title">Customer Segmentation</x-slot>
                <div class="h-[300px]">
                    <canvas id="customer-segmentation-chart"></canvas>
                </div>
            </x-card>
            
            <x-card class="col-span-3">
                <x-slot name="title">Customer Retention Rate</x-slot>
                <div class="h-[300px]">
                    <canvas id="retention-rate-chart"></canvas>
                </div>
            </x-card>
        </div>
        
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
            <x-card class="col-span-4">
                <x-slot name="title">Channel Performance</x-slot>
                <div class="h-[300px]">
                    <canvas id="channel-performance-chart"></canvas>
                </div>
            </x-card>
            
            <x-card class="col-span-3">
                <x-slot name="title">Key Metrics</x-slot>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Customer Lifetime Value</p>
                        <p class="text-2xl font-bold">$1,250</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Net Promoter Score</p>
                        <p class="text-2xl font-bold">72</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Customer Acquisition Cost</p>
                        <p class="text-2xl font-bold">$75</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Average Order Value</p>
                        <p class="text-2xl font-bold">$120</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>

