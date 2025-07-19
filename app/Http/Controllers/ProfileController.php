<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function overview(Request $request)
    {
        $user = $request->user();

        // Find the default address. If none is set, get the most recent one as a fallback.
        $defaultAddress = $user->addresses()->where('is_default', true)->first()
            ?? $user->addresses()->latest()->first();

        return view('profile.overview', [
            'user' => $user,
            'defaultAddress' => $defaultAddress, 
        ]);
    }

    /**
     * Display the user's address book.
     */
    public function addressBook(Request $request)
    {
        // In the future, you will fetch all saved addresses here.
        // For now, we just need the view.
        return view('profile.address-book', [
            'user' => $request->user(),
        ]);
    }
}
