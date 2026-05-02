<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskWatcherController;
use Illuminate\Support\Facades\Route;

// PWA offline fallback
Route::get('/offline', fn() => view('vendor.laravelpwa.offline'))->name('offline');

// Auth (Breeze generated)
require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');

    // Project-scoped routes — all require project membership
    Route::middleware('project.member')->group(function () {

        // Project Members
        Route::prefix('projects/{project}/members')->name('projects.members.')->group(function () {
            Route::get('/',                        [ProjectMemberController::class, 'index'])->name('index');
            Route::post('/',                       [ProjectMemberController::class, 'store'])->name('store');
            Route::delete('/{user}',               [ProjectMemberController::class, 'destroy'])->name('destroy');
            Route::get('/search',                  [ProjectMemberController::class, 'search'])->name('search');
            Route::get('/{user}/check-tasks',      [ProjectMemberController::class, 'checkTasks'])->name('check-tasks');
        });

        // Statuses
        Route::resource('projects/{project}/statuses', StatusController::class)
            ->except(['show'])
            ->names([
                'index'   => 'projects.statuses.index',
                'create'  => 'projects.statuses.create',
                'store'   => 'projects.statuses.store',
                'edit'    => 'projects.statuses.edit',
                'update'  => 'projects.statuses.update',
                'destroy' => 'projects.statuses.destroy',
            ]);
        Route::post('projects/{project}/statuses/reorder', [StatusController::class, 'reorder'])
            ->name('projects.statuses.reorder');

        // Tasks
        Route::resource('projects/{project}/tasks', TaskController::class)
            ->names([
                'index'   => 'projects.tasks.index',
                'create'  => 'projects.tasks.create',
                'store'   => 'projects.tasks.store',
                'show'    => 'projects.tasks.show',
                'edit'    => 'projects.tasks.edit',
                'update'  => 'projects.tasks.update',
                'destroy' => 'projects.tasks.destroy',
            ]);
    });

    // Kanban drag-drop move (task only — no project param needed, task carries project context)
    Route::post('tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');

    // Copy / Move task to another project
    Route::post('tasks/{task}/copy-to-project', [TaskController::class, 'copyToProject'])->name('tasks.copyToProject');
    Route::post('tasks/{task}/move-to-project', [TaskController::class, 'moveToProject'])->name('tasks.moveToProject');

    // Watch / Unwatch
    Route::post('tasks/{task}/watch',   [TaskWatcherController::class, 'store'])->name('tasks.watch');
    Route::delete('tasks/{task}/watch', [TaskWatcherController::class, 'destroy'])->name('tasks.unwatch');

    // Comments
    Route::post('tasks/{task}/comments',    [CommentController::class, 'store'])->name('comments.store');
    Route::put('comments/{comment}',        [CommentController::class, 'update'])->name('comments.update');
    Route::delete('comments/{comment}',     [CommentController::class, 'destroy'])->name('comments.destroy');

    // Attachments
    Route::post('attachments',                        [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('attachments/{attachment}',          [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::post('attachments/image-upload',            [AttachmentController::class, 'imageUpload'])->name('attachments.image-upload');
    Route::get('attachments/{attachment}/download',    [AttachmentController::class, 'download'])->name('attachments.download');

    // Notifications
    Route::get('notifications',                     [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read',          [NotificationController::class, 'markRead'])->name('notifications.read');

    // Profile (Breeze generated)
    Route::get('profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-only
    Route::prefix('admin')->name('admin.')->middleware('role:super_admin,admin')->group(function () {
        Route::post('users/invite', [UserController::class, 'invite'])->name('users.invite');
        Route::resource('users', UserController::class)->except(['create', 'store']);

        // Archived projects
        Route::get('projects/archived',              [AdminProjectController::class, 'archived'])->name('projects.archived');
        Route::post('projects/{project}/unarchive',  [AdminProjectController::class, 'unarchive'])->name('projects.unarchive');
    });

});
