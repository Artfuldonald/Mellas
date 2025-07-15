<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreAddressRequest;
use App\Services\MtnMomo\CollectionClient;
use App\Http\Requests\UpdateAddressRequest;
use App\Exceptions\InsufficientStockException;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cartService, private CollectionClient $mtnMomoClient ) 
    {
        $this->middleware('auth');
    }

    /**
     * The main checkout summary page.
     */
    public function index()
    {
        $cartState = $this->cartService->getCartState();

        if ($cartState['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        // Intelligent address selection: session -> default -> latest
        $selectedAddress = Auth::user()->addresses()->find(session('checkout.address_id'))
            ?? Auth::user()->addresses()->where('is_default', true)->first()
            ?? Auth::user()->addresses()->latest()->first();

        if (!$selectedAddress) {
            return redirect()->route('checkout.addresses.create')
                ->with('info', 'Please add a shipping address to continue.');
        }

        // ---LOGIC for Payment Method ---    
        $availablePaymentMethods = [
            'mtn_momo' => 'MTN Mobile Money',
            'cash_on_delivery' => 'Cash on Delivery',
            // Add more here like 'vodafone_cash', 'card', etc.
        ];

        // Get the selected payment method from the session, defaulting to the first available one
        $selectedPaymentMethod = session(
            'checkout.payment_method', 
            array_key_first($availablePaymentMethods)
        );

         $isAddressStepComplete = session()->has('checkout.address_id');

         $isPaymentStepComplete = session()->has('checkout.payment_method');        
      
        $isCheckoutReady = $isAddressStepComplete && $isPaymentStepComplete;
      
        $selectedPaymentMethod = session('checkout.payment_method');

        // Lock in the chosen address for the final 'process' step.
        session(['checkout.address_id' => $selectedAddress->id]);

        return view('checkout.index', [
            'selectedAddress' => $selectedAddress,
            'cartState'       => $cartState,
            'availablePaymentMethods' => $availablePaymentMethods,
            'selectedPaymentMethod' => $selectedPaymentMethod, 
            'isAddressStepComplete' => $isAddressStepComplete,
            'isPaymentStepComplete' => $isPaymentStepComplete, 
            'isCheckoutReady'       => $isCheckoutReady, 
        ]);
    }

    /**
     * Process the final order creation and payment initiation.
     */
    public function process(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:mtn_momo,cash_on_delivery',
            'momo_phone' => 'required_if:payment_method,mtn_momo|nullable|string',
        ]);

        $addressId = session('checkout.address_id');
        $cartState = $this->cartService->getCartState();
        $totals = $cartState['totals'];

        if (!$addressId || !$selectedAddress = Address::find($addressId)) {
            return response()->json(['message' => 'No shipping address selected.'], 422);
        }
        if ($cartState['items']->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => Order::STATUS_PENDING,
                'payment_method' => $request->payment_method,
                'subtotal' => $totals['subtotal'],
                'discount_code' => $totals['applied_discount']?->code,
                'discount_amount' => $totals['discount'],
                'shipping_cost' => $totals['shipping'],
                'tax_amount' => $totals['tax'],
                'total_amount' => $totals['grandTotal'],
                'billing_address' => $selectedAddress->toArray(),
                'shipping_address' => $selectedAddress->toArray(),
            ]);

            foreach ($cartState['items'] as $item) {
                $adjustable = $item->variant_id
                    ? ProductVariant::lockForUpdate()->findOrFail($item->variant_id)
                    : Product::lockForUpdate()->findOrFail($item->product_id);

                if ($adjustable->quantity < $item->quantity) {
                    throw new InsufficientStockException(
                        "Sorry, we only have {$adjustable->quantity} of '{$item->display_name}' available."
                    );
                }

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->variant_id, 
                    'product_name' => $item->display_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price_at_add,
                    'line_total' => $item->line_total,     
                ]);
                
                $quantityBefore = $adjustable->quantity;
                $adjustable->decrement('quantity', $item->quantity);

                $adjustable->stockAdjustments()->create([
                    'order_id' => $order->id, 'user_id' => Auth::id(),
                    'quantity_change' => -$item->quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $adjustable->fresh()->quantity,
                    'reason' => 'Order Placement', 'notes' => "Sale from Order #{$order->id}",
                ]);
            }
            
            if ($totals['applied_discount']) {
                $totals['applied_discount']->increment('times_used');
            }

            $paymentResult = $request->payment_method === 'mtn_momo'
                ? $this->processMtnMomoPayment($order, $request->momo_phone)
                : $this->processCodPayment($order);

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }

            DB::commit();

            Cart::where('user_id', Auth::id())->delete();
            session()->forget(['checkout.address_id', 'cart.discount_code']);

            return response()->json([
                'success' => true,
                'message' => $paymentResult['message'],
                'redirect_url' => route('checkout.success', $order->id),
            ]);

        } catch (InsufficientStockException $e) {            
            return response()->json(['message' => $e->getMessage()], 422);
        
        } catch (\Exception $e) {           
            Log::error('Checkout process failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    // --- ADDRESS MANAGEMENT METHODS ---

    public function showAddresses()
    {
        return view('checkout.addresses.show', ['addresses' => Auth::user()->addresses()->latest()->get()]);
    }

    public function selectAddress(Request $request)
    {
        $request->validate(['address_id' => 'required|exists:addresses,id,user_id,' . Auth::id()]);
        session(['checkout.address_id' => $request->address_id]);
        return redirect()->route('checkout.index');
    }

    public function createAddress()
    {
        return view('checkout.addresses.create');
    }

    public function storeAddress(StoreAddressRequest $request)
    {
        $address = Auth::user()->addresses()->create($request->validated());
        if (Auth::user()->addresses()->count() === 1) {
            $address->update(['is_default' => true]);
        }
        session(['checkout.address_id' => $address->id]);
        return redirect()->route('checkout.index')->with('success', 'Address added successfully.');
    }

    public function edit(Address $address)
    {
        // Ensure the user is authorized to edit this address
        $this->authorize('update', $address);

        return view('checkout.addresses.edit', compact('address'));
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        // Authorization is handled by the UpdateAddressRequest
        $address->update($request->validated());
        
        // If the "is_default" checkbox was checked, handle it.
        if ($request->has('is_default') && $request->is_default) {
            // Unset any other default addresses for this user
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        return redirect()->route('checkout.addresses.show')->with('success', 'Address updated successfully.');
    }

    // --- PAYMENT & POST-CHECKOUT METHODS ---       
    public function selectPaymentMethod(Request $request)
    {
        // You can add more robust validation here if needed
        $validated = $request->validate([
            'payment_method' => 'required|string',
        ]);

        session(['checkout.payment_method' => $validated['payment_method']]);

        return redirect()->route('checkout.index');
    }

        /**
     * Show the final, dedicated payment page.
     */
    public function showPaymentPage()
    {
        // Get all the necessary data
        $cartState = $this->cartService->getCartState();

        // Guards to prevent accessing this page directly without a valid cart/session
        if ($cartState['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        if (!session()->has('checkout.payment_method')) {
            return redirect()->route('checkout.index')->with('error', 'Please select a payment method first.');
        }

        // Define the payment method names again for the view
        $availablePaymentMethods = [
            'mtn_momo' => 'MTN Mobile Money',
            'cash_on_delivery' => 'Cash on Delivery',
        ];
        
        $selectedPaymentMethod = session('checkout.payment_method');

        
        return view('checkout.payment', compact(
            'cartState',
            'availablePaymentMethods',
            'selectedPaymentMethod'
        ));
    }

    private function processMtnMomoPayment(Order $order, string $phone): array
    {       
        $payment = $order->payments()->create([
            'payment_method' => 'mtn_momo',
            'amount' => $order->total_amount,
            'status' => Payment::STATUS_PENDING,
            'currency' => 'GHS',
            'payment_reference' => "ORDER-{$order->id}",
        ]);
       
        $result = $this->mtnMomoClient->requestToPay(
            (string) $order->total_amount,
            $phone,
            $payment->payment_reference, 
            "Payment for Order ID {$order->id}",
            "Thank you for your order!"
        );

        if ($result['success']) {           
            $payment->update([
                'transaction_id' => $result['momo_reference_id'],
            ]);
           
            return [
                'success' => true,
                'message' => 'Payment request sent. Please approve on your phone.',
                'redirect_url' => route('checkout.processing', ['order' => $order->id])
            ];
        }
       
        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'failure_reason' => $result['message'] ?? 'Payment initiation failed.',
        ]);

        return ['success' => false, 'message' => $result['message'] ?? 'Could not initiate payment.'];
    }

    private function processCodPayment(Order $order): array
    {
        $order->payments()->create([
            'payment_method' => 'cash_on_delivery',
            'amount' => $order->total_amount,
            'status' => Payment::STATUS_PENDING,
            'currency' => 'GHS',
        ]);
        $order->update(['status' => Order::STATUS_PROCESSING]);
        return [
        'success' => true,
        'message' => 'Order placed successfully! You will pay on delivery.',
        'redirect_url' => route('checkout.success', ['order' => $order->id]) 
    ];
    }
    
    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);            
        }

        $order->load('payments');
   
        return view('checkout.success', compact('order'));
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }

    public function showProcessingPage(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }        
       
        if ($order->payment_method !== 'mtn_momo') {
            return redirect()->route('orders.show', $order);
        }

        return view('checkout.processing', compact('order'));
    }

    
    public function checkPaymentStatus(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
       
        return response()->json([
            'status' => $order->status,
            'payment_status' => $order->payments()->latest()->first()?->status,
        ]);
    }
    
}