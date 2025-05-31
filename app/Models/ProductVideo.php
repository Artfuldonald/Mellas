<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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