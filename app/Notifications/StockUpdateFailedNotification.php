<?php
// app/Notifications/StockUpdateFailedNotification.php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route; // <-- Import the Route facade

class StockUpdateFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;
    public Throwable $exception;

    public function __construct(Order $order, Throwable $exception)
    {
        $this->order = $order;
        $this->exception = $exception;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Helper function to safely generate the order URL.
     */
    private function getOrderUrl(): string
    {
        $defaultUrl = '#'; // Fallback

        if (!$this->order || !$this->order->id) {
            Log::warning("StockUpdateFailedNotification: Order object or Order ID is missing when trying to generate URL.");
            return $defaultUrl;
        }

        // FIX: Use Route facade to check if the named route exists
        if (!Route::has('admin.orders.show')) { // <-- CORRECTED LINE
             Log::warning("StockUpdateFailedNotification: Route [admin.orders.show] not defined.");
             return $defaultUrl;
        }

        try {
            // Explicitly pass the order ID as the parameter
            return route('admin.orders.show', ['order' => $this->order->id]);
        } catch (\Exception $e) {
            Log::error("StockUpdateFailedNotification: Failed to generate URL for route [admin.orders.show] with Order ID [{$this->order->id}]. Error: " . $e->getMessage());
            return $defaultUrl;
        }
    }


    public function toMail(object $notifiable): MailMessage
    {
        $orderUrl = $this->getOrderUrl(); // Use helper function

        // ... (rest of toMail method remains the same) ...
         return (new MailMessage)
                    ->error()
                    ->subject("🚨 Stock Update Failed for Order #{$this->order->order_number}")
                    ->greeting("Hello " . ($notifiable->name ?? 'Admin') . ",")
                    ->line("The automated stock level update failed permanently for Order #{$this->order->order_number} (ID: {$this->order->id}).")
                    ->line("This means stock levels may be inaccurate and require manual correction.")
                    ->line('Error Message: ' . $this->exception->getMessage())
                    ->action('View Order', $orderUrl)
                    ->line('Please investigate this issue immediately.');
    }


    public function toArray(object $notifiable): array
    {
        $orderUrl = $this->getOrderUrl(); // Use helper function

        // ... (rest of toArray method remains the same) ...
         return [
            'order_id' => $this->order->id ?? null,
            'order_number' => $this->order->order_number ?? 'N/A',
            'message' => "Stock update failed for Order #" . ($this->order->order_number ?? '[Unknown]') . ". Manual check required.",
            'error_snippet' => Str::limit($this->exception->getMessage(), 100),
            'link' => $orderUrl,
            'icon' => 'heroicon-o-exclamation-triangle',
            'level' => 'error',
        ];
    }
}