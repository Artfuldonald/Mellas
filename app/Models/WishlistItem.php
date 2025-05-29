<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @property-read \App\Models\ProductVariant|null $variant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereUserId($value)
 * @mixin \Eloquent
 */
class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_variant_id', 
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo // Uncomment if using variants
    {
     return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}