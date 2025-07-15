<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'email', 'phone',
        'gps_address', 'address_line_2', 'city', 'state',
        'postal_code', 'country', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper to get a formatted string of the address
    public function getFormattedAddressAttribute(): string
    {
        $parts = [$this->address_line_1, $this->city, $this->state, $this->postal_code];
        return implode(', ', array_filter($parts));
    }
}