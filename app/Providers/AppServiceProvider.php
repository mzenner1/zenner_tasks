<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Policies\AttachmentPolicy;
use App\Policies\CommentPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
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
        // Listeners are auto-discovered from app/Listeners — no manual
        // Event::listen() calls needed.
    }
}
