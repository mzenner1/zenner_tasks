<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
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
            ->subject('New comment on: ' . $task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->comment->author->name . ' commented on a task you are following.')
            ->line('**' . $task->title . '**')
            ->line(\Illuminate\Support\Str::limit($this->comment->body, 200))
            ->action('View Task', url(route('projects.tasks.show', [$task->project_id, $task])))
            ->line('Thank you for using Zenner Tasks!');
    }

    public function toDatabase(object $notifiable): array
    {
        $task = $this->comment->task;

        return [
            'message'    => $this->comment->author->name . ' commented on "' . $task->title . '"',
            'url'        => route('projects.tasks.show', [$task->project_id, $task]),
            'task_id'    => $task->id,
            'comment_id' => $this->comment->id,
        ];
    }
}
