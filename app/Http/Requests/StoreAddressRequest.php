<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255'],
            'phone'             => ['required', 'string', 'max:20'],
            'gps_address'       => ['required', 'string', 'max:255'],
            'address_line_2'    => ['nullable', 'string', 'max:255'], 
            'city'              => ['required', 'string', 'max:255'],
            'state'            => ['required', 'string', 'max:255'], 
            'postal_code'       => ['required', 'string', 'max:20'],
            'country'           => ['required', 'string', 'size:2'], // Expects 2-letter ISO code e.g., GH, US, NG
            'is_default'        => ['nullable', 'boolean'],
        ];
    }
}
