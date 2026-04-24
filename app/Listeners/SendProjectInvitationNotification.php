<?php

namespace App\Listeners;

use App\Events\UserInvited;
use App\Notifications\ProjectInvitationNotification;

class SendProjectInvitationNotification
{
    public function handle(UserInvited $event): void
    {
        $event->invitedUser->notify(
            new ProjectInvitationNotification($event->project, $event->invitedBy)
        );
    }
}
