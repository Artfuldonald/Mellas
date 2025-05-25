<?php
// app/Listeners/UpdateStockLevel.php
namespace App\Listeners;

use Throwable;
use App\Models\User;
use App\Models\Product;
use App\Events\OrderPlaced;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;      
use Illuminate\Queue\InteractsWithQueue;   
use Illuminate\Contracts\Queue\ShouldQueue; 
use Illuminate\Support\Facades\Notification;
use App\Notifications\StockUpdateFailedNotification;

class UpdateStockLevel implements ShouldQueue 
{
    use InteractsWithQueue; 

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;
        // Check if order exists - important if model was deleted before queue runs
        if (!$order) {
             Log::warning("UpdateStockLevel listener: Order associated with the event no longer exists.");
             return;
        }

        Log::info("Processing stock update for Order ID: {$order->id} via queue.");

        // Ensure order items are loaded
        // Note: When using queues, relationships might need to be re-loaded
        // if the original model passed to the event didn't have them loaded
        // or if using ->withoutRelations() when dispatching.
        // loadMissing is generally safe.
        $order->loadMissing('items');

        if ($order->items->isEmpty()) {
             Log::warning("UpdateStockLevel listener: Order ID {$order->id} has no items to process.");
             return;
        }


        foreach ($order->items as $item) {
            // Use a transaction for each item update to ensure atomicity
            // and proper lock release.
            DB::transaction(function () use ($item, $order) { // Pass $order into transaction closure
                try {
                    if ($item->product_variant_id) {
                        // Find the variant WITH locking
                        $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

                        if ($variant) {
                            $originalQty = $variant->quantity; // Get qty before decrement
                            $variant->decrement('quantity', $item->quantity);
                            // Log the change accurately
                            Log::info("Decremented stock for Variant ID: {$variant->id} (SKU: {$variant->sku}) by {$item->quantity}. Qty: {$originalQty} -> {$variant->quantity}. Order ID: {$order->id}");

                            if ($variant->quantity < 0) {
                                Log::warning("Stock for Variant ID: {$variant->id} went negative after Order ID: {$order->id}. Possible oversell.");
                            }
                        } else {
                            Log::error("Could not find ProductVariant ID: {$item->product_variant_id} for OrderItem ID: {$item->id} during stock update. Order ID: {$order->id}");
                            // Consider throwing an exception here to fail the job if variant MUST exist
                            // throw new \Exception("Variant not found for stock update.");
                        }
                    } elseif ($item->product_id) {
                        // Find the simple product WITH locking
                        $product = Product::lockForUpdate()->find($item->product_id);

                        if ($product) {
                            $originalQty = $product->quantity; // Get qty before decrement
                            $product->decrement('quantity', $item->quantity);
                            Log::info("Decremented stock for Product ID: {$product->id} (SKU: {$product->sku}) by {$item->quantity}. Qty: {$originalQty} -> {$product->quantity}. Order ID: {$order->id}");

                            if ($product->quantity < 0) {
                                Log::warning("Stock for Product ID: {$product->id} went negative after Order ID: {$order->id}. Possible oversell.");
                            }
                        } else {
                            Log::error("Could not find Product ID: {$item->product_id} for OrderItem ID: {$item->id} during stock update. Order ID: {$order->id}");
                             // Consider throwing an exception here
                             // throw new \Exception("Product not found for stock update.");
                        }
                    } else {
                        Log::warning("OrderItem ID: {$item->id} has neither product_id nor product_variant_id. Order ID: {$order->id}");
                    }

                } catch (\Exception $e) {
                    // Log the error specifically within the transaction attempt
                    Log::error("Exception during stock update transaction for OrderItem ID: {$item->id} (Order ID: {$order->id}). Error: " . $e->getMessage());
                    // Re-throw the exception to make the transaction rollback and potentially fail the queue job for retry
                    throw $e;
                }
            }, 5); // Optional: Number of times to retry the transaction if a deadlock occurs

        } // End foreach loop

        Log::info("Finished stock update processing for Order ID: {$order->id}");
    }

     /**
      * Handle a job failure.
      * Optional: Add logic here if you want to do something specific when the job fails permanently.
      *
      * @param  \App\Events\OrderPlaced  $event
      * @param  \Throwable  $exception
      * @return void
      */
      public function failed(OrderPlaced $event, Throwable $exception): void // Use Throwable type hint
    {
        // Log the critical failure
        Log::critical("Permanent failure updating stock for Order ID: {$event->order->id}. Error: " . $exception->getMessage(), [
            'exception' => $exception // Log the full exception details
        ]);

        // --- Send Notification to Admins ---
        // 1. Find Admin Users
        $admins = User::isAdmin()->get(); // Get all users where is_admin = true

        if ($admins->isNotEmpty()) {
            // 2. Send the notification
            try {
                Notification::send($admins, new StockUpdateFailedNotification($event->order, $exception));
                Log::info("StockUpdateFailedNotification sent to {$admins->count()} admin(s) for Order ID: {$event->order->id}.");
            } catch (\Exception $notificationError) {
                // Log error if sending notification fails
                 Log::error("Failed to send StockUpdateFailedNotification for Order ID: {$event->order->id}. Error: " . $notificationError->getMessage());
            }
        } else {
            Log::warning("No admin users found to send StockUpdateFailedNotification for Order ID: {$event->order->id}.");
        }
        // ---------------------------------
    }
}