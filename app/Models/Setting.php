<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property string $label
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 * @mixin \Eloquent
 */
class Setting extends Model
{
    use HasFactory;

    // Allow mass assignment for all fields used in the seeder/controller
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * The attributes that should be cast.
     * (We don't cast 'value' here as its type depends on the 'type' column)
     *
     * @var array
     */
    protected $casts = [
        // Add casts if needed, but value is tricky
    ];

    /**
     * Override boot method to clear cache when a setting is saved or deleted.
     */
    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            // Clear the specific setting cache and the 'all' settings cache
            Cache::forget('setting_' . $setting->key);
            Cache::forget('settings.all');
            // Log::info("Cache cleared for setting: {$setting->key}"); // Optional logging
        });

        static::deleted(function (Setting $setting) {
            Cache::forget('setting_' . $setting->key);
            Cache::forget('settings.all');
             // Log::info("Cache cleared for deleted setting: {$setting->key}"); // Optional logging
        });
    }

    /**
     * Helper function to get a setting value by key, with caching.
     *
     * @param string $key The setting key.
     * @param mixed $default Default value if not found.
     * @return mixed The setting value.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $cacheKey = 'setting_' . $key;

        // Remember forever until cleared by the boot method's saved/deleted events
        $setting = Cache::rememberForever($cacheKey, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if ($setting) {
            // Basic type casting based on the 'type' column
            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer', 'number' => (int) $setting->value,
                'float', 'decimal' => (float) $setting->value,
                default => $setting->value, // string, text, etc.
            };
        }

        return $default;
    }

     /**
     * Helper function to get all settings, grouped, with caching.
     * Useful for the settings admin page.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllGrouped(): \Illuminate\Support\Collection
    {
         // Remember forever until cleared by boot method
         return Cache::rememberForever('settings.all', function () {
             return self::orderBy('group')->orderBy('label')->get()->groupBy('group');
         });
    }
}