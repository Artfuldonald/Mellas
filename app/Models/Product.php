<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as CastsAttribute;


class Product extends Model 
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',         
        'brand_id',
        'short_description',
        'specifications',   
        'price',
        'compare_at_price',
        'cost_price',
        'sku',
        'quantity',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
        'weight',
        'weight_unit',
        'dimensions'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'float',
        'compare_at_price' => 'float',
        'cost_price' => 'float',
        'quantity' => 'integer',
        'specifications' => 'array', 
        'dimensions' => 'array',
    ];    
    

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    
    public function brand(): BelongsTo 
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class);
    }   
   
    public function reviews(): HasMany 
    {
        return $this->hasMany(Review::class);
    }
   
    public function approvedReviews(): HasMany 
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function images()
    {
        return $this->hasMany(\App\Models\ProductImage::class);
    }

    public function videos()
    {
        return $this->hasMany(ProductVideo::class);
    }

    public function stockAdjustments(): MorphMany
    {
        return $this->morphMany(StockAdjustment::class, 'adjustable');
    }

   
    public function getAverageRatingAttribute(): ?float
    {
        return $this->approvedReviews()->avg('rating');
    }

    
    public function getApprovedReviewsCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    public function scopeForCard(Builder $query): void
    {
        $query->where('is_active', true)
            ->with(['images' => fn($i) => $i->orderBy('position')->limit(1)])
            ->withCount('variants'); // <-- ADD THIS LINE
    }

    protected function stockCount(): CastsAttribute
    {
        return CastsAttribute::make(
            get: function () {
                if (!isset($this->variants_count)) { $this->loadCount('variants'); }
                if ($this->variants_count > 0) {
                    return $this->relationLoaded('variants')
                        ? $this->variants->where('is_active', true)->sum('quantity')
                        : $this->variants()->where('is_active', true)->sum('quantity');
                }
                return $this->quantity;
            }
        );
    }

    protected function currentPrice(): CastsAttribute
    {
        return CastsAttribute::make(get: fn () => $this->price);
    }
    
    protected function originalPrice(): CastsAttribute
    {
        return CastsAttribute::make(get: fn () => $this->compare_at_price);
    }

    protected function discountPercentage(): CastsAttribute
    {
        return CastsAttribute::make(
            get: function () {
                if ($this->compare_at_price > 0 && $this->compare_at_price > $this->price) {
                    return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
                }
                return 0;
            }
        );
    }
    
}