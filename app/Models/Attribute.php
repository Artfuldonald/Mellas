<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Import Str

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AttributeValue> $values
 * @property-read int|null $values_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attribute whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    // Automatically generate slug when setting the name
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });

        static::updating(function ($attribute) {
             // Optionally update slug if name changes, be careful with existing links
            // if ($attribute->isDirty('name') && empty($attribute->slug)) {
            //     $attribute->slug = Str::slug($attribute->name);
            // }
             if (empty($attribute->slug)) { // Ensure slug exists if name is set
                $attribute->slug = Str::slug($attribute->name);
             }
        });
    }


    /**
     * Get the attribute values for the attribute.
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('value'); // Order values alphabetically by default
    }

    /**
     * The products that belong to the attribute.
     * (Which products CAN use this attribute)
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}