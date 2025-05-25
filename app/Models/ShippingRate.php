<?php

namespace App\Models;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_zone_id',
        'name',
        'cost',
        'is_active',
        'description',
        // Add future criteria to fillable if you add columns
        // 'min_order_subtotal',
    ];

    protected $casts = [
        'cost' => 'decimal:2', // Cast cost to 2 decimal places
        'is_active' => 'boolean',
         // Add casts for future criteria if needed
        // 'min_order_subtotal' => 'decimal:2',
    ];

    /**
     * Get the shipping zone that this rate belongs to.
     */
    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }
}