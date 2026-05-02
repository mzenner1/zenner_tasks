<?php

namespace App\Listeners;

use App\Events\CommentPosted;
use App\Notifications\NewCommentNotification;

class SendNewCommentNotification
{
    public function handle(CommentPosted $event): void
    {
        $comment = $event->comment;
        $task    = $comment->task;

        if ($task->project->is_archived) {
            return;
        }

        $task->load('watchers');

        $recipients = $task->watchers
            ->unique('id')
            ->reject(fn ($user) => $user->id === $comment->user_id);

        foreach ($recipients as $recipient) {
            if ($comment->is_internal && $recipient->projectRole($task->project_id) === 'client') {
                continue;
            }
            $recipient->notify(new NewCommentNotification($comment));
        }
    }
}
