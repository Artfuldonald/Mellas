<?php

namespace App\Models;

use App\Models\ShippingRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the shipping rates associated with this zone.
     */
    public function shippingRates() 
    {
        // Assumes ShippingRate model exists and has 'shipping_zone_id'
        return $this->hasMany(ShippingRate::class);
    }
}