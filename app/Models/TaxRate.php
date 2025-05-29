<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property numeric $rate
 * @property int $priority
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TaxRate extends Model
{
    //use HasFactory;

    protected $fillable = [
        'name',
        'rate',
        'priority',
        'is_active',
        // 'apply_to_shipping',
        // 'tax_zone_id',
    ];

    protected $casts = [
        'rate' => 'decimal:4', // Cast rate to 4 decimal places for precision
        'is_active' => 'boolean',
        'priority' => 'integer',
        // 'apply_to_shipping' => 'boolean',
    ];
}