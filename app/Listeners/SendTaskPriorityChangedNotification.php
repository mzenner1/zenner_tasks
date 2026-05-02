<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskPriorityChangedNotification;

class SendTaskPriorityChangedNotification
{
    public function handle(TaskUpdated $event): void
    {
        if ($event->task->project->is_archived) {
            return;
        }

        if (!isset($event->changes['priority'])) {
            return;
        }

        [$fromPriority, $toPriority] = $event->changes['priority'];

        $event->task->load('watchers');

        foreach ($event->task->watchers as $watcher) {
            if ($watcher->id === $event->actedBy) {
                continue;
            }
            $watcher->notify(new TaskPriorityChangedNotification($event->task, $fromPriority, $toPriority));
        }
    }
}
