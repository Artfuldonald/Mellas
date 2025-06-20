<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get counts for the tabs
        $pendingCount = Review::where('is_approved', false)->count();
        $approvedCount = Review::where('is_approved', true)->count();

        // Start the base query
       $query = Review::select([
                                'id',
                                'product_id',
                                'user_id',
                                'reviewer_name',
                                'reviewer_email',
                                'rating',
                                'title',
                                'comment',
                                'is_approved',
                                'created_at',
                            ])->with(['product:id,name,slug', 'user:id,name'])
                            ->latest('created_at');

        // Filter by the 'status' tab
        if ($request->input('status') === 'approved') {
            $query->where('is_approved', true);
        } else {
            // Default to pending reviews
            $query->where('is_approved', false);
        }

        $reviews = $query->paginate(15)->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'pendingCount', 'approvedCount'));
    }

    /**
     * Approve a specific review.
     */
    public function approve(Review $review)
    {
        $review->update(['is_approved' => true]);

        return back()->with('success', 'Review has been approved.');
    }

    /**
     * Unapprove a specific review (move it back to pending).
     */
    public function unapprove(Review $review)
    {
        $review->update(['is_approved' => false]);

        return back()->with('success', 'Review has been unapproved and moved to pending.');
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Review has been permanently deleted.');
    }
}