{{-- resources/views/admin/dashboard.blade.php --}}
<x-admin-layout title="Dashboard">

    {{-- Custom Styles for Dashboard Elements --}}
    @push('styles')
    <style>
        /* Chart Placeholder Fallback */
        .chart-placeholder { min-height: 300px; background-color: #fdf2f8; /* pink-50 */ border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #9ca3af; /* gray-400 */ }
        /* Top Product Image Styling */
        .top-product-img { width: 40px; height: 40px; object-fit: cover; border-radius: 0.375rem; /* rounded-md */ background-color: #f3f4f6; /* gray-100 */ border: 1px solid #e5e7eb; /* gray-200 */ }
        /* Star Rating Color */
         .star-rating svg { color: #fbbf24; } /* text-yellow-400 */
    </style>
    @endpush

    {{-- Main Content Area with Light Pink Background --}}
    <div class="bg-pink-50 text-gray-800 min-h-screen"> {{-- Light pink background, dark text default --}}
        <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Header --}}
            <div class="md:flex md:items-center md:justify-between md:space-x-4 mb-8">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Dashboard</h1>
                </div>
                {{-- Optional: Add date range filters here later --}}
            </div>

            {{-- Session Messages --}}
            @include('admin.partials._session_messages')

            {{-- Top Row Stat Cards --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <x-analytics.stat-card
                    title="Total Revenue"
                    value="${{ number_format($totalRevenueAllTime ?? 0, 2) }}"
                    icon="currency-dollar"
                    iconBgColor="bg-pink-500"
                    class="bg-white text-gray-900" {{-- White card --}}
                />
                <x-analytics.stat-card
                    title="Total Orders"
                    value="{{ number_format($totalOrdersCount ?? 0) }}"
                    icon="shopping-cart"
                    iconBgColor="bg-pink-500"
                    ctaLink="{{ route('admin.orders.index') }}"
                    ctaText="View Orders"
                    class="bg-white text-gray-900"
                />
                <x-analytics.stat-card
                    title="Total Customers"
                    value="{{ number_format($totalCustomersCount ?? 0) }}"
                    icon="users"
                    iconBgColor="bg-pink-500"
                    ctaLink="{{ route('admin.customers.index') }}"
                    ctaText="View Customers"
                    class="bg-white text-gray-900"
                />
                 <x-analytics.stat-card
                    title="New Customers (30d)"
                    value="{{ number_format($newCustomersCount30d ?? 0) }}"
                    icon="user-plus"
                    iconBgColor="bg-pink-500"
                    class="bg-white text-gray-900"
                />
            </div>

            {{-- Second Row: Revenue Chart --}}
            <div class="grid grid-cols-1 gap-6">
                 <x-analytics.chart-card
                    title="Revenue Overview"
                    chartId="revenueChartCanvas" {{-- ID for JS target --}}
                    class="lg:col-span-1 bg-white text-gray-900" {{-- White card --}}
                    >
                    {{-- Slot for Weekly/Monthly Toggle --}}
                    <x-slot name="extraInfoSlot">
                        <div class="flex space-x-1 rounded-md bg-pink-100 p-0.5" x-data="{ timeframe: 'monthly' }">
                            <button @click="timeframe = 'monthly'; window.updateRevenueChart(revenueMonthlyData);"
                                    :class="timeframe === 'monthly' ? 'bg-white text-pink-700 shadow' : 'text-pink-600 hover:bg-pink-50 hover:text-pink-700'"
                                    class="px-3 py-1 text-xs font-medium rounded-md transition">Monthly</button>
                            <button @click="timeframe = 'weekly'; window.updateRevenueChart(revenueWeeklyData);"
                                    :class="timeframe === 'weekly' ? 'bg-white text-pink-700 shadow' : 'text-pink-600 hover:bg-pink-50 hover:text-pink-700'"
                                    class="px-3 py-1 text-xs font-medium rounded-md transition">Weekly</button>
                        </div>
                    </x-slot>

                    {{-- Default Slot for Chart Canvas --}}
                    <div class="relative h-72"> {{-- Container to constrain canvas height --}}
                        <canvas id="revenueChartCanvas" class="w-full h-full"></canvas>
                    </div>
                 </x-analytics.chart-card>
            </div>

            {{-- Third Row: Recent Orders & Top Products/Transactions --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Recent Orders Table --}}
                <div class="bg-white shadow rounded-lg lg:col-span-2">
                     <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                         <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                            <div class="ml-4 mt-2">
                                 <h3 class="text-lg leading-6 font-semibold text-gray-900">Recent Orders</h3>
                            </div>
                            <div class="ml-4 mt-2 flex-shrink-0">
                                <a href="{{ route('admin.orders.index') }}" class="text-pink-600 hover:text-pink-800 text-sm flex items-center">
                                    View all → <x-heroicon-o-chevron-right class="w-4 h-4 ml-1"/>
                                </a>
                            </div>
                         </div>
                     </div>
                     <div class="overflow-x-auto">
                        @if($recentOrders->isEmpty())
                            <p class="px-6 py-4 text-sm text-gray-500">No recent orders found.</p>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.orders.show', $order) }}" class="text-pink-600 hover:text-pink-800">{{ $order->order_number }}</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->user->name ?? 'Guest' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at->diffForHumans() }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @php
                                                    // Define status colors for light theme
                                                    $statusClasses = [
                                                         App\Models\Order::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
                                                         App\Models\Order::STATUS_PROCESSING => 'bg-blue-100 text-blue-800',
                                                         App\Models\Order::STATUS_SHIPPED => 'bg-cyan-100 text-cyan-800',
                                                         App\Models\Order::STATUS_DELIVERED => 'bg-green-100 text-green-800',
                                                         App\Models\Order::STATUS_CANCELLED => 'bg-red-100 text-red-800',
                                                         App\Models\Order::STATUS_REFUNDED => 'bg-purple-100 text-purple-800',
                                                     ];
                                                     $currentStatusClass = $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $currentStatusClass }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($order->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                     </div>
                 </div> {{-- End Recent Orders --}}

                 {{-- Right Column: Top Products & Recent Transactions --}}
                 <div class="space-y-6 lg:col-span-1">

                     {{-- Top Selling Products --}}
                     <div class="bg-white shadow rounded-lg">
                         <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                             <h3 class="text-lg leading-6 font-semibold text-gray-900">Top Selling Products</h3>
                         </div>
                         <div class="divide-y divide-gray-200">
                             @forelse($topProducts as $product)
                                <div class="p-4 flex items-center space-x-3">
                                    {{-- Product Image --}}
                                    @php
                                        // Safely get the first image URL or a placeholder
                                        $imageUrl = $product->images->first()?->path
                                                    ? Storage::url($product->images->first()->path)
                                                    : asset('images/placeholder-product.png'); // Ensure you have a placeholder image
                                    @endphp
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $product->name }}"
                                         class="top-product-img flex-shrink-0">
                                    {{-- Product Name & Quantity --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-700 truncate">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $product->total_quantity_sold }} sold</p>
                                    </div>
                                </div>
                             @empty
                                 <p class="p-4 text-sm text-gray-500">No sales data available yet.</p>
                             @endforelse
                         </div>
                          <div class="bg-gray-50 px-4 py-3 sm:px-6 rounded-b-lg">
                            <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-pink-600 hover:text-pink-800">View all products →</a>
                        </div>
                     </div> {{-- End Top Products --}}

                     {{-- Recent Successful Transactions --}}
                     <div class="bg-white shadow rounded-lg">
                         <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                             <h3 class="text-lg leading-6 font-semibold text-gray-900">Recent Sales</h3>
                         </div>
                         <div class="divide-y divide-gray-200">
                             @forelse($recentSuccessfulTransactions as $amount)
                                <div class="p-3 flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Sale Completed</span>
                                    <span class="text-sm font-medium text-green-600">+${{ number_format($amount, 2) }}</span>
                                </div>
                             @empty
                                 <p class="p-4 text-sm text-gray-500">No recent successful transactions.</p>
                             @endforelse
                         </div>
                     </div> {{-- End Recent Transactions --}}

                 </div> {{-- End Right Column --}}

            </div> {{-- End Third Row Grid --}}
            <script id="revenueMonthlyDataJson" type="application/json">@json($revenueMonthlyChartData ?? ['labels' => [], 'values' => []])</script>
            <script id="revenueWeeklyDataJson" type="application/json">@json($revenueWeeklyChartData ?? ['labels' => [], 'values' => []])</script>
        </div> {{-- End Container --}}
    </div> {{-- End Background --}}

    @push('scripts')   
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Chart Colors & Defaults ---
            const primaryPink = '#ec4899';
            const gridColor = '#e5e7eb';
            const grayText = '#6b7280';
            const tooltipBackgroundColor = 'rgba(31, 41, 55, 0.9)';
            const tooltipTitleColor = '#f9fafb';
            const tooltipBodyColor = '#d1d5db';

            let revenueChartInstance = null;

            // --- Get Data from Hidden Elements ---
            let revenueMonthlyData = { labels: [], values: [] };
            let revenueWeeklyData = { labels: [], values: [] };

            try {
                const monthlyJsonElement = document.getElementById('revenueMonthlyDataJson');
                if (monthlyJsonElement) {
                    revenueMonthlyData = JSON.parse(monthlyJsonElement.textContent);
                } else { console.warn("Element 'revenueMonthlyDataJson' not found."); }
            } catch (e) { console.error("Failed to parse monthly revenue JSON:", e); }

            try {
                const weeklyJsonElement = document.getElementById('revenueWeeklyDataJson');
                if (weeklyJsonElement) {
                    revenueWeeklyData = JSON.parse(weeklyJsonElement.textContent);
                } else { console.warn("Element 'revenueWeeklyDataJson' not found."); }
            } catch (e) { console.error("Failed to parse weekly revenue JSON:", e); }
            // --- End Get Data ---
            
            // --- Function to Create/Update Revenue Chart ---
            function createOrUpdateRevenueChart(chartData) {
                const revenueCtx = document.getElementById('revenueChartCanvas');
                // Check if Chart object exists (it should if imported in app.js)
                if (!revenueCtx || typeof Chart === 'undefined') {
                    console.warn("Chart.js library or canvas element 'revenueChartCanvas' not found.");
                    const placeholder = document.querySelector('#revenueChartCanvas')?.parentElement;
                    if(placeholder && !placeholder.querySelector('.chart-error-message')) {
                        placeholder.innerHTML = `<p class="text-center text-gray-500 chart-error-message p-4">Could not load chart.</p>`;
                    }
                    return;
                }
                if (!chartData || !Array.isArray(chartData.labels) || !Array.isArray(chartData.values)) {
                     console.error("Invalid chart data structure provided:", chartData);
                     return;
                 }
                 
                const chartConfig = {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Revenue', data: chartData.values,
                            backgroundColor: primaryPink, borderColor: primaryPink,
                            borderWidth: 1, borderRadius: 4, borderSkipped: false, barThickness: 10
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: tooltipBackgroundColor, titleColor: tooltipTitleColor, bodyColor: tooltipBodyColor, padding: 10, cornerRadius: 4, displayColors: false,
                                callbacks: { label: (ctx) => ctx.parsed.y !== null ? '$' + ctx.parsed.y.toFixed(2) : '' }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: {
                                    color: grayText,
                                    callback: (val) => '$' + new Intl.NumberFormat('en-US', { notation: "compact", maximumFractionDigits: 1 }).format(val)
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    color: grayText,
                                    autoSkip: true,
                                    maxTicksLimit: 10 // Adjust based on weekly/monthly view
                                }
                            }
                        }
                    }
                };

                if (revenueChartInstance) {
                    revenueChartInstance.config.options.scales.x.ticks.maxTicksLimit = chartData.labels.length > 15 ? 10 : 12; // Adjust ticks based on data length
                    revenueChartInstance.data.labels = chartData.labels;
                    revenueChartInstance.data.datasets[0].data = chartData.values;
                    revenueChartInstance.update();
                } else {
                    revenueChartInstance = new Chart(revenueCtx, chartConfig);
                }
            }

            // --- Make function globally accessible for Alpine ---
            // Ensure Alpine is initialized AFTER this script runs or use defer if needed
            if (typeof Alpine !== 'undefined') {
                 window.updateRevenueChart = createOrUpdateRevenueChart;
            } else {
                // Fallback if Alpine loads later (less ideal)
                 document.addEventListener('alpine:init', () => {
                     window.updateRevenueChart = createOrUpdateRevenueChart;
                 });
            }


            // --- Initial Chart Load ---
            createOrUpdateRevenueChart(revenueMonthlyData); // Load monthly data by default

        }); // End DOMContentLoaded
    </script>
    @endpush

</x-admin-layout>