<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
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
     * Any project member can view public comments.
     * Internal comments are hidden from clients via scopeVisibleTo().
     */
    public function view(User $user, Comment $comment): bool
    {
        $role = $user->projectRole($comment->task->project_id);

        if (!in_array($role, ['admin', 'member', 'client'])) {
            return false;
        }

        // Clients cannot see internal comments
        if ($comment->is_internal && $role === 'client') {
            return false;
        }

        return true;
    }

    /**
     * Any project member can post public comments.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only admins and members can create internal comments.
     */
    public function createInternal(User $user, string $projectId): bool
    {
        $role = $user->projectRole($projectId);
        return in_array($role, ['admin', 'member']);
    }

    /**
     * Only the comment author or a project admin can edit a comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        if ($comment->user_id === $user->id) {
            return true;
        }

        $role = $user->projectRole($comment->task->project_id);
        return $role === 'admin' || $user->isAdmin();
    }

    /**
     * Only the comment author or a project admin can delete a comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        if ($comment->user_id === $user->id) {
            return true;
        }

        $role = $user->projectRole($comment->task->project_id);
        return $role === 'admin' || $user->isAdmin();
    }
}
