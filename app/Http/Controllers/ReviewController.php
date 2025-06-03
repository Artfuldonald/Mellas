<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; 

class ReviewController extends Controller
{
    public function __construct()
    {        
        $this->middleware('auth')->only(['store']);
    }

    public function store(Request $request, Product $product)
    {     
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return redirect()->to(url()->previous() . '#reviews-form-section')
                             ->withErrors($validator)
                             ->withInput();
        }
        
        $existingReview = Review::where('product_id', $product->id)
                                ->where('user_id', $user->id)
                                ->first();
        if ($existingReview) {
            return redirect()->to(url()->previous() . '#reviews-form-section')
                             ->with('error', 'You have already reviewed this product.')
                             ->withInput();
        }

        try {
            $review = new Review();
            $review->product_id = $product->id;
            $review->user_id = $user->id; // User is authenticated
            $review->reviewer_name = $user->name; // Use authenticated user's name
            $review->reviewer_email = $user->email; // Use authenticated user's email
            $review->rating = $request->input('rating');
            $review->comment = $request->input('comment');
            // 'title' is removed

            $review->is_approved = config('settings.reviews.auto_approve', false); // Or your default policy

            $review->save();

            return redirect()->to(url()->previous() . '#reviews')
                             ->with('success', 'Thank you! Your review has been submitted' . (!$review->is_approved ? ' and is awaiting approval.' : '.'));

        } catch (\Exception $e) {
            Log::error("Error submitting review for product {$product->id} by user {$user->id}: " . $e->getMessage());
            return redirect()->to(url()->previous() . '#reviews-form-section')
                             ->with('error', 'There was an error submitting your review. Please try again.')
                             ->withInput();
        }
    }
}