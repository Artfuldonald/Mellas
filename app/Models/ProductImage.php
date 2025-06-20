<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path', 
        'alt', 
        'position'
    ];

    protected $appends = ['image_url'];
    
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