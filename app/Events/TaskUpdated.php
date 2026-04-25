<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Task   $task,
        public readonly array  $changes = [],   // e.g. ['status_id' => [old, new]]
        public readonly ?string $actedBy = null  // auth()->id() of whoever made the change
    ) {}
}
