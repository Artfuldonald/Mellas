<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller.
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Base rules that apply to everyone
        $rules = [
            'product_id' => 'required|integer|exists:products,id',
            'rating'     => 'required|integer|between:1,5',
            'title'      => 'required|string|max:100',
            'comment'    => 'required|string|max:1000',
        ];

        // Add rules for guests (non-authenticated users)
        if (!Auth::check()) {
            $rules['reviewer_name'] = 'required|string|max:255';
            $rules['reviewer_email'] = 'required|email|max:255';
        }

        return $rules;
    }
}