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

        $event->task->load('watchers');

        foreach ($event->task->watchers as $watcher) {
            if ($watcher->id === $event->actedBy) {
                continue;
            }
            $watcher->notify(new TaskStatusChangedNotification($event->task, $fromStatus, $toStatus));
        }
    }
}
