<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator; // Import Validator

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
            'title' => ['nullable', 'string', 'max:100'],
            // Guest fields (only required if user is not logged in)
            'reviewer_name' => [Auth::guest() ? 'required' : 'nullable', 'string', 'max:255'],
            'reviewer_email' => [Auth::guest() ? 'required' : 'nullable', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()->to(url()->previous() . '#reviews-form-section') // Or specific route to PDP
                             ->withErrors($validator)
                             ->withInput();
        }

        // Check if the authenticated user has already reviewed this product (optional)
        if (Auth::check()) {
            $existingReview = Review::where('product_id', $product->id)
                                    ->where('user_id', Auth::id())
                                    ->first();
            if ($existingReview) {
                return redirect()->to(url()->previous() . '#reviews-form-section')
                                 ->with('error', 'You have already reviewed this product.')
                                 ->withInput();
            }
        } else {
            // For guest, you might check by email if you want to limit one review per email (more complex)
        }


        $review = new Review();
        $review->product_id = $product->id;
        $review->rating = $request->input('rating');
        $review->comment = $request->input('comment');
        $review->title = $request->input('title');

        if (Auth::check()) {
            $review->user_id = Auth::id();
            // For reviewer_name and reviewer_email, you could still allow override or fetch from user profile
            $review->reviewer_name = Auth::user()->name; // Or $request->input('reviewer_name', Auth::user()->name);
            $review->reviewer_email = Auth::user()->email; // Or $request->input('reviewer_email', Auth::user()->email);
        } else {
            $review->reviewer_name = $request->input('reviewer_name');
            $review->reviewer_email = $request->input('reviewer_email');
        }

        // Set 'is_approved' based on your site's policy
        // For example, auto-approve if a setting is enabled, otherwise default to false for moderation
        $review->is_approved = config('settings.reviews.auto_approve', false); // Example: get from config
        // Or simply: $review->is_approved = false; // Always requires moderation

        $review->save();

        // You might want to send a notification to admin for new review moderation

        return redirect()->to(url()->previous() . '#reviews') // Or specific route to PDP, linking to reviews tab
                         ->with('success', 'Thank you! Your review has been submitted' . (!$review->is_approved ? ' and is awaiting approval.' : '.'));
    }

    // You might add other methods here later like edit, update, destroy for user's own reviews or admin management
}