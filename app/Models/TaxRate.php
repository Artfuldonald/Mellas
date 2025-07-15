<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxRate extends Model
{
    use HasFactory;

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