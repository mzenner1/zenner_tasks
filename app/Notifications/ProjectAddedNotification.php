<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectAddedNotification extends Notification
{
    public function __construct(
        public readonly Project $project,
        public readonly User    $invitedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You\'ve been added to a project on Zenner Tasks')
            ->greeting('Hi ' . $notifiable->name . '!')
            ->line('**' . $this->invitedBy->name . '** has added you as a collaborator on the following project:')
            ->line('### ' . $this->project->name)
            ->when(
                $this->project->description,
                fn ($mail) => $mail->line($this->project->description)
            )
            ->action('View Project', route('projects.show', $this->project))
            ->line('If you were not expecting this, you can safely ignore this email.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message'    => $this->invitedBy->name . ' added you to "' . $this->project->name . '"',
            'url'        => route('projects.show', $this->project),
            'project_id' => $this->project->id,
        ];
    }
}
