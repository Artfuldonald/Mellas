<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug', 
        'description',
         'logo_path', 
         'is_active'
        ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function products(): HasMany {
        return $this->hasMany(Product::class);
    }
    // Accessor for logo URL
    public function getLogoUrlAttribute(){
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::url($this->logo_path);
        }
        return null; // Or a default placeholder
    }
}
