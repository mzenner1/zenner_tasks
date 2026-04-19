<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Task   $task,
        public readonly string $fromStatus,
        public readonly string $toStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task status changed: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The status of a task you are assigned to has changed.')
            ->line('**' . $this->task->title . '**')
            ->line('Status: ' . $this->fromStatus . ' → ' . $this->toStatus)
            ->line('Project: ' . $this->task->project->name)
            ->action('View Task', url(route('projects.tasks.show', [$this->task->project_id, $this->task])))
            ->line('Thank you for using Zenner Tasks!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => '"' . $this->task->title . '" moved from "' . $this->fromStatus . '" to "' . $this->toStatus . '"',
            'url'     => route('projects.tasks.show', [$this->task->project_id, $this->task]),
            'task_id' => $this->task->id,
        ];
    }
}
