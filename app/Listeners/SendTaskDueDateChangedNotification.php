<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskDueDateChangedNotification;
use Carbon\Carbon;

class SendTaskDueDateChangedNotification
{
    public function handle(TaskUpdated $event): void
    {
        if (!isset($event->changes['due_date'])) {
            return;
        }

        [$fromRaw, $toRaw] = $event->changes['due_date'];

        $fromDate = $fromRaw ? Carbon::parse($fromRaw) : null;
        $toDate   = $toRaw   ? Carbon::parse($toRaw)   : null;

        foreach ($event->task->assignees as $assignee) {
            $assignee->notify(new TaskDueDateChangedNotification($event->task, $fromDate, $toDate));
        }
    }
}
