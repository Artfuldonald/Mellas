<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $order_number
 * @property string $status
 * @property string $payment_status
 * @property string|null $payment_method
 * @property numeric $subtotal
 * @property numeric $shipping_cost
 * @property numeric $tax_amount
 * @property numeric $total_amount
 * @property array<array-key, mixed>|null $shipping_address
 * @property array<array-key, mixed>|null $billing_address
 * @property string|null $shipping_method
 * @property string|null $tracking_number
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $transaction_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 * @mixin \Eloquent
 */
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