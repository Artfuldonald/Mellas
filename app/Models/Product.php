<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
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

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get all of the product's reviews.
     */
    public function reviews(): HasMany // <-- Add this
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get approved reviews for the product.
     */
    public function approvedReviews(): HasMany // <-- Add this
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
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
}