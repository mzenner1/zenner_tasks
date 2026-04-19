<?php

namespace App\Providers;

use App\Events\CommentPosted;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Events\UserInvited;
use App\Listeners\SendNewCommentNotification;
use App\Listeners\SendProjectInvitationNotification;
use App\Listeners\SendTaskAssignedNotification;
use App\Listeners\SendTaskStatusChangedNotification;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Policies\AttachmentPolicy;
use App\Policies\CommentPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Policies ──────────────────────────────────────────────────────────
        Gate::policy(Project::class,    ProjectPolicy::class);
        Gate::policy(Task::class,       TaskPolicy::class);
        Gate::policy(Comment::class,    CommentPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);

        // Super admins bypass every Gate check globally
        Gate::before(function ($user, $ability) {
            if ($user->role === 'super_admin') {
                return true;
            }
        });

        // ── Events & Listeners ────────────────────────────────────────────────
        Event::listen(TaskCreated::class,   SendTaskAssignedNotification::class);
        Event::listen(TaskUpdated::class,   SendTaskStatusChangedNotification::class);
        Event::listen(CommentPosted::class, SendNewCommentNotification::class);
        Event::listen(UserInvited::class,   SendProjectInvitationNotification::class);
    }
}
