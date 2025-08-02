<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
             if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
             }
        });
    }

    /**
     * Get the attribute values for the attribute.
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('value');
    }

    /**
     * The products that belong to the attribute.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
