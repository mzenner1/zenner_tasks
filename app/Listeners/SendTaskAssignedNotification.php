<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Notifications\TaskAssignedNotification;

class SendTaskAssignedNotification
{
    public function handle(TaskCreated $event): void
    {
        $task = $event->task;

        // Auto-watch: the task creator watches the task
        $task->addWatcher($task->created_by);

        // Auto-watch: each assignee watches the task
        foreach ($task->assignees as $assignee) {
            $task->addWatcher($assignee->id);
        }

        // Notify assignees (excluding the creator who just made it)
        foreach ($task->assignees as $assignee) {
            if ($assignee->id === $task->created_by) {
                continue;
            }
            $assignee->notify(new TaskAssignedNotification($task));
        }
    }
}
