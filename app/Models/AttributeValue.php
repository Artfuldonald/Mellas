<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Import Str

/**
 * 
 *
 * @property int $id
 * @property int $attribute_id
 * @property string $value
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Attribute $attribute
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $productVariants
 * @property-read int|null $product_variants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeValue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeValue whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeValue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeValue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeValue whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeValue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttributeValue whereValue($value)
 * @mixin \Eloquent
 */
class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
    ];

    // Automatically generate slug when setting the value
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attributeValue) {
            if (empty($attributeValue->slug)) {
                $attributeValue->slug = Str::slug($attributeValue->value);
            }
        });

         static::updating(function ($attributeValue) {
             if (empty($attributeValue->slug)) {
                 $attributeValue->slug = Str::slug($attributeValue->value);
             }
         });
    }

    /**
     * Get the attribute that owns the value.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * The product variants that belong to the attribute value.
     * (Which specific variants use this value, e.g., which variants are 'Red')
     */
    public function productVariants()
    {
        // Assuming you will create this pivot table later
        return $this->belongsToMany(ProductVariant::class, 'attribute_value_product_variant');
    }
}