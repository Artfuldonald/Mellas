<?php
// app/Http/Controllers/Webhook/MtnMomoWebhookController.php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Import DB for transaction
use App\Models\Order;
use App\Events\OrderPlaced; // Import the event
use Illuminate\Http\Response; // Import Response

class MtnMomoWebhookController extends Controller
{
    /**
     * Handle incoming MTN MoMo webhook notifications.
     */
    public function handle(Request $request): Response // Return type hint
    {
        // 1. Log Raw Payload (Essential for debugging)
        // Ensure you have a 'webhooks' channel configured in config/logging.php
        // Or just use the default log channel.
        Log::channel('webhooks')->info('MTN MoMo Webhook Received:', $request->all());

        // 2. TODO: Implement Signature Verification (CRUCIAL LATER)
        // $isValid = $this->verifyMtnSignature($request); // Example call
        // if (!$isValid) {
        //     Log::warning('Invalid MTN MoMo webhook signature received.');
        //     return response('Invalid signature', 403);
        // }
        // Log::info('MTN MoMo webhook signature verified.'); // Log success if implemented

        // 3. Extract Key Data (ADJUST KEYS BASED ON ACTUAL PAYLOAD)
        $payload = $request->all();
        $externalId = $payload['externalId'] ?? null; // Your unique transaction/order ID sent initially
        $mtnTransactionId = $payload['financialTransactionId'] ?? null; // MTN's reference
        $status = isset($payload['status']) ? strtolower($payload['status']) : null; // 'SUCCESSFUL', 'FAILED', etc.
        $reason = $payload['reason'] ?? null; // Reason for failure/status

        // 4. Basic Validation
        if (!$externalId || !$status) {
            Log::error('MTN MoMo webhook missing externalId or status.', $payload);
            // Respond 200 OK to prevent retries for fundamentally bad requests
            return response('Webhook acknowledged (missing data)', 200);
        }

        // 5. Find Order (using the ID *you* generated and sent to MTN)
        // Ensure you store this 'externalId' when creating the order or initiating payment
        $order = Order::where('transaction_id', $externalId) // Assuming you add a 'transaction_id' column
                      // ->orWhere('order_number', $externalId) // Or if using order_number
                      ->first();

        if (!$order) {
            Log::error("Webhook Error: Order not found for externalId: {$externalId}.");
            // Acknowledge receipt even if order not found to stop retries
            return response('Webhook acknowledged (order not found)', 200);
        }

        Log::info("Webhook: Found Order ID {$order->id} for externalId {$externalId}. Received MTN Status: {$status}. Current Order Payment Status: {$order->payment_status}");

        // 6. Idempotency Check: Avoid reprocessing completed orders
        if ($order->payment_status === Order::PAYMENT_PAID && $status === 'successful') {
             Log::info("Webhook: Order ID {$order->id} already paid. Ignoring duplicate success notification.");
             return response('Webhook acknowledged (already processed)', 200);
        }
        // Add checks for other terminal statuses if needed (cancelled, refunded)
        if (in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED])) {
             Log::info("Webhook: Order ID {$order->id} is already {$order->status}. Ignoring notification.");
             return response('Webhook acknowledged (order terminal)', 200);
        }

        // 7. Process Status Update within a Transaction
        try {
            DB::transaction(function () use ($order, $status, $mtnTransactionId, $reason) {
                if ($status === 'successful') {
                    $order->payment_status = Order::PAYMENT_PAID;
                    $order->status = Order::STATUS_PROCESSING; // Or 'completed' if no processing needed
                    $order->paid_at = now();
                    $order->payment_gateway_reference = $mtnTransactionId; // Store MTN's ID if needed
                    $order->save();

                    Log::info("Webhook: Order ID {$order->id} updated to PAID and PROCESSING.");

                    // Dispatch event for stock update etc.
                    OrderPlaced::dispatch($order);
                    Log::info("Webhook: OrderPlaced event dispatched for Order ID {$order->id}.");

                } elseif ($status === 'failed') {
                    $order->payment_status = Order::PAYMENT_FAILED;
                    // Optionally update main status or add notes
                    $order->notes = trim(($order->notes ?? '') . "\nPayment Failed Reason: " . ($reason ?? 'Unknown'));
                    $order->save();
                    Log::warning("Webhook: Order ID {$order->id} marked as PAYMENT_FAILED. Reason: " . ($reason ?? 'Unknown'));
                    // TODO: Maybe notify admin or customer of failure?
                } else {
                    // Handle other potential statuses if needed (e.g., PENDING - might just log)
                    Log::info("Webhook: Received status '{$status}' for Order ID {$order->id}. No state change applied.");
                }
            }); // End Transaction

            // 8. Respond to MTN
            return response('Webhook processed successfully', 200);

        } catch (\Exception $e) {
            DB::rollBack(); // Ensure rollback on exception
            Log::critical("Webhook Processing Error for Order ID {$order->id}, externalId {$externalId}: " . $e->getMessage(), [
                'exception' => $e,
                'payload' => $payload // Log payload on critical error
            ]);
            // Return 500 to signal an internal error - MTN might retry
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Placeholder for signature verification logic
     */
    // private function verifyMtnSignature(Request $request): bool
    // {
    //     // Implement actual verification based on MTN documentation
    //     // Compare request header signature with calculated hash using your secret
    //     return true; // Placeholder
    // }
}