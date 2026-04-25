<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskPriorityChangedNotification extends Notification
{

    public function __construct(
        public readonly Task   $task,
        public readonly string $fromPriority,
        public readonly string $toPriority
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task priority changed: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The priority of a task you are assigned to has been updated.')
            ->line('**' . $this->task->title . '**')
            ->line('Priority: ' . ucfirst($this->fromPriority) . ' → ' . ucfirst($this->toPriority))
            ->line('Project: ' . $this->task->project->name)
            ->action('View Task', url(route('projects.tasks.show', [$this->task->project_id, $this->task])))
            ->line('Thank you for using Zenner Tasks!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => '"' . $this->task->title . '" priority changed from "' . ucfirst($this->fromPriority) . '" to "' . ucfirst($this->toPriority) . '"',
            'url'     => route('projects.tasks.show', [$this->task->project_id, $this->task]),
            'task_id' => $this->task->id,
        ];
    }
}
