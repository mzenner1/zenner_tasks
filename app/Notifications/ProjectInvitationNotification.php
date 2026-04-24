<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class ProjectInvitationNotification extends Notification
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
        // Generate a password reset token — doubles as "set your password" for
        // new users whose accounts were just created with a null password.
        $token = Password::broker()->createToken($notifiable);

        $setPasswordUrl = route('password.reset', [
            'token' => $token,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->subject('You\'ve been invited to join Zenner Tasks')
            ->greeting('Welcome to Zenner Tasks, ' . $notifiable->name . '!')
            ->line('**' . $this->invitedBy->name . '** has invited you to collaborate on the following project:')
            ->line('### ' . $this->project->name)
            ->when(
                $this->project->description,
                fn ($mail) => $mail->line('> ' . $this->project->description)
            )
            ->line('Your account has already been created using this email address. To get started, simply set a password for your account by clicking the button below.')
            ->action('Set Your Password & Sign In', $setPasswordUrl)
            ->line('This link will expire in **60 minutes**. If it expires, you can always use the [Forgot Password](' . route('password.request') . ') link on the login page.')
            ->line('If you were not expecting this invitation, you can safely ignore this email.');
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
