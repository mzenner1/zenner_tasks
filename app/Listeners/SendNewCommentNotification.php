<?php

namespace App\Listeners;

use App\Events\CommentPosted;
use App\Notifications\NewCommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewCommentNotification implements ShouldQueue
{
    public function handle(CommentPosted $event): void
    {
        $comment = $event->comment;
        $task    = $comment->task;

        // Collect recipients: all assignees + task creator, excluding the comment author
        $recipients = $task->assignees
            ->push($task->creator)
            ->unique('id')
            ->reject(fn ($user) => $user->id === $comment->user_id);

        // Don't notify about internal comments if recipient is a client
        foreach ($recipients as $recipient) {
            if ($comment->is_internal && $recipient->projectRole($task->project_id) === 'client') {
                continue;
            }
            $recipient->notify(new NewCommentNotification($comment));
        }
    }
}
