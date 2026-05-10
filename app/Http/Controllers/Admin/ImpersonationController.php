<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function impersonate(User $user)
    {
        $admin = auth()->user();

        if ($admin->role !== 'super_admin') {
            abort(403);
        }

        if ($user->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot impersonate yourself.');
        }

        if ($user->role === 'super_admin') {
            return redirect()->back()->with('error', 'You cannot impersonate another super admin.');
        }

        // Store original admin ID before switching sessions
        session(['impersonating_original_id' => $admin->id]);

        Auth::loginUsingId($user->id);

        return redirect()->route('dashboard')
            ->with('success', "You are now logged in as {$user->name}.");
    }

    public function stopImpersonating()
    {
        $originalId = session('impersonating_original_id');

        if (! $originalId) {
            return redirect()->route('dashboard');
        }

        $original = User::find($originalId);

        if (! $original) {
            session()->forget('impersonating_original_id');
            return redirect()->route('dashboard');
        }

        session()->forget('impersonating_original_id');

        Auth::loginUsingId($original->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'You have returned to your own account.');
    }
}
