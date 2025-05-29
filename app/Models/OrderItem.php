<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property int|null $product_variant_id
 * @property string $product_name
 * @property string|null $variant_name
 * @property string|null $sku
 * @property numeric $price
 * @property int $quantity
 * @property numeric $line_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\ProductVariant|null $variant
 * @method static \Database\Factories\OrderItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereVariantName($value)
 * @mixin \Eloquent
 */
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