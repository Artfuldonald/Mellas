<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Import Str

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