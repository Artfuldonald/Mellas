<?php

namespace App\Models;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property int $shipping_zone_id
 * @property string $name
 * @property numeric $cost
 * @property bool $is_active
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ShippingZone $shippingZone
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereShippingZoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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