<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * 
 *
 * @property int $id
 * @property int $product_id
 * @property string $path
 * @property string|null $alt
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $image_url
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereAlt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path', 
        'alt', 
        'position'
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

     public function getImageUrlAttribute(): string
    {
        if ($this->path && Storage::disk('public')->exists($this->path)) {
            return Storage::url($this->path);
        }
        // Ensure this placeholder exists in public/images/placeholder.png
        return asset('images/placeholder.png');
    }
}