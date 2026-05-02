<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskDueDateChangedNotification;
use Carbon\Carbon;

class SendTaskDueDateChangedNotification
{
    public function handle(TaskUpdated $event): void
    {
        if ($event->task->project->is_archived) {
            return;
        }

        if (!isset($event->changes['due_date'])) {
            return;
        }

        [$fromRaw, $toRaw] = $event->changes['due_date'];

        $fromDate = $fromRaw ? Carbon::parse($fromRaw) : null;
        $toDate   = $toRaw   ? Carbon::parse($toRaw)   : null;

        $event->task->load('watchers');

        foreach ($event->task->watchers as $watcher) {
            if ($watcher->id === $event->actedBy) {
                continue;
            }
            $watcher->notify(new TaskDueDateChangedNotification($event->task, $fromDate, $toDate));
        }
    }
}
