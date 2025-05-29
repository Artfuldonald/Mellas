<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $image
 * @property int|null $parent_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category|null $ancestors
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $descendants
 * @property-read int|null $descendants_count
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static Builder<static>|Category active()
 * @method static Builder<static>|Category newModelQuery()
 * @method static Builder<static>|Category newQuery()
 * @method static Builder<static>|Category query()
 * @method static Builder<static>|Category whereCreatedAt($value)
 * @method static Builder<static>|Category whereDescription($value)
 * @method static Builder<static>|Category whereId($value)
 * @method static Builder<static>|Category whereImage($value)
 * @method static Builder<static>|Category whereIsActive($value)
 * @method static Builder<static>|Category whereName($value)
 * @method static Builder<static>|Category whereParentId($value)
 * @method static Builder<static>|Category whereSlug($value)
 * @method static Builder<static>|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'is_active',
        'image',
    ];

    protected $casts = [
        'parent_id' => 'integer', // Good practice to cast foreign keys
        'is_active' => 'boolean', // <-- Add boolean cast here
    ];

    /**
     * Automatically generate the slug when creating or updating if it's empty.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($category) {
            if (empty($category->slug) || $category->isDirty('name')) {
                $slug = Str::slug($category->name);
                $count = static::where('slug', $slug)->where('id', '!=', $category->id ?? null)->count();
                if ($count > 0) {
                    $suffix = $category->id ? $category->id : time(); // Or use another unique suffix
                    $category->slug = $slug . '-' . $suffix;
                } else {
                    $category->slug = $slug;
                }
            }
        });
    }

    /**
     * Define the relationship with Products.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    // --- Relationships for Hierarchy ---
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    public function ancestors(): BelongsTo
    {
       return $this->parent()->with('ancestors');
    }

    // --- Scope ---
    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}