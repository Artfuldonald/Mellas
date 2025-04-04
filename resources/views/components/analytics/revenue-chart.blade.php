<div x-data="{
    chartData: [
        { month: 'Jan', revenue: 2000 },
        { month: 'Feb', revenue: 2200 },
        { month: 'Mar', revenue: 2700 },
        { month: 'Apr', revenue: 2400 },
        { month: 'May', revenue: 2800 },
        { month: 'Jun', revenue: 3200 },
        { month: 'Jul', revenue: 3100 },
        { month: 'Aug', revenue: 3400 },
        { month: 'Sep', revenue: 3700 },
        { month: 'Oct', revenue: 3500 },
        { month: 'Nov', revenue: 3800 },
        { month: 'Dec', revenue: 4200 }
    ]
}" x-init="
    (() => {
        const ctx = document.getElementById('revenue-chart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(item => item.month),
                datasets: [{
                    label: 'Revenue',
                    data: chartData.map(item => item.revenue),
                    borderColor: document.documentElement.classList.contains('dark') ? '#adfa1d' : '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: $' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    })()
">
    <div class="h-[350px]">
        <canvas id="revenue-chart"></canvas>
    </div>
</div>

