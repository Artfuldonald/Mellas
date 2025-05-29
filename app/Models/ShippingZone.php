<?php

namespace App\Models;

use App\Models\ShippingRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ShippingRate> $shippingRates
 * @property-read int|null $shipping_rates_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingZone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingZone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingZone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingZone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingZone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingZone whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingZone whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingZone whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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