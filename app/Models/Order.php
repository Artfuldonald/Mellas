<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str; 

class Order extends Model
{
    use HasFactory;
    
    // ... Constants remain the same ...
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id', 'order_number', 'transaction_id', 'status', 'payment_status',
        'payment_method', 'subtotal', 'shipping_cost', 'tax_amount', 'total_amount',
        'shipping_address', 'billing_address', 'shipping_method', 'tracking_number',
        'notes', 'paid_at', 'shipped_at', 'delivered_at', 'cancelled_at',
    ];

    protected $casts = [
        'shipping_address' => 'array', 'billing_address' => 'array', 'paid_at' => 'datetime',
        'shipped_at' => 'datetime', 'delivered_at' => 'datetime', 'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2', 'shipping_cost' => 'decimal:2', 'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];
    
    // --- NEW: Automatically generate order number ---
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $prefix = 'ORD-' . now()->format('Ymd');
                $order->order_number = $prefix . '-' . strtoupper(Str::random(8));
            }
        });
    }

    // --- Relationships ---
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }

    // --- NEW: Eloquent Scopes for cleaner queries ---
    public function scopePaid($query) { return $query->where('payment_status', self::PAYMENT_PAID); }
    public function scopePending($query) { return $query->where('status', self::STATUS_PENDING); }

    // --- NEW: Accessor for status badge CSS class ---
    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSING => 'bg-blue-100 text-blue-800',
            self::STATUS_SHIPPED    => 'bg-cyan-100 text-cyan-800',
            self::STATUS_DELIVERED  => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED  => 'bg-red-100 text-red-800',
            self::STATUS_REFUNDED   => 'bg-purple-100 text-purple-800',
            default                 => 'bg-yellow-100 text-yellow-800', // Default to pending
        };
    }

    // --- Static Helpers ---
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