<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'price',
        'quantity',
        'line_total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Get the order that owns the item.
     */
    public function order() 
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with the order item (if it's a simple product).
     */
    public function product() 
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant associated with the order item.
     */
    public function variant() 
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Helper to get the underlying product/variant model.
     */
    public function getItemModel()
    {
        return $this->variant ?? $this->product;
    }
}