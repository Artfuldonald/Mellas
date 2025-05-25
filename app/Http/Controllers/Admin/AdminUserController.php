<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Optional: Ensure only admins can access this
        // Gate::authorize('viewAny', User::class); // Example using policy

        // Use the scope to only get admin users
        $query = User::isAdmin() // <-- USE THE SCOPE
                     ->latest();

        // Filtering (optional for admins)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $admins = $query->paginate(20)->withQueryString();

        return view('admin.admin-users.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Gate::authorize('create', User::class); // Example policy/gate
        return view('admin.admin-users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Gate::authorize('create', User::class); // Example policy/gate

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            // Maybe add 'is_active' or other fields if needed
        ]);

        try {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => true, // <-- Set as admin
                'email_verified_at' => now(), // Optional: Auto-verify admins?
            ]);

            return redirect()->route('admin.admin-users.index')->with('success', 'Administrator created successfully.');

        } catch (\Exception $e) {
            // Log error
            return back()->with('error', 'Failed to create administrator.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $admin_user)
    {
        if (!$admin_user->is_admin) {
            abort(404); // Only show admins here
       }
       // Gate::authorize('view', $admin_user); // Example policy/gate
       return view('admin.admin-users.show', compact('admin_user')); // You might just redirect to edit
       // return redirect()->route('admin.admin-users.edit', $admin_user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $admin_user)
    {
        if (!$admin_user->is_admin) {
            abort(404); // Only edit admins here
       }
       // Gate::authorize('update', $admin_user); // Example policy/gate
       return view('admin.admin-users.edit', compact('admin_user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $admin_user)
    {
        if (!$admin_user->is_admin) {
            abort(403); // Only update admins here
       }
       // Gate::authorize('update', $admin_user); // Example policy/gate

       $validated = $request->validate([
           'name' => 'required|string|max:255',
           'email' => [
               'required',
               'string',
               'email',
               'max:255',
               Rule::unique('users')->ignore($admin_user->id),
           ],
           // Password update is optional
           'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
           // --- Crucial: Control changing admin status ---
           'is_admin' => 'required|boolean', // Validate it's submitted as true/false
       ]);

       // --- Permission Check: Prevent Self-Demotion (Example) ---
       if (Auth::id() === $admin_user->id && isset($validated['is_admin']) && !$validated['is_admin']) {
            return back()->with('error', 'You cannot remove your own administrator privileges.')->withInput();
       }
        // --- Permission Check: Prevent Demoting the only admin (Example - needs more robust logic) ---
        if (!$validated['is_admin'] && User::isAdmin()->count() <= 1 && $admin_user->is_admin) {
             return back()->with('error', 'Cannot remove the last administrator.')->withInput();
        }
        // --- Add more sophisticated Gate checks here ---


       // Handle password update
       if (!empty($validated['password'])) {
           $validated['password'] = Hash::make($validated['password']);
       } else {
           unset($validated['password']); // Don't update if empty
       }

       try {
           $admin_user->update($validated);
           return redirect()->route('admin.admin-users.index')->with('success', 'Administrator updated successfully.');
       } catch (\Exception $e) {
           // Log error
           return back()->with('error', 'Failed to update administrator.')->withInput();
       }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $admin_user)
    {
        if (!$admin_user->is_admin) {
            abort(403); // Only delete admins here
       }
       // Gate::authorize('delete', $admin_user); // Example policy/gate

       // --- CRITICAL CHECKS ---
       // Prevent Self-Deletion
       if (Auth::id() === $admin_user->id) {
           return back()->with('error', 'You cannot delete your own account.');
       }
       // Prevent Deleting Last Admin (Basic Check)
       if (User::isAdmin()->count() <= 1) {
            return back()->with('error', 'Cannot delete the last administrator.');
       }
       // --- End Critical Checks ---

       try {
           // Consider what happens to content owned by the admin (reassign?)
           $admin_user->delete(); // Or soft delete if preferred
           return redirect()->route('admin.admin-users.index')->with('success', 'Administrator deleted successfully.');
       } catch (\Exception $e) {
           // Log error
           return back()->with('error', 'Failed to delete administrator.');
       }
   }
}