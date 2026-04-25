<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminInviteNotification extends Notification
{
    public function __construct(
        public readonly User   $invitedBy,
        public readonly string $role,
        public readonly string $setPasswordUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleLabel = ucfirst(str_replace('_', ' ', $this->role));

        return (new MailMessage)
            ->subject('You\'ve been invited to Zenner Tasks')
            ->greeting('Welcome to Zenner Tasks, ' . $notifiable->name . '!')
            ->line('**' . $this->invitedBy->name . '** has created an account for you on Zenner Tasks as a **' . $roleLabel . '**.')
            ->line('To get started, click the button below to set your password and sign in.')
            ->action('Set Your Password & Sign In', $this->setPasswordUrl)
            ->line('This link will expire in **60 minutes**. If it expires, use the [Forgot Password](' . route('password.request') . ') link on the login page.')
            ->line('If you were not expecting this invitation, you can safely ignore this email.');
    }
}
