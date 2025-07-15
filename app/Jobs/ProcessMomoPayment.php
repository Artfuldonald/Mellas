<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Payment; // Assuming you have a Payment model

class ProcessMomoPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The payload from the MTN MoMo webhook.
     */
    public function __construct(public array $payload)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      $paymentReference = $this->payload['externalId'] ?? null;
        if (!$paymentReference) {
            Log::error('MTN MoMo Job: Payload missing externalId.', ['payload' => $this->payload]);
            return;
        }

        DB::transaction(function () use ($paymentReference) {
            // Find the payment using the reference we created.
            $payment = Payment::where('payment_reference', $paymentReference)->lockForUpdate()->first();

            if (!$payment) {
                Log::warning('MTN MoMo Job: Received webhook for unknown payment reference.', ['ref' => $paymentReference]);
                return;
            }

            // Idempotency check: If it's already processed, do nothing.
            if (!$payment->isPending()) {
                Log::info('MTN MoMo Job: Duplicate webhook for an already processed payment.', ['ref' => $paymentReference]);
                return;
            }

            $newStatus = $this->payload['status']; // SUCCESSFUL or FAILED
            $gatewayTransactionId = $this->payload['financialTransactionId'] ?? null;

            if ($newStatus === 'SUCCESSFUL') {
                $payment->update([
                    'status' => Payment::STATUS_SUCCESSFUL,
                    'transaction_id' => $gatewayTransactionId, // The real transaction ID from MTN
                    'gateway_response' => $this->payload,
                    'paid_at' => now(),
                ]);
                // Also update the parent order status
                $payment->order()->update(['status' => Order::STATUS_PROCESSING]);
                Log::info("Payment {$payment->id} successful. Order {$payment->order_id} moved to processing.");
                // TODO: Send success notifications, etc.

            } else { // FAILED
                $payment->update([
                    'status' => Payment::STATUS_FAILED,
                    'transaction_id' => $gatewayTransactionId,
                    'failure_reason' => $this->payload['reason'] ?? 'Transaction failed at gateway.',
                    'gateway_response' => $this->payload,
                    'failed_at' => now(),
                ]);
                // The order remains PENDING, allowing the user to try paying again.
                // We also need to reverse the stock decrement.
                // This is complex, so for now, we'll log it.
                // A more advanced system would use a "StockReversalJob".
                Log::warning("Payment {$payment->id} failed. Order {$payment->order_id} stock should be reversed.", [
                    'reason' => $this->payload['reason'] ?? 'Unknown'
                ]);
                // TODO: Send failure notifications.
            }
        });
       
    }
}