<?php

namespace App\Events;

use App\Models\Project;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserInvited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly User    $invitedUser,
        public readonly Project $project,
        public readonly User    $invitedBy
    ) {}
}
