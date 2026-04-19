<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskAssignedNotification implements ShouldQueue
{
    public function handle(TaskCreated $event): void
    {
        foreach ($event->task->assignees as $assignee) {
            // Don't notify the person who created and assigned to themselves
            if ($assignee->id !== $event->task->created_by) {
                $assignee->notify(new TaskAssignedNotification($event->task));
            }
        }
    }
}
