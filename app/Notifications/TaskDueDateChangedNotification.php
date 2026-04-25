<?php

namespace App\Notifications;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueDateChangedNotification extends Notification
{

    public function __construct(
        public readonly Task        $task,
        public readonly ?Carbon     $fromDate,
        public readonly ?Carbon     $toDate
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $from = $this->fromDate ? $this->fromDate->format('M j, Y') : 'None';
        $to   = $this->toDate   ? $this->toDate->format('M j, Y')   : 'None';

        return (new MailMessage)
            ->subject('Task due date changed: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The due date of a task you are assigned to has been updated.')
            ->line('**' . $this->task->title . '**')
            ->line('Due Date: ' . $from . ' → ' . $to)
            ->line('Project: ' . $this->task->project->name)
            ->action('View Task', url(route('projects.tasks.show', [$this->task->project_id, $this->task])))
            ->line('Thank you for using Zenner Tasks!');
    }

    public function toDatabase(object $notifiable): array
    {
        $from = $this->fromDate ? $this->fromDate->format('M j, Y') : 'None';
        $to   = $this->toDate   ? $this->toDate->format('M j, Y')   : 'None';

        return [
            'message' => '"' . $this->task->title . '" due date changed from "' . $from . '" to "' . $to . '"',
            'url'     => route('projects.tasks.show', [$this->task->project_id, $this->task]),
            'task_id' => $this->task->id,
        ];
    }
}
