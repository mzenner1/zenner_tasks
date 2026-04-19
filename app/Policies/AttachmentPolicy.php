<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
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
     * Any project member can view attachments.
     */
    public function view(User $user, Attachment $attachment): bool
    {
        $projectId = $this->resolveProjectId($attachment);
        $role = $user->projectRole($projectId);
        return in_array($role, ['admin', 'member', 'client']);
    }

    /**
     * Any project member can upload attachments.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the uploader or a project admin can delete an attachment.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        if ($attachment->user_id === $user->id) {
            return true;
        }

        $projectId = $this->resolveProjectId($attachment);
        $role = $user->projectRole($projectId);
        return $role === 'admin' || $user->isAdmin();
    }

    /**
     * Resolve the project_id from the attachable morph target (Task or Comment → Task).
     */
    private function resolveProjectId(Attachment $attachment): string
    {
        $attachable = $attachment->attachable;

        if ($attachable instanceof \App\Models\Task) {
            return $attachable->project_id;
        }

        if ($attachable instanceof \App\Models\Comment) {
            return $attachable->task->project_id;
        }

        return '';
    }
}
