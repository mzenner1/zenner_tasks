<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Models\Status;
use App\Notifications\TaskStatusChangedNotification;

class SendTaskStatusChangedNotification
{
    public function handle(TaskUpdated $event): void
    {
        if (!isset($event->changes['status_id'])) {
            return;
        }

        [$oldId, $newId] = $event->changes['status_id'];

        $fromStatus = Status::find($oldId)?->name ?? 'Unknown';
        $toStatus   = Status::find($newId)?->name ?? 'Unknown';

        foreach ($event->task->assignees as $assignee) {
            if ($assignee->id === $event->actedBy) {
                continue;
            }
            $assignee->notify(new TaskStatusChangedNotification($event->task, $fromStatus, $toStatus));
        }
    }
}
