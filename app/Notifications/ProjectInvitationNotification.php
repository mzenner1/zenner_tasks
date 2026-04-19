<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ProjectInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
        // Signed URL valid for 72 hours — routes to the register page with email pre-filled
        $signedUrl = URL::temporarySignedRoute(
            'register',
            now()->addHours(72),
            ['email' => $notifiable->email, 'project' => $this->project->id]
        );

        return (new MailMessage)
            ->subject($this->invitedBy->name . ' invited you to "' . $this->project->name . '"')
            ->greeting('Hello!')
            ->line($this->invitedBy->name . ' has invited you to collaborate on the project:')
            ->line('**' . $this->project->name . '**')
            ->when($this->project->description, fn ($mail) => $mail->line($this->project->description))
            ->action('Accept Invitation', $signedUrl)
            ->line('This invitation link will expire in 72 hours.')
            ->line('If you were not expecting this invitation, no action is required.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message'    => $this->invitedBy->name . ' invited you to "' . $this->project->name . '"',
            'url'        => route('projects.show', $this->project),
            'project_id' => $this->project->id,
        ];
    }
}
