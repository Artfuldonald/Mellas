<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->orderBy('position', 'asc');
    }

    public function firstImage()
    {
        return $this->hasOne(ProductImage::class)->oldest('id');
    }
   
    public function reviews(): HasMany 
    {
        return $this->hasMany(Review::class);
    }
   
    public function approvedReviews(): HasMany 
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