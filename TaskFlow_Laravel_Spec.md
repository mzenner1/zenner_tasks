# ✅ TaskFlow — Laravel Task Management App
### Full Technical Specification & GitHub Copilot Build Guide
**Version 1.0 · April 2026**

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack](#2-technology-stack)
3. [Database Schema](#3-database-schema)
4. [Roles & Permissions](#4-roles--permissions)
5. [Route Structure](#5-route-structure)
6. [Controllers](#6-controllers)
7. [Eloquent Models](#7-eloquent-models)
8. [UI Views (Blade)](#8-ui-views-blade)
9. [Events, Listeners & Notifications](#9-events-listeners--notifications)
10. [GitHub Copilot Build Sequence](#10-github-copilot-build-sequence)
11. [Middleware](#11-middleware)
12. [Configuration & Environment](#12-configuration--environment)
13. [Future Enhancements](#13-future-enhancements)
14. [Appendix: Quick Reference](#appendix-quick-reference)

---

## 1. Project Overview

TaskFlow is a client-facing task management application inspired by DoneDone. It enables teams to manage projects, tasks, and client communication in one place — with a clear role-based permission model that separates internal admins from external clients.

### 1.1 Core Actors

| Actor | Who They Are |
|-------|-------------|
| **Super Admin** | Platform owner; full access to everything |
| **Admin** | Internal team manager; creates projects, invites users |
| **Member** | Internal team member; works tasks within assigned projects |
| **Client** | External user; invited to specific projects with limited access |

### 1.2 High-Level Features

- Project management with per-project client/member access control
- Task creation, assignment, status workflows, priorities, and due dates
- Kanban board view and list view per project
- Threaded comments with @mentions and file attachments per task
- Custom status workflows configurable per project
- Role-based permissions (Super Admin, Admin, Member, Client)
- Email notifications on task updates and comments
- Activity log / audit trail per task
- Dashboard: my tasks, recently updated, overdue items
- In-app notification bell with unread count

---

## 2. Technology Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| Backend Framework | Laravel 11 | PHP 8.3+ |
| Database | MySQL 8 / PostgreSQL 15 | Migrations are DB-agnostic |
| Authentication | Laravel Breeze + Sanctum | Session auth for web; Sanctum for future API |
| Frontend | Blade + Alpine.js + Tailwind CSS | Lightweight, no heavy JS framework needed |
| Kanban / Drag-Drop | SortableJS (CDN) | Drag-and-drop between status columns |
| File Storage | Laravel Storage (local / S3) | Configurable via `.env` |
| Email | Laravel Mail + Mailpit (dev) | SMTP in production |
| Queue | Laravel Queue (database driver) | Upgrade to Redis in production |
| Testing | Pest PHP | Feature + unit tests |
| Markdown | league/commonmark | Task descriptions and comment rendering |

> **Copilot tip:** Scaffold with `composer create-project laravel/laravel taskflow` then install Breeze with `php artisan breeze:install blade`.

---

## 3. Database Schema

All tables use `snake_case` naming. ULIDs are used as primary keys for all user-facing resources to prevent ID enumeration. Standard auto-increment IDs are fine for internal join/config tables.

### 3.1 `users`

| Column | Type | Notes |
|--------|------|-------|
| `id` | ULID string (PK) | Use `HasUlids` trait |
| `name` | string | |
| `email` | string, unique | |
| `password` | string, nullable | Null for invited-only clients before they set a password |
| `role` | enum: `super_admin`, `admin`, `member`, `client` | Global role |
| `avatar` | string, nullable | File path or URL |
| `email_verified_at` | timestamp, nullable | |
| `invited_by` | ULID FK → users, nullable | Who sent the invite |
| `last_active_at` | timestamp, nullable | |
| `remember_token` | string, nullable | |
| `created_at`, `updated_at` | timestamps | |

### 3.2 `projects`

| Column | Type | Notes |
|--------|------|-------|
| `id` | ULID string (PK) | |
| `name` | string | |
| `description` | text, nullable | |
| `slug` | string, unique | URL-safe identifier, auto-generated from name |
| `color` | string, nullable | Hex color for UI dot/badge |
| `is_archived` | boolean, default false | |
| `created_by` | ULID FK → users | |
| `created_at`, `updated_at` | timestamps | |

### 3.3 `project_members` (pivot)

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `project_id` | ULID FK → projects | |
| `user_id` | ULID FK → users | |
| `project_role` | enum: `admin`, `member`, `client` | Per-project role override |
| `created_at`, `updated_at` | timestamps | |

### 3.4 `statuses`

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `project_id` | ULID FK → projects | |
| `name` | string | e.g. "Open", "In Progress", "Done" |
| `color` | string | Hex color for badge |
| `is_default` | boolean, default false | Assigned to new tasks automatically |
| `is_closed` | boolean, default false | Treated as a "done" state for reporting |
| `sort_order` | integer | Display order in Kanban and dropdowns |

### 3.5 `tasks`

| Column | Type | Notes |
|--------|------|-------|
| `id` | ULID string (PK) | |
| `project_id` | ULID FK → projects | |
| `status_id` | bigint FK → statuses | |
| `created_by` | ULID FK → users | |
| `title` | string | |
| `description` | longtext, nullable | Supports Markdown |
| `priority` | enum: `low`, `normal`, `high`, `urgent` — default `normal` | |
| `due_date` | date, nullable | |
| `sort_order` | integer | For ordering within a Kanban status column |
| `is_archived` | boolean, default false | |
| `created_at`, `updated_at` | timestamps | |

### 3.6 `task_assignees` (pivot)

| Column | Type |
|--------|------|
| `task_id` | ULID FK → tasks |
| `user_id` | ULID FK → users |

### 3.7 `comments`

| Column | Type | Notes |
|--------|------|-------|
| `id` | ULID string (PK) | |
| `task_id` | ULID FK → tasks | |
| `user_id` | ULID FK → users | |
| `body` | text | Markdown supported |
| `parent_id` | ULID FK → comments, nullable | For threaded replies |
| `is_internal` | boolean, default false | Hidden from clients when true |
| `created_at`, `updated_at` | timestamps | |

### 3.8 `attachments`

| Column | Type | Notes |
|--------|------|-------|
| `id` | ULID string (PK) | |
| `attachable_type` | string (morph) | `Task` or `Comment` |
| `attachable_id` | ULID | |
| `user_id` | ULID FK → users | Uploader |
| `filename` | string | Original file name |
| `path` | string | Storage path |
| `mime_type` | string | |
| `size` | bigint | Size in bytes |
| `created_at`, `updated_at` | timestamps | |

### 3.9 `activity_log`

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `task_id` | ULID FK → tasks | |
| `user_id` | ULID FK → users, nullable | Null = system event |
| `event` | string | e.g. `status_changed`, `assigned`, `commented` |
| `properties` | json, nullable | `{"from": "Open", "to": "In Progress"}` |
| `created_at` | timestamp | |

### 3.10 `notifications`

Use Laravel's built-in notifications table:

```bash
php artisan notifications:table
php artisan migrate
```

---

## 4. Roles & Permissions

Permissions are checked at two levels: the **global user role** and the **per-project role**. The per-project role always takes precedence for project-scoped actions.

### 4.1 Global Roles

| Role | Who | Global Abilities |
|------|-----|-----------------|
| `super_admin` | Platform owner | Everything — all users, all projects, all settings |
| `admin` | Team manager | Create/archive projects, invite users, manage members |
| `member` | Internal team | View assigned projects, manage tasks within them |
| `client` | External user | View only projects they're added to; limited actions |

### 4.2 Per-Project Permission Matrix

| Action | Project Admin | Project Member | Project Client |
|--------|:---:|:---:|:---:|
| View project tasks | ✅ | ✅ | ✅ |
| Create tasks | ✅ | ✅ | ✅ |
| Edit any task | ✅ | ✅ | ❌ |
| Edit own tasks | ✅ | ✅ | ✅ |
| Delete tasks | ✅ | ❌ | ❌ |
| Change task status | ✅ | ✅ | ✅ (limited statuses) |
| Assign tasks to others | ✅ | ✅ | ❌ |
| Add/view public comments | ✅ | ✅ | ✅ |
| Create internal-only comments | ✅ | ✅ | ❌ |
| View internal-only comments | ✅ | ✅ | ❌ |
| Upload attachments | ✅ | ✅ | ✅ |
| Manage project members | ✅ | ❌ | ❌ |
| Edit project settings | ✅ | ❌ | ❌ |
| Archive project | ✅ (admin+ only) | ❌ | ❌ |
| Manage status workflow | ✅ | ❌ | ❌ |

### 4.3 Implementation — Laravel Policies

Create the following Policy classes with `php artisan make:policy`:

- `ProjectPolicy` — `viewAny`, `view`, `create`, `update`, `delete`, `archive`, `manageMembers`
- `TaskPolicy` — `viewAny`, `view`, `create`, `update`, `delete`, `changeStatus`, `assign`
- `CommentPolicy` — `view`, `create`, `update`, `delete`, `createInternal`
- `AttachmentPolicy` — `view`, `create`, `delete`

```php
// Example: TaskPolicy.php
public function update(User $user, Task $task): bool
{
    $role = $user->projectRole($task->project_id);

    if ($role === 'client') {
        return $task->created_by === $user->id;
    }

    return in_array($role, ['admin', 'member']);
}
```

> **Note:** Add a `projectRole(string $projectId): string` helper on the `User` model that checks `project_members` first, then falls back to the global role.

---

## 5. Route Structure

```php
// routes/web.php

// Auth (Breeze generated)
require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');

    // Project Members
    Route::prefix('projects/{project}/members')->name('projects.members.')->group(function () {
        Route::get('/', [ProjectMemberController::class, 'index'])->name('index');
        Route::post('/', [ProjectMemberController::class, 'store'])->name('store');
        Route::put('/{user}', [ProjectMemberController::class, 'update'])->name('update');
        Route::delete('/{user}', [ProjectMemberController::class, 'destroy'])->name('destroy');
    });

    // Statuses
    Route::resource('projects/{project}/statuses', StatusController::class)->except(['show']);
    Route::post('projects/{project}/statuses/reorder', [StatusController::class, 'reorder'])->name('statuses.reorder');

    // Tasks
    Route::resource('projects/{project}/tasks', TaskController::class);
    Route::post('tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move'); // Kanban drag-drop

    // Comments
    Route::post('tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Attachments
    Route::post('attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-only
    Route::prefix('admin')->name('admin.')->middleware('role:super_admin,admin')->group(function () {
        Route::resource('users', UserController::class);
    });

});
```

---

## 6. Controllers

Generate all controllers with `php artisan make:controller --resource`. Each method authorizes through its corresponding Policy.

### 6.1 `DashboardController`

- Query tasks assigned to `auth()->user()` across all their projects
- Separate queries: overdue tasks, tasks due this week, recently updated tasks
- Pass data to `dashboard.blade.php`

### 6.2 `ProjectController`

- `index()` — list projects the user belongs to via `project_members`; admins see all
- `store()` — validate, create project, seed 3 default statuses (Open / In Progress / Done), add creator as project admin in `project_members`
- `show()` — load project with tasks in list or board layout depending on `?view=board` query param
- `update()` — update name, description, color
- `archive()` / `restore()` — toggle `is_archived`

### 6.3 `TaskController`

- `index()` — tasks scoped to project with filters: `assignee`, `status`, `priority`, `due_date`, `search`. Eager-load `assignees`, `status`, comment count.
- `store()` — create task; fire `TaskCreated` event; notify assignees
- `show()` — load task with threaded `comments`, `attachments`, `activityLog`, `assignees`
- `update()` — partial update; detect changed fields with `isDirty()`, write to `activity_log`; fire `TaskUpdated` event
- `move()` — Kanban drag-drop endpoint. Accepts `{status_id, sort_order}` JSON. Updates task and returns `200 JSON`.
- `destroy()` — admin-only; hard delete if no comments, otherwise soft-delete

### 6.4 `CommentController`

- `store()` — validate `body` and `is_internal`; create comment; fire `CommentPosted` event; notify task assignees and creator
- `update()` — only the comment author or a project admin may edit
- `destroy()` — only the comment author or a project admin may delete

### 6.5 `StatusController`

- `index()` — ordered list for the project settings page
- `store()` — create status with `name`, `color`, `is_closed`
- `update()` — update name / color / is_closed
- `destroy()` — block deletion if tasks use this status; prompt to reassign them first
- `reorder()` — accept `[{id, sort_order}]` array and bulk-update `sort_order`

### 6.6 `ProjectMemberController`

- `index()` — list all members and clients for project settings
- `store()` — invite by email: if user exists, add to `project_members`; if not, create user with `null` password and send `ProjectInvitationNotification` with a signed URL
- `update()` — change a user's `project_role`
- `destroy()` — remove from `project_members`; does not delete the user account

---

## 7. Eloquent Models

### 7.1 `User`

```php
class User extends Authenticatable
{
    use HasUlids, HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'avatar', 'invited_by'];
    protected $hidden   = ['password', 'remember_token'];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('project_role')
            ->withTimestamps();
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees');
    }

    /**
     * Get this user's effective role within a specific project.
     * Checks project_members first, falls back to global role.
     */
    public function projectRole(string $projectId): string
    {
        $member = $this->projects()->where('project_id', $projectId)->first();
        return $member?->pivot->project_role ?? $this->role;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }
}
```

### 7.2 `Project`

```php
class Project extends Model
{
    use HasUlids, HasFactory;

    protected $fillable = ['name', 'description', 'slug', 'color', 'is_archived', 'created_by'];
    protected $casts    = ['is_archived' => 'boolean'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('project_role')
            ->withTimestamps();
    }

    public function tasks(): HasMany    { return $this->hasMany(Task::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class)->orderBy('sort_order');
    }

    /**
     * Scope: admins see all projects; everyone else sees only their own.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) return $query;
        return $query->whereHas('members', fn($q) => $q->where('user_id', $user->id));
    }
}
```

### 7.3 `Task`

```php
class Task extends Model
{
    use HasUlids, HasFactory;

    protected $fillable = ['project_id', 'status_id', 'created_by', 'title', 'description',
                           'priority', 'due_date', 'sort_order', 'is_archived'];
    protected $casts    = ['due_date' => 'date', 'is_archived' => 'boolean'];

    public function project(): BelongsTo   { return $this->belongsTo(Project::class); }
    public function status(): BelongsTo    { return $this->belongsTo(Status::class); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function activityLog(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereHas('status', fn($q) => $q->where('is_closed', false));
    }
}
```

### 7.4 `Comment`

```php
class Comment extends Model
{
    use HasUlids, HasFactory;

    protected $fillable = ['task_id', 'user_id', 'body', 'parent_id', 'is_internal'];
    protected $casts    = ['is_internal' => 'boolean'];

    public function task(): BelongsTo    { return $this->belongsTo(Task::class); }
    public function author(): BelongsTo  { return $this->belongsTo(User::class, 'user_id'); }
    public function parent(): BelongsTo  { return $this->belongsTo(Comment::class, 'parent_id'); }
    public function replies(): HasMany   { return $this->hasMany(Comment::class, 'parent_id'); }
    public function attachments(): MorphMany { return $this->morphMany(Attachment::class, 'attachable'); }

    /** Scope: hide internal comments from clients */
    public function scopeVisibleTo(Builder $query, User $user, string $projectId): Builder
    {
        if ($user->projectRole($projectId) === 'client') {
            return $query->where('is_internal', false);
        }
        return $query;
    }
}
```

---

## 8. UI Views (Blade)

### 8.1 View File Structure

```
resources/views/
├── layouts/
│   ├── app.blade.php           # Main layout with nav sidebar
│   └── guest.blade.php         # Auth pages (Breeze generated)
├── dashboard.blade.php         # My tasks overview
├── projects/
│   ├── index.blade.php         # Project list
│   ├── show.blade.php          # Tasks — list view
│   ├── board.blade.php         # Tasks — Kanban board view
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── members.blade.php       # Project member management
├── tasks/
│   ├── show.blade.php          # Full task detail with comments
│   └── _partials/
│       ├── task-card.blade.php
│       ├── comment.blade.php
│       └── activity-item.blade.php
├── statuses/
│   └── index.blade.php         # Project workflow settings
└── admin/
    └── users/
        ├── index.blade.php
        └── show.blade.php
```

### 8.2 Layout — Left Sidebar (`layouts/app.blade.php`)

The sidebar should contain:
- App logo / name at the top
- **Dashboard** link (home icon)
- **Projects** section — list of the user's projects with colored dot indicators
- **New Project** link — visible only to admins via `@can('create', App\Models\Project::class)`
- Bottom: notification bell with unread badge, user avatar with dropdown (Profile, Logout)

### 8.3 Project List View (`projects/show.blade.php`)

Filterable table/list of tasks. Columns: Title, Status badge, Assignee avatars, Priority, Due Date. Filters bar at the top using Alpine.js dropdowns for status, assignee, priority.

### 8.4 Kanban Board View (`projects/board.blade.php`)

- One column per status, ordered by `sort_order`
- Each column contains draggable task cards using SortableJS
- On drop, fire a `fetch()` POST to `tasks/{task}/move`

```html
{{-- Include SortableJS from CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
document.querySelectorAll('.kanban-column').forEach(column => {
    Sortable.create(column, {
        group: 'tasks',
        animation: 150,
        onEnd: function(evt) {
            fetch(`/tasks/${evt.item.dataset.taskId}/move`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    status_id: evt.to.dataset.statusId,
                    sort_order: evt.newIndex
                })
            });
        }
    });
});
</script>
```

### 8.5 Task Detail View (`tasks/show.blade.php`)

The task detail page is the core UI. It should display:

- Task title (inline-editable for users with `update` permission)
- Description rendered via `league/commonmark`
- Status dropdown — changes status on `<select>` change via a small form POST
- Priority badge with color coding
- Assignee multi-select (project members only)
- Due date picker
- Attachments list with upload dropzone (`<input type="file" multiple>`)
- **Comments thread** — chronological; each shows avatar, name, timestamp, body, and edit/delete actions for the author
- Internal comments shown with a distinct yellow/amber background, hidden from clients:

```blade
@if (!$comment->is_internal || $user->projectRole($task->project_id) !== 'client')
    @include('tasks._partials.comment', ['comment' => $comment])
@endif
```

- Activity log — collapsed by default, toggle with Alpine.js `x-show`

---

## 9. Events, Listeners & Notifications

### 9.1 Events

| Event Class | Fired When |
|-------------|-----------|
| `TaskCreated` | A new task is created |
| `TaskUpdated` | A task field changes (status, assignee, priority, due date) |
| `CommentPosted` | A comment is added to a task |
| `UserInvited` | A user is invited to a project |

### 9.2 Notification Classes

Create these with `php artisan make:notification`. Each implements `toMail()` and `toDatabase()`.

| Notification | Sent To | Trigger |
|-------------|---------|---------|
| `TaskAssignedNotification` | Newly assigned user | Task assigned |
| `TaskStatusChangedNotification` | Task assignees | Status changes |
| `NewCommentNotification` | Task assignees + creator | New comment posted |
| `ProjectInvitationNotification` | Invited user | Invited to project |

### 9.3 Activity Logging on Task Update

```php
// In TaskController::update() — run BEFORE $task->save()
$changes = [];

if ($task->isDirty('status_id')) {
    $changes[] = [
        'event'      => 'status_changed',
        'properties' => ['from' => $task->getOriginal('status_id'), 'to' => $task->status_id],
    ];
}

if ($task->isDirty('due_date')) {
    $changes[] = [
        'event'      => 'due_date_changed',
        'properties' => ['from' => $task->getOriginal('due_date'), 'to' => $task->due_date],
    ];
}

$task->save();

foreach ($changes as $change) {
    ActivityLog::create([
        ...$change,
        'task_id' => $task->id,
        'user_id' => auth()->id(),
    ]);
}
```

---

## 10. GitHub Copilot Build Sequence

> Use these prompts in **order** inside Copilot Chat in VS Code (`Ctrl+Shift+I`). Open the relevant file or folder before each prompt so Copilot has the right context. Use `@workspace` to give Copilot visibility into your full project.

---

### Step 1 — Initial Scaffolding

```
Create a new Laravel 11 application called taskflow.
Install Laravel Breeze with the Blade stack.
Install league/commonmark for Markdown rendering.
Configure the .env for a MySQL database called taskflow_db.
Update the User model to use the HasUlids trait and add a
role column as an enum (super_admin, admin, member, client)
with a default of 'member'.
```

---

### Step 2 — Migrations

```
Generate Laravel migration files for the following tables
as described in the spec open in the editor:
projects, project_members, statuses, tasks, task_assignees,
comments, attachments, activity_log.

Use ULID primary keys on: projects, tasks, comments, attachments.
Use bigint auto-increment PKs on: statuses, activity_log, project_members.
Add appropriate foreign key constraints with cascading deletes
where the parent record deletion should remove children (e.g. project → tasks).
```

---

### Step 3 — Models & Relationships

```
Create Eloquent models for Project, Task, Status, Comment,
Attachment, and ActivityLog with all relationships from the spec:

- Project: hasMany Tasks, Statuses; belongsToMany Users via project_members; scopeForUser
- Task: belongsTo Project, Status, Creator; belongsToMany Users via task_assignees;
  hasMany Comments; morphMany Attachments; hasMany ActivityLog; scopeOverdue
- Comment: belongsTo Task, Author, Parent; hasMany Replies; morphMany Attachments; scopeVisibleTo
- Attachment: morphTo attachable; belongsTo User

Add the projectRole(string $projectId): string helper and isAdmin(): bool to the User model.
```

---

### Step 4 — Policies

```
Generate Laravel Policy classes for Project, Task, Comment, and Attachment.
Implement the permission matrix from the spec:

- Clients can only edit/delete their own tasks and comments
- Clients cannot see or create internal comments
- Members cannot delete tasks
- Project admins have full control within their project
- Super admins bypass all policy checks

Use $user->projectRole($projectId) in each policy method.
Register all policies in AuthServiceProvider.
```

---

### Step 5 — Controllers

```
Generate resource controllers for ProjectController, TaskController,
StatusController, CommentController, ProjectMemberController,
and AttachmentController using the route structure in routes/web.php.

Each controller method should:
1. Authorize using $this->authorize() with the appropriate Policy
2. Validate input using Form Request classes
3. Execute the business logic described in the spec
4. Redirect with flash messages for web requests, or return JSON for AJAX endpoints

TaskController::move() accepts JSON body {status_id, sort_order} and returns JSON 200.
ProjectMemberController::store() should handle both existing users and new invitations.
```

---

### Step 6 — Views

```
Generate Blade views for:
1. layouts/app.blade.php — left sidebar with project list, notification bell, user dropdown
2. projects/index.blade.php — project cards with color dots and archived state
3. projects/show.blade.php — filterable task list with status badge, priority, assignee avatars, due date
4. projects/board.blade.php — Kanban board with SortableJS drag-drop between status columns
5. tasks/show.blade.php — full task detail: title, description (Markdown), status dropdown,
   assignee picker, due date, attachments, threaded comments, activity log

Use Tailwind CSS throughout. Use Alpine.js for dropdowns, modals, and toggles.
Hide internal comments from users whose projectRole is 'client' using @if directives.
Use @can directives to conditionally show edit/delete/assign buttons.
```

---

### Step 7 — Events, Listeners & Notifications

```
Generate the following Laravel classes:

Events: TaskCreated, TaskUpdated, CommentPosted, UserInvited

Notifications (each with toMail() and toDatabase()):
- TaskAssignedNotification
- TaskStatusChangedNotification
- NewCommentNotification
- ProjectInvitationNotification (include a signed URL valid for 72 hours)

Create Listeners that dispatch the appropriate notifications.
Register everything in EventServiceProvider.
Queue all notifications using the 'database' queue driver.
```

---

### Step 8 — Seeders & Tests

```
Create a DatabaseSeeder that generates realistic test data:
- 1 super admin, 2 admins, 5 members, 3 client users
- 3 projects, each with 3–5 custom statuses and 10–20 tasks
- Tasks with random assignees, statuses, priorities, and due dates
- Some tasks with 2–5 comments (mix of public and internal)

Then generate Pest PHP feature tests for:
1. A client cannot view a project they are not a member of
2. A client cannot edit another user's task
3. A client cannot see comments where is_internal = true
4. An admin can change any task's status in their project
5. A member cannot delete a task
6. Dragging a task (POST tasks/{task}/move) updates status_id and sort_order
```

---

## 11. Middleware

### 11.1 `RoleMiddleware`

```php
// app/Http/Middleware/RoleMiddleware.php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!in_array(auth()->user()->role, $roles)) {
        abort(403, 'Insufficient permissions.');
    }

    return $next($request);
}

// Register in bootstrap/app.php:
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['role' => RoleMiddleware::class]);
})

// Usage in routes:
Route::middleware('role:super_admin,admin')->group(...)
```

### 11.2 `EnsureProjectMember`

```php
// app/Http/Middleware/EnsureProjectMember.php
// Attach to all project-scoped routes.
// Verifies the authenticated user is in project_members for the
// {project} route parameter (super_admins bypass this check).

public function handle(Request $request, Closure $next): Response
{
    $project = $request->route('project');
    $user    = auth()->user();

    if ($user->role === 'super_admin') {
        return $next($request);
    }

    if (!$project->members->contains($user)) {
        abort(403, 'You are not a member of this project.');
    }

    return $next($request);
}
```

---

## 12. Configuration & Environment

### 12.1 Required `.env` Variables

```env
APP_NAME="TaskFlow"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_DATABASE=taskflow_db
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM_ADDRESS="no-reply@taskflow.test"
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=local
# Change to 's3' in production and fill in keys below:
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
```

### 12.2 Post-Setup Artisan Commands

Run these in order after cloning / initial setup:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan queue:table && php artisan migrate   # if not already run
npm install && npm run dev
php artisan queue:work                           # separate terminal
```

---

## 13. Future Enhancements

| Feature | Notes |
|---------|-------|
| REST API | Add `api.php` routes with Sanctum token auth for mobile/third-party access |
| Real-time updates | Add Laravel Reverb (WebSockets) for live task/comment updates without page refresh |
| Email-to-task | Inbound mail via Mailgun/Postmark webhook automatically creates tasks |
| Subtasks | Add `parent_task_id` FK on `tasks` table for hierarchical task breakdown |
| Time tracking | Add `time_entries` table: `task_id`, `user_id`, `started_at`, `stopped_at` |
| Recurring tasks | Add `recurrence_rule` (RRULE string) to tasks; scheduled command creates next occurrence |
| Global search | Laravel Scout + Meilisearch for full-text search across tasks and comments |
| Reporting | Per-project completion charts; CSV export |
| Two-factor auth | Laravel Fortify 2FA for admin accounts |
| Webhooks | Outbound webhooks on task events for Slack / Zapier integration |

---

## Appendix: Quick Reference

### Key Artisan Commands

```bash
# Models, migrations, factories
php artisan make:model Project -mf
php artisan make:model Task -mf
php artisan make:model Status -m
php artisan make:model Comment -mf
php artisan make:model Attachment -m
php artisan make:model ActivityLog -m

# Controllers
php artisan make:controller ProjectController --resource
php artisan make:controller TaskController --resource
php artisan make:controller StatusController --resource
php artisan make:controller CommentController
php artisan make:controller ProjectMemberController
php artisan make:controller AttachmentController
php artisan make:controller DashboardController --invokable

# Policies
php artisan make:policy ProjectPolicy --model=Project
php artisan make:policy TaskPolicy --model=Task
php artisan make:policy CommentPolicy --model=Comment
php artisan make:policy AttachmentPolicy --model=Attachment

# Events & Notifications
php artisan make:event TaskCreated
php artisan make:event TaskUpdated
php artisan make:event CommentPosted
php artisan make:notification TaskAssignedNotification
php artisan make:notification TaskStatusChangedNotification
php artisan make:notification NewCommentNotification
php artisan make:notification ProjectInvitationNotification

# Middleware
php artisan make:middleware RoleMiddleware
php artisan make:middleware EnsureProjectMember

# Queue & notifications tables
php artisan queue:table
php artisan notifications:table
php artisan migrate

# Run tests
php artisan test
php artisan test --filter TaskPolicyTest
```

### Recommended Composer Packages

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade

composer require league/commonmark          # Markdown rendering
composer require barryvdh/laravel-debugbar --dev  # Query inspector (dev only)

# Optional but highly recommended:
composer require spatie/laravel-activitylog  # Replaces manual ActivityLog model
composer require spatie/laravel-medialibrary # Advanced file attachment management
```

---

*TaskFlow Laravel Build Spec · v1.0 · April 2026*
