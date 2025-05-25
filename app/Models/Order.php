<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    
     // Define order status constants for consistency
     public const STATUS_PENDING = 'pending';
     public const STATUS_PROCESSING = 'processing';
     public const STATUS_SHIPPED = 'shipped';
     public const STATUS_DELIVERED = 'delivered';
     public const STATUS_CANCELLED = 'cancelled';
     public const STATUS_REFUNDED = 'refunded';
 
     // Define payment status constants
     public const PAYMENT_PENDING = 'pending';
     public const PAYMENT_PAID = 'paid';
     public const PAYMENT_FAILED = 'failed';
     public const PAYMENT_REFUNDED = 'refunded';
 
 
     protected $fillable = [
         'user_id',        
         'order_number',
         'transaction_id',
         'status',
         'payment_status',
         'payment_method',
         'subtotal',
         'shipping_cost',
         'tax_amount',
         'total_amount',
         'shipping_address',
         'billing_address',
         'shipping_method',
         'tracking_number',
         'notes',
         'paid_at',
         'shipped_at',
         'delivered_at',
         'cancelled_at',
     ];
 
     protected $casts = [
         'shipping_address' => 'array', // Cast JSON columns to arrays
         'billing_address' => 'array',
         'paid_at' => 'datetime',
         'shipped_at' => 'datetime',
         'delivered_at' => 'datetime',
         'cancelled_at' => 'datetime',
         'subtotal' => 'decimal:2', // Ensure correct casting for display/calculations
         'shipping_cost' => 'decimal:2',
         'tax_amount' => 'decimal:2',
         'total_amount' => 'decimal:2',
     ];
 
     /**
      * Get the user that owns the order.
      */
     public function user()
     {
         return $this->belongsTo(User::class);
     }
 
     /**
      * Get the items for the order.
      */
     public function items()
     {
         return $this->hasMany(OrderItem::class);
     }
 
     /**
      * Get all available order statuses.
      */
     public static function getStatuses(): array
     {
         return [
             self::STATUS_PENDING,
             self::STATUS_PROCESSING,
             self::STATUS_SHIPPED,
             self::STATUS_DELIVERED,
             self::STATUS_CANCELLED,
             self::STATUS_REFUNDED,
         ];
     }
 
      /**
      * Get all available payment statuses.
      */
     public static function getPaymentStatuses(): array
     {
         return [
             self::PAYMENT_PENDING,
             self::PAYMENT_PAID,
             self::PAYMENT_FAILED,
             self::PAYMENT_REFUNDED,
         ];
     }
}