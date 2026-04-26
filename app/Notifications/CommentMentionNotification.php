<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentMentionNotification extends Notification
{
    public function __construct(
        public readonly Comment $comment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $task = $this->comment->task;

        return (new MailMessage)
            ->subject('You were mentioned in a comment on: ' . $task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->comment->author->name . ' mentioned you in a comment on:')
            ->line('**' . $task->title . '**')
            ->line('Project: ' . $task->project->name)
            ->line(\Illuminate\Support\Str::limit(strip_tags($this->comment->body), 200))
            ->action('View Comment', url(route('projects.tasks.show', [$task->project_id, $task])))
            ->line('Thank you for using Zenner Tasks!');
    }

    public function toDatabase(object $notifiable): array
    {
        $task = $this->comment->task;

        return [
            'message'    => $this->comment->author->name . ' mentioned you in a comment on "' . $task->title . '"',
            'url'        => route('projects.tasks.show', [$task->project_id, $task]),
            'task_id'    => $task->id,
            'comment_id' => $this->comment->id,
        ];
    }
}
