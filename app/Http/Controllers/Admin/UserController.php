<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminInviteNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(25);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load('projects');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'  => ['required', Rule::in(['super_admin', 'admin', 'member', 'client'])],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated.');
    }

    public function invite(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'  => ['required', Rule::in(['super_admin', 'admin', 'member', 'client'])],
        ]);

        $user = User::create([
            'name'       => explode('@', $request->email)[0],
            'email'      => $request->email,
            'password'   => null,
            'role'       => $request->role,
            'invited_by' => auth()->id(),
        ]);

        $token = Password::broker()->createToken($user);
        $setPasswordUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        $user->notify(new AdminInviteNotification(auth()->user(), $request->role, $setPasswordUrl));

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name} was invited and will receive an email to set their password.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
