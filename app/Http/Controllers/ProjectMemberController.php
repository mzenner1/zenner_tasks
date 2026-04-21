<?php

namespace App\Http\Controllers;

use App\Events\UserInvited;
use App\Models\Project;
use App\Models\User;
use App\Http\Requests\StoreProjectMemberRequest;

class ProjectMemberController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('manageMembers', $project);

        $members = $project->members()->orderBy('name')->get();

        $memberIds = $members->pluck('id');
        $existingUsers = User::whereNotIn('id', $memberIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('projects.members', compact('project', 'members', 'existingUsers'));
    }

    public function store(StoreProjectMemberRequest $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $isNewUser = false;

        if ($request->input('_mode') === 'existing') {
            $user = User::findOrFail($request->user_id);
        } else {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                $user = User::create([
                    'name'       => explode('@', $request->email)[0],
                    'email'      => $request->email,
                    'password'   => null,
                    'role'       => 'client',
                    'invited_by' => auth()->id(),
                ]);
                $isNewUser = true;
            }
        }

        if ($project->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('projects.members.index', $project)
                ->with('error', "{$user->name} is already a member of this project.");
        }

        // Use the user's global role as the project_role pivot value
        $project->members()->attach($user->id, ['project_role' => $user->role]);

        UserInvited::dispatch($user, $project, auth()->user());

        return redirect()->route('projects.members.index', $project)
            ->with('success', $isNewUser
                ? "{$user->name} was invited and added to the project."
                : "{$user->name} added to the project.");
    }

    public function destroy(Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        $project->members()->detach($user->id);

        return redirect()->route('projects.members.index', $project)
            ->with('success', "{$user->name} removed from project.");
    }
}
