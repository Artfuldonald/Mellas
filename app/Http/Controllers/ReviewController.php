<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(StoreReviewRequest $request)
    {
        $validatedData = $request->validated();

        // If the user is logged in, check if they've already reviewed this product.
        if (Auth::check()) {
            $existingReview = Review::where('user_id', Auth::id())
                                    ->where('product_id', $validatedData['product_id'])
                                    ->exists();

            if ($existingReview) {
                return response()->json([
                    'success' => false, 
                    'message' => 'You have already reviewed this product.'
                ], 422); // Unprocessable Entity
            }
        }

        Review::create([
            'product_id'     => $validatedData['product_id'],
            'user_id'        => Auth::id(), // Will be null if guest
            'reviewer_name'  => Auth::check() ? Auth::user()->name : $validatedData['reviewer_name'],
            'reviewer_email' => Auth::check() ? Auth::user()->email : $validatedData['reviewer_email'],
            'rating'         => $validatedData['rating'],
            'title'          => $validatedData['title'],
            'comment'        => $validatedData['comment'],
            'is_approved'    => false, // All new reviews require admin moderation
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your review has been submitted for approval.'
        ]);
    }
}