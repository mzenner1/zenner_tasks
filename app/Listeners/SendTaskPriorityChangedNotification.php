<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskPriorityChangedNotification;

class SendTaskPriorityChangedNotification
{
    public function handle(TaskUpdated $event): void
    {
        if (!isset($event->changes['priority'])) {
            return;
        }

        [$fromPriority, $toPriority] = $event->changes['priority'];

        foreach ($event->task->assignees as $assignee) {
            if ($assignee->id === $event->actedBy) {
                continue;
            }
            $assignee->notify(new TaskPriorityChangedNotification($event->task, $fromPriority, $toPriority));
        }
    }
}
