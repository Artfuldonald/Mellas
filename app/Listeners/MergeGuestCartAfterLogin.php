<?php

namespace App\Listeners;

use App\Models\Cart;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MergeGuestCartAfterLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * This will merge the guest cart (session_id) with the user's cart (user_id).
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $sessionId = session()->getId();

        // Find all cart items associated with the guest's session
        $guestCartItems = Cart::where('session_id', $sessionId)->get();

        if ($guestCartItems->isEmpty()) {
            return; // No guest cart to merge, so we do nothing.
        }

        foreach ($guestCartItems as $guestItem) {
            // Check if an identical item (product + variant) already exists in the user's cart
            $userCartItem = Cart::where('user_id', $user->id)
                                ->where('product_id', $guestItem->product_id)
                                ->where('variant_id', $guestItem->variant_id)
                                ->first();

            if ($userCartItem) {
                // If it exists, update the quantity (or just keep the user's version)
                // For simplicity, we can add the quantities. Add stock checks here if needed.
                $userCartItem->quantity += $guestItem->quantity;
                $userCartItem->save();

                // Delete the guest item since it has been merged
                $guestItem->delete();
            } else {
                // If it doesn't exist, simply assign the item to the logged-in user
                // and clear the session_id.
                $guestItem->user_id = $user->id;
                $guestItem->session_id = null;
                $guestItem->save();
            }
        }
    }
}