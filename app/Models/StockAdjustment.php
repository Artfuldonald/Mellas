<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $adjustable_type
 * @property int $adjustable_id
 * @property int $quantity_change
 * @property int $quantity_before
 * @property int $quantity_after
 * @property string|null $reason
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $adjustable
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereAdjustableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereAdjustableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereQuantityAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereQuantityBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereQuantityChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockAdjustment whereUserId($value)
 * @mixin \Eloquent
 */
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