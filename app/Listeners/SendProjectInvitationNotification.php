<?php

namespace App\Listeners;

use App\Events\UserInvited;
use App\Notifications\ProjectInvitationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendProjectInvitationNotification implements ShouldQueue
{
    public function handle(UserInvited $event): void
    {
        $event->invitedUser->notify(
            new ProjectInvitationNotification($event->project, $event->invitedBy)
        );
    }
}
