<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::withTrashed()->where('google_id', $googleUser->getId())->first();

        if (! $user) {
            // Check if an account already exists with this email (e.g. invited user).
            $user = User::withTrashed()->where('email', $googleUser->getEmail())->first();

            if ($user?->trashed()) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'This account has been deactivated.']);
            }

            if ($user) {
                // Link the Google account to the existing user.
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $user->avatar ?? $googleUser->getAvatar(),
                ]);
            } else {
                // Create a brand-new account.
                $user = User::create([
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                    'password'  => null,
                ]);
            }
        }

        if ($user->trashed()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'This account has been deactivated.']);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
