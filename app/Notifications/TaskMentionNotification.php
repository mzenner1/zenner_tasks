<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskMentionNotification extends Notification
{
    public function __construct(
        public readonly Task $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You were mentioned in a task: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You were mentioned in the following task:')
            ->line('**' . $this->task->title . '**')
            ->line('Project: ' . $this->task->project->name)
            ->line('Mentioned by: ' . $this->task->creator->name)
            ->action('View Task', url(route('projects.tasks.show', [$this->task->project_id, $this->task])))
            ->line('Thank you for using Zenner Tasks!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->task->creator->name . ' mentioned you in "' . $this->task->title . '"',
            'url'     => route('projects.tasks.show', [$this->task->project_id, $this->task]),
            'task_id' => $this->task->id,
        ];
    }
}
