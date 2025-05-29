<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Order;
use App\Models\WishlistItem;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    
    /**
     * The attributes that are mass assignable. 
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

     /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // --- SCOPES ---

    /**
     * Scope a query to only include admin users.
     */
    public function scopeIsAdmin(Builder $query): void 
    {
        $query->where('is_admin', true);
    }

    /**
     * Scope a query to only include non-admin users (customers).
     */
    public function scopeIsCustomer(Builder $query): void 
    {
        $query->where('is_admin', false);
    }

    public function reviews(): HasMany 
    {
        return $this->hasMany(Review::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    // Helper to check if a product is in the user's wishlist
    public function hasInWishlist(Product $product): bool
    {
        return $this->wishlistItems()->where('product_id', $product->id)->exists();
    }
}