<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends Model
{
    use HasFactory;

    // Define constants for types
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed_amount';

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_spend',
        'max_uses',
        'max_uses_per_user',
        'starts_at',
        'expires_at',
        'is_active',
        'times_used', // Note: Typically updated during checkout logic, not directly via admin form
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_spend' => 'decimal:2',
        'max_uses' => 'integer',
        'max_uses_per_user' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'times_used' => 'integer',
    ];

    /**
     * Get the formatted value for display (e.g., "10%" or "$15.00").
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return number_format($this->value, 0) . '%'; // Show percentage without decimals usually
        }
        return '$' . number_format($this->value, 2); // Format fixed amount as currency
    }

    /**
     * Scope a query to only include valid (active, within date range, not exceeded uses) discounts.
     * Useful for the checkout process later.
     */
    public function scopeValid(Builder $query): void
    {
        $now = now();
        $query->where('is_active', true)
              ->where(function ($q) use ($now) {
                  $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
              })
              ->where(function ($q) use ($now) {
                  $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
              })
              ->where(function ($q) {
                  $q->whereNull('max_uses')->orWhereColumn('times_used', '<', 'max_uses');
              });
    }
}