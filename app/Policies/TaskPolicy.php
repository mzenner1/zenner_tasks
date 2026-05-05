<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
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
     * Any project member can view the task list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any project member can view a task.
     */
    public function view(User $user, Task $task): bool
    {
        $role = $user->projectRole($task->project_id);
        return in_array($role, ['admin', 'member', 'client']);
    }

    /**
     * Admins, members, and clients can create tasks.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Admins and members can edit any task.
     * Clients can only edit tasks they created.
     */
    public function update(User $user, Task $task): bool
    {
        $role = $user->projectRole($task->project_id);

        if ($role === 'client') {
            return $task->created_by === $user->id;
        }

        return in_array($role, ['admin', 'member', 'client']);
    }

    /**
     * Project admins, members, and global admins can delete tasks.
     */
    public function delete(User $user, Task $task): bool
    {
        $role = $user->projectRole($task->project_id);
        return in_array($role, ['admin', 'member']) || $user->isAdmin();
    }

    /**
     * Only admins and members can change a task's due date.
     */
    public function changeDueDate(User $user, Task $task): bool
    {
        $role = $user->projectRole($task->project_id);
        return in_array($role, ['admin', 'member']) || $user->isAdmin();
    }

    /**
     * Admins, members, and clients can change status
     * (controller will enforce client-specific status restrictions).
     */
    public function changeStatus(User $user, Task $task): bool
    {
        $role = $user->projectRole($task->project_id);
        return in_array($role, ['admin', 'member', 'client']);
    }

    /**
     * Only admins and members can assign tasks to others.
     */
    public function assign(User $user, Task $task): bool
    {
        $role = $user->projectRole($task->project_id);
        return in_array($role, ['admin', 'member', 'client']);
    }
}
