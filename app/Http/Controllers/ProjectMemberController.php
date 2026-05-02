<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Http\Requests\StoreProjectMemberRequest;
use App\Notifications\ProjectInvitationNotification;
use App\Notifications\ProjectAddedNotification;
use Illuminate\Http\Request;

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

        $project->members()->attach($user->id);

        if ($isNewUser) {
            $user->notify(new ProjectInvitationNotification($project, auth()->user()));
        } else {
            $user->notify(new ProjectAddedNotification($project, auth()->user()));
        }

        return redirect()->route('projects.members.index', $project)
            ->with('success', $isNewUser
                ? "{$user->name} was invited by email and added to the project."
                : "{$user->name} was added to the project and notified by email.");
    }

    public function checkTasks(Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        $taskCount = $project->tasks()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->count();

        $otherMembers = $project->members()
            ->where('users.id', '!=', $user->id)
            ->orderBy('name')
            ->get(['users.id', 'users.name']);

        return response()->json([
            'task_count'    => $taskCount,
            'other_members' => $otherMembers,
        ]);
    }

    public function destroy(Request $request, Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        // Collect tasks in this project assigned to the removed user
        $assignedTasks = $project->tasks()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->with('assignees')
            ->get();

        if ($assignedTasks->isNotEmpty()) {
            $reassignTo = $request->input('reassign_to');

            foreach ($assignedTasks as $task) {
                $task->assignees()->detach($user->id);

                if ($reassignTo) {
                    // Only reassign if the target is still a project member (other than the removed user)
                    $targetIsValid = $project->members()
                        ->where('users.id', $reassignTo)
                        ->where('users.id', '!=', $user->id)
                        ->exists();

                    if ($targetIsValid && !$task->assignees()->where('users.id', $reassignTo)->exists()) {
                        $task->assignees()->attach($reassignTo);
                    }
                }
            }
        }

        $project->members()->detach($user->id);

        return redirect()->route('projects.members.index', $project)
            ->with('success', "{$user->name} removed from project.");
    }

    public function search(\Illuminate\Http\Request $request, Project $project)
    {
        $query = $request->get('q', '');

        $members = $project->members()
            ->where('name', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->get(['users.id', 'users.name'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name]);

        return response()->json($members);
    }
}
