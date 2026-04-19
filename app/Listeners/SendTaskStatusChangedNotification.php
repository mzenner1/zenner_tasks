<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskStatusChangedNotification;
use App\Models\Status;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskStatusChangedNotification implements ShouldQueue
{
    public function handle(TaskUpdated $event): void
    {
        // Only fire if status actually changed
        if (!isset($event->changes['status_id'])) {
            return;
        }

        [$oldId, $newId] = $event->changes['status_id'];

        $fromStatus = Status::find($oldId)?->name ?? 'Unknown';
        $toStatus   = Status::find($newId)?->name ?? 'Unknown';

        foreach ($event->task->assignees as $assignee) {
            $assignee->notify(new TaskStatusChangedNotification($event->task, $fromStatus, $toStatus));
        }
    }
}
