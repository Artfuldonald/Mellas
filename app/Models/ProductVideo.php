<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $product_id
 * @property string $path
 * @property string|null $title
 * @property string|null $description
 * @property string|null $thumbnail_path
 * @property int $position
 * @property int $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVideo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductVideo extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'title',
        'description',
        'thumbnail_path',
        'position',
        'is_featured'
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}