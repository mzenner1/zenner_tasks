<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Super admins bypass all policy checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return null;
    }

    /**
     * Any authenticated user can see the project list (scoped via scopeForUser).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * User must be a member of the project.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Only admins can create projects.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only a project admin (or global admin) can edit project settings.
     */
    public function update(User $user, Project $project): bool
    {
        return in_array($user->projectRole($project->id), ['admin']) || $user->isAdmin();
    }

    /**
     * Only global admins can delete projects.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only a project admin (or global admin) can archive/restore.
     */
    public function archive(User $user, Project $project): bool
    {
        return in_array($user->projectRole($project->id), ['admin']) || $user->isAdmin();
    }

    /**
     * Only a project admin (or global admin) can manage members.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return in_array($user->projectRole($project->id), ['admin']) || $user->isAdmin();
    }

    /**
     * Only a project admin (or global admin) can manage status workflow.
     */
    public function manageStatuses(User $user, Project $project): bool
    {
        return in_array($user->projectRole($project->id), ['admin']) || $user->isAdmin();
    }

    /**
     * Only a project admin (or global admin) can manage tags.
     */
    public function manageTags(User $user, Project $project): bool
    {
        return in_array($user->projectRole($project->id), ['admin']) || $user->isAdmin();
    }
}
