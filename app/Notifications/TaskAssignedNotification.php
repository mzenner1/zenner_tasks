<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            ->subject('You have been assigned a task: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have been assigned to the following task:')
            ->line('**' . $this->task->title . '**')
            ->line('Project: ' . $this->task->project->name)
            ->line('Priority: ' . ucfirst($this->task->priority))
            ->when($this->task->due_date, fn ($mail) => $mail->line('Due: ' . $this->task->due_date->format('M j, Y')))
            ->action('View Task', url(route('projects.tasks.show', [$this->task->project_id, $this->task])))
            ->line('Thank you for using Zenner Tasks!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'You were assigned to "' . $this->task->title . '" in ' . $this->task->project->name,
            'url'     => route('projects.tasks.show', [$this->task->project_id, $this->task]),
            'task_id' => $this->task->id,
        ];
    }
}
