<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // --- Top Row Stats ---
        $totalOrdersCount = Order::count();
        $totalCustomersCount = User::isCustomer()->count();
        $totalRevenueAllTime = Order::where('payment_status', Order::PAYMENT_PAID)->sum('total_amount');
        $newCustomersCount30d = User::isCustomer()
                                ->where('created_at', '>=', now()->subDays(30))
                                ->count();

        // --- Revenue Chart Data ---
        // Monthly (Last 6 Months)
        $revenueMonthly = Order::select(
                DB::raw("strftime('%Y-%m', created_at) as month"), // Group by YYYY-MM
                DB::raw('SUM(total_amount) as total')
            )
            ->where('payment_status', Order::PAYMENT_PAID)
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth()) // Go back 6 full months
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $revenueMonthlyChartData = [
            'labels' => $revenueMonthly->pluck('month')->map(fn($m) => Carbon::parse($m.'-01')->format('M Y'))->all(),
            'values' => $revenueMonthly->pluck('total')->all(),
        ];

        // Weekly (Last 8 Weeks)
         $revenueWeekly = Order::select(
                DB::raw("strftime('%Y-%W', created_at) as week"), // Group by Year-WeekNumber
                DB::raw('SUM(total_amount) as total')
            )
            ->where('payment_status', Order::PAYMENT_PAID)
            ->where('created_at', '>=', now()->subWeeks(8)->startOfWeek()) // Go back 8 full weeks
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();

        // Format labels better for weekly
        $weeklyLabels = [];
        foreach ($revenueWeekly->pluck('week') as $yearWeek) {
            [$year, $week] = explode('-', $yearWeek);
            $date = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $weeklyLabels[] = 'Wk '.$week.' ('.$date->format('M d').')'; // e.g., Wk 35 (Aug 26)
        }

        $revenueWeeklyChartData = [
            'labels' => $weeklyLabels,
            'values' => $revenueWeekly->pluck('total')->all(),
        ];
       
        // --- Recent Orders (Transactions) Table ---
        $recentOrders = Order::select([
            'id', 
            'user_id', 
            'order_number', 
            'total_amount', 
            'status', 
            'created_at'
        ])
        ->with('user:id,name') 
        ->latest()
        ->take(8)
        ->get();

        // --- Top 5 Selling Products (with first image) ---
        $topProductIds = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity_sold'))
            ->whereHas('order', fn($q) => $q->where('payment_status', Order::PAYMENT_PAID))
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity_sold')
            ->limit(5)
            ->pluck('product_id'); // Get only the IDs

        // Fetch the product details including the first image for those IDs
        $topProducts = Product::with(['images' => fn($q) => $q->orderBy('position')->limit(1)])
                              ->whereIn('id', $topProductIds)
                              // We need the quantity sold, join back or re-query (simpler here)
                              ->select('products.*', DB::raw('(SELECT SUM(quantity) FROM order_items WHERE order_items.product_id = products.id AND order_items.order_id IN (SELECT id FROM orders WHERE payment_status = "paid")) as total_quantity_sold'))
                              ->orderByDesc('total_quantity_sold') // Order again based on the subquery
                              ->get();


        // --- Recent 5 Successful Transactions (Amounts Only) ---
        $recentSuccessfulTransactions = Order::where('payment_status', Order::PAYMENT_PAID)
                                            ->latest() // Get the most recent paid orders
                                            ->take(5)
                                            ->pluck('total_amount'); // Get only the amounts


        return view('admin.dashboard', compact(
            'totalOrdersCount',
            'totalCustomersCount',
            'totalRevenueAllTime', // Renamed for clarity
            'newCustomersCount30d', // Renamed for clarity
            'revenueMonthlyChartData',
            'revenueWeeklyChartData',
            'recentOrders',
            'topProducts',
            'recentSuccessfulTransactions'
        ));
    }
}