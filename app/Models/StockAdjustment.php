<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'adjustable_id',
        'adjustable_type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'reason',
        'notes',
    ];

    public function adjustable(): MorphTo
    {
        return $this->morphTo(); // Links to Product or ProductVariant
    }

    public function user(): BelongsTo // Admin who made the change
    {
        return $this->belongsTo(User::class);
    }
}