<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'variant_id',
        'quantity',
        'variant_data',
        'price_at_add',
    ];

    protected $casts = [
        'variant_data' => 'array',
        'price_at_add' => 'decimal:2',
    ];

    public static function getItemQuantity(int $productId): int
    {
        $query = Auth::check()
            ? self::where('user_id', auth()->id())
            : self::where('session_id', session()->getId());

        $item = $query->where('product_id', $productId)
                      ->whereNull('variant_id')
                      ->first();

        return $item ? $item->quantity : 0;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalAttribute()
    {
        return $this->price_at_add * $this->quantity;
    }
}
