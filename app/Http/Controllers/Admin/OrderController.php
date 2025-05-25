<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with('user') // Eager load user for display
                      ->latest(); // Default sort by newest

        // --- Filtering ---
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function ($uq) use ($searchTerm) {
                      $uq->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('email', 'like', "%{$searchTerm}%");
                  });
            });
        }
        // --- End Filtering ---

        $orders = $query->paginate(20)->withQueryString();
        $statuses = Order::getStatuses(); // Get statuses for filter dropdown
        $paymentStatuses = Order::getPaymentStatuses(); // Get payment statuses for filter

        return view('admin.orders.index', compact('orders', 'statuses', 'paymentStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Eager load necessary details for the show view
        $order->load(['user', 'items.product', 'items.variant']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $statuses = Order::getStatuses(); // Get available statuses
        return view('admin.orders.edit', compact('order', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $availableStatuses = Order::getStatuses();

        $validated = $request->validate([
            'status' => ['required', Rule::in($availableStatuses)],
            'tracking_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string', // Allow updating admin notes
        ]);

        try {
            // Update timestamps based on status change (example)
            if ($validated['status'] === Order::STATUS_SHIPPED && !$order->shipped_at) {
                $validated['shipped_at'] = now();
            } elseif ($validated['status'] === Order::STATUS_DELIVERED && !$order->delivered_at) {
                 $validated['delivered_at'] = now();
            } elseif ($validated['status'] === Order::STATUS_CANCELLED && !$order->cancelled_at) {
                 $validated['cancelled_at'] = now();
            }
            // Add more logic for other statuses if needed

            $order->update($validated);

            Log::info("Order #{$order->order_number} (ID: {$order->id}) updated.", $validated);

            // TODO: Optionally send email notification to customer about status change

            return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated successfully.');

        } catch (\Exception $e) {
            Log::error("Error updating order #{$order->order_number} (ID: {$order->id}): " . $e->getMessage());
            return back()->with('error', 'Failed to update order. Please check logs.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        
    }
}