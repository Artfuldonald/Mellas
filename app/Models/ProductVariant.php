<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * 
 *
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property string $sku
 * @property string $price
 * @property int $quantity
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AttributeValue> $attributeValues
 * @property-read int|null $attribute_values_count
 * @property-read \App\Models\Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockAdjustment> $stockAdjustments
 * @property-read int|null $stock_adjustments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'quantity',
        'is_active'
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_value_product_variant');
    }

    public function stockAdjustments(): MorphMany
    {
        return $this->morphMany(StockAdjustment::class, 'adjustable');
    }
}