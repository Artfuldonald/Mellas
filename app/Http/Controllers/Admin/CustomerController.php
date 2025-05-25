<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::isCustomer() // <-- USE THE SCOPE
                     ->withCount('orders')
                     ->latest();

        // Filtering
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $customer)
    {
         // Prevent viewing admin users via the customer section
         if ($customer->is_admin) {
            abort(404, 'Admin users cannot be viewed here.');
        }

        $customer->loadCount('orders');
        $customer->load(['orders' => function($query) {
            $query->latest()->limit(15);
        }]);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $customer)
    {
        // Prevent editing admin users via the customer section
        if ($customer->is_admin) {
            abort(404, 'Admin users cannot be edited here.');
       }

       return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $customer)
    {
       // Prevent updating admin users via the customer section
       if ($customer->is_admin) {
        abort(403, 'Admin users cannot be updated here.');
   }

   $validated = $request->validate([
       'name' => 'required|string|max:255',
       'email' => [
           'required',
           'string',
           'email',
           'max:255',
           Rule::unique('users')->ignore($customer->id),
       ],
       // 'password' => 'nullable|string|min:8|confirmed',
        // Add is_admin validation *only if* you want to allow making a customer an admin from here
        // 'is_admin' => 'sometimes|boolean' // Be very careful with this permission!
   ]);

   // IMPORTANT: Do NOT allow changing 'is_admin' via this customer update form
   // unless you have specific permissions logic in place.
   // Remove 'is_admin' from validated data if it somehow gets submitted.
   unset($validated['is_admin']);

   try {
       $customer->update($validated);
       return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated successfully.');
   } catch (\Exception $e) {
       // Log error $e->getMessage()
       return back()->with('error', 'Failed to update customer.')->withInput();
   }
}
    

    
    public function sendPasswordResetLink(Request $request, User $customer)
    {
        // Ensure we are dealing with a customer
        if ($customer->is_admin) {
            abort(403, 'Cannot send password reset for admin users via this interface.');
        }

        // Optional: Add Authorization check (Gate/Policy)
        // Gate::authorize('sendPasswordReset', $customer);
        try {
            $status = Password::broker()->sendResetLink(['email' => $customer->email]);

            if ($status == Password::RESET_LINK_SENT) {
                // Safely get the admin ID
                $adminId = Auth::check() ? Auth::id() : '[System/Unknown]'; // <-- Use Auth facade and check
                Log::info("Password reset link sent to customer: {$customer->email} (ID: {$customer->id}) by admin ID: " . $adminId); // <-- Updated log message
                return back()->with('success', 'Password reset link sent successfully to ' . $customer->email);
            } else {
                 // Log the specific status for debugging if needed
                Log::warning("Password reset link failed to send for customer: {$customer->email} (ID: {$customer->id}). Status: {$status}");
                // Translate the status code to a user-friendly message
                $errorMessage = match ($status) {
                    Password::INVALID_USER => 'No user found with that email address.',
                    Password::RESET_THROTTLED => 'Password reset throttled. Please try again later.',
                    default => 'Failed to send password reset link. Please try again.',
                };
                return back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error("Error sending password reset link for customer ID {$customer->id}: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred while sending the password reset link.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $customer)
    {
        // Prevent deleting admin users via the customer section
        if ($customer->is_admin) {
            abort(403, 'Admin users cannot be deleted.');
        }

        // Keep original logic (abort, soft delete, or deactivate)
        abort(403, 'Deleting customers is not permitted.');

        // Or implement soft delete / deactivation as before
    }
     
}