<?php

namespace App\Http\Controllers;

use App\Events\UserInvited;
use App\Models\Project;
use App\Models\User;
use App\Http\Requests\StoreProjectMemberRequest;
use App\Http\Requests\UpdateProjectMemberRequest;

class ProjectMemberController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('manageMembers', $project);

        $members = $project->members()->orderBy('name')->get();

        return view('projects.members', compact('project', 'members'));
    }

    public function store(StoreProjectMemberRequest $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $isNewUser = false;
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Create placeholder user with null password — they'll set it via invite link
            $user = User::create([
                'name'       => explode('@', $request->email)[0],
                'email'      => $request->email,
                'password'   => null,
                'role'       => 'client',
                'invited_by' => auth()->id(),
            ]);
            $isNewUser = true;
        }

        // Avoid duplicate membership
        if ($project->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('projects.members.index', $project)
                ->with('error', "{$user->name} is already a member of this project.");
        }

        $project->members()->attach($user->id, ['project_role' => $request->project_role]);

        // Fire UserInvited event → sends ProjectInvitationNotification with signed URL
        UserInvited::dispatch($user, $project, auth()->user());

        return redirect()->route('projects.members.index', $project)
            ->with('success', $isNewUser
                ? "{$user->name} was invited and added to the project."
                : "{$user->name} added to the project.");
    }

    public function update(UpdateProjectMemberRequest $request, Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        $project->members()->updateExistingPivot($user->id, [
            'project_role' => $request->project_role,
        ]);

        return redirect()->route('projects.members.index', $project)
            ->with('success', "{$user->name}'s role updated.");
    }

    public function destroy(Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        $project->members()->detach($user->id);

        return redirect()->route('projects.members.index', $project)
            ->with('success', "{$user->name} removed from project.");
    }
}
