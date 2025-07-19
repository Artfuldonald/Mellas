<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // It's good practice to add the auth middleware here too
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $orders = auth()->user()->orders()
            ->with(['items']) 
            ->orderBy('created_at', 'desc')
            ->paginate(10);
      
        return view('profile.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {        
        if ($order->user_id !== auth()->id()) {
            abort(403, 'This is not your order.');
        }
        
        $order->load(['items.product.images', 'items.variant', 'payments']);
       
        return view('profile.orders.show', compact('order'));
    }
}