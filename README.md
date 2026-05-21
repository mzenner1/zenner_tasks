# Zenner Tasks

A client-facing task management application built with Laravel 13. Inspired by DoneDone, it enables teams to manage projects, tasks, and client communication in one place with a clear role-based permission model that separates internal staff from external clients.

---

## Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Roles & Permissions](#roles--permissions)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Running the App](#running-the-app)
- [Running Tests](#running-tests)
- [Project Structure](#project-structure)
- [Database Schema Overview](#database-schema-overview)
- [Notifications](#notifications)

---

## Features

- **Project management** — create, archive, and restore projects with per-project color coding and slugs
- **Kanban board & list view** — drag-and-drop tasks between status columns via SortableJS
- **Custom status workflows** — configurable statuses (name, color, order, open/closed state) per project
- **Task management** — ULID-keyed tasks with priority (`low` / `normal` / `high` / `urgent`), due dates, markdown descriptions, and sequential task numbers
- **Task assignments** — assign multiple team members per task; watch tasks for activity alerts
- **Threaded comments** — nested replies with Markdown support, internal-only visibility flag (hidden from clients), and @mention notifications
- **File attachments** — polymorphic attachments on tasks and comments; local or S3 storage
- **Tags** — label tasks with reusable tags per project
- **Activity log** — full audit trail of status changes, assignments, comments, and more per task
- **Role-based access control** — four global roles enforced by Laravel Policies, with per-project role overrides
- **Email notifications** — queued mail for task assignments, status/priority/due-date changes, new comments, @mentions, and project invitations
- **In-app notification bell** — unread count with mark-all-read support
- **Dashboard** — my tasks, recently updated, and overdue items at a glance
- **Admin panel** — user management and impersonation for Super Admins
- **PWA support** — installable as a standalone app via `laravel-pwa`
- **Google OAuth** — social login via Laravel Socialite

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.3+ |
| Auth | Laravel Breeze (session) + Socialite (Google OAuth) |
| Frontend | Blade, Alpine.js, Tailwind CSS v3 |
| Drag & Drop | SortableJS (CDN) |
| Markdown | league/commonmark, EasyMDE editor |
| File Storage | Laravel Storage (local / S3) |
| Email | Laravel Mail — `log` driver in dev, SMTP in production |
| Queue | Laravel Queue — `database` driver (upgrade to Redis for production) |
| Testing | Pest PHP 4 |
| PWA | silviolleite/laravelpwa |
| Build | Vite 8 + Tailwind CSS Vite plugin |

---

## Roles & Permissions

### Global Roles

| Role | Description |
|---|---|
| `super_admin` | Full platform access; can impersonate any user |
| `admin` | Create/archive projects, invite users, manage members |
| `member` | Work tasks within assigned projects |
| `client` | External user; view-only access to projects they're invited to |

### Per-Project Permission Matrix

| Action | Project Admin | Project Member | Project Client |
|---|:---:|:---:|:---:|
| View tasks | ✅ | ✅ | ✅ |
| Create tasks | ✅ | ✅ | ✅ |
| Edit any task | ✅ | ✅ | ❌ |
| Edit own tasks | ✅ | ✅ | ✅ |
| Delete tasks | ✅ | ❌ | ❌ |
| Change task status | ✅ | ✅ | ✅ (limited) |
| Assign tasks | ✅ | ✅ | ❌ |
| Public comments | ✅ | ✅ | ✅ |
| Internal comments | ✅ | ✅ | ❌ |
| Upload attachments | ✅ | ✅ | ✅ |
| Manage members | ✅ | ❌ | ❌ |
| Edit project settings | ✅ | ❌ | ❌ |
| Archive project | ✅ | ❌ | ❌ |
| Manage status workflow | ✅ | ❌ | ❌ |

---

## Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+ & npm
- SQLite (default) **or** MySQL 8 / PostgreSQL 15
- (Optional) A mail server or Mailpit for local email testing

---

## Installation

```bash
# 1. Clone the repository
git clone <repo-url> zenner_tasks
cd zenner_tasks

# 2. One-command setup (install deps, copy .env, generate key, migrate, build assets)
composer run setup
```

The `setup` script runs:
1. `composer install`
2. Copies `.env.example` → `.env` (if `.env` doesn't exist)
3. `php artisan key:generate`
4. `php artisan migrate --force`
5. `npm install`
6. `npm run build`

---

## Environment Configuration

Copy `.env.example` and adjust the following key values:

```dotenv
APP_NAME="Zenner Tasks"
APP_URL=http://localhost:8000

# Database — SQLite is the default; switch to mysql/pgsql for production
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=zenner_tasks
# DB_USERNAME=root
# DB_PASSWORD=

# Queue — database driver is the default
QUEUE_CONNECTION=database

# Mail — use 'log' locally or configure SMTP for production
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="Zenner Tasks"

# Google OAuth (optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# File storage — use 's3' for production
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
```

After editing `.env`, create the storage symlink:

```bash
php artisan storage:link
```

---

## Running the App

The `dev` script starts all required processes concurrently:

```bash
composer run dev
```

This runs:
| Process | Command |
|---|---|
| Web server | `php artisan serve` |
| Queue worker | `php artisan queue:listen --tries=1 --timeout=0` |
| Log tail | `php artisan pail --timeout=0` |
| Vite HMR | `npm run dev` |

The app will be available at **http://localhost:8000**.

---

## Running Tests

```bash
composer run test
# or directly
php artisan test
# or with Pest
./vendor/bin/pest
```

Test coverage includes:
- `KanbanMoveTest` — task drag-and-drop between status columns
- `TaskPolicyTest` — role-based task access control
- `ProfileTest` — profile update flows
- `Auth/` — registration, login, password reset

---

## Project Structure

```
app/
├── Events/             # TaskCreated, TaskUpdated, CommentPosted, UserInvited
├── Http/
│   ├── Controllers/    # Resource controllers + Admin sub-controllers
│   ├── Middleware/
│   └── Requests/       # Form request validation
├── Listeners/          # Queue-dispatched notification listeners
├── Models/             # Eloquent models (ULID primary keys on user-facing resources)
├── Notifications/      # Mailable notification classes
├── Policies/           # Authorization policies
├── Providers/
├── Support/
└── View/               # View composers / components
database/
├── factories/
├── migrations/
└── seeders/
resources/
├── css/
├── js/
└── views/              # Blade templates
routes/
├── web.php
└── auth.php
tests/
├── Feature/
└── Unit/
```

---

## Database Schema Overview

All user-facing resources use **ULID** primary keys to prevent ID enumeration.

| Table | Key Type | Purpose |
|---|---|---|
| `users` | ULID | Accounts with global role |
| `projects` | ULID | Top-level grouping of tasks |
| `project_members` | bigint | Pivot: users ↔ projects |
| `statuses` | bigint | Per-project status workflow |
| `tasks` | ULID | Work items with priority, due date, and sequential number |
| `task_assignees` | — | Pivot: tasks ↔ users |
| `task_watchers` | — | Pivot: tasks ↔ watching users |
| `comments` | ULID | Threaded, with internal flag and Markdown body |
| `attachments` | ULID | Polymorphic (tasks or comments) |
| `tags` / `task_tag` | bigint | Labeling system |
| `activity_log` | bigint | Audit trail per task |
| `notifications` | — | Laravel built-in; stores in-app alerts |

---

## Notifications

The following email/in-app notifications are implemented:

| Notification | Trigger |
|---|---|
| `TaskAssignedNotification` | Task assigned to a user |
| `TaskStatusChangedNotification` | Task status updated |
| `TaskPriorityChangedNotification` | Task priority updated |
| `TaskDueDateChangedNotification` | Task due date changed |
| `NewCommentNotification` | New comment posted on a watched task |
| `CommentMentionNotification` | User @mentioned in a comment |
| `TaskMentionNotification` | User @mentioned in a task description |
| `ProjectInvitationNotification` | User invited to a project |
| `ProjectAddedNotification` | User added to an existing project |
| `AdminInviteNotification` | Admin sends a platform invite |

All notifications are dispatched via queued listeners. Run a queue worker (`php artisan queue:listen`) to process them.
