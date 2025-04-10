<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Import Str

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