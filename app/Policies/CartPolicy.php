<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CartPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Cart $cart): bool
    {
        return $this->belongsToUser($user, $cart); 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Cart $cart): bool
    {
        return $this->belongsToUser($user, $cart);
    }

    /**
     * Helper to check if the cart item belongs to the current user or session.
     */
    private function belongsToUser(?User $user, Cart $cart): bool
    {
        if ($user) {
            return $cart->user_id === $user->id;
        }
        
        return $cart->session_id === session()->getId() && is_null($cart->user_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Cart $cart): bool
    {
        return $this->belongsToUser($user, $cart);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cart $cart): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cart $cart): bool
    {
        return false;
    }
}
