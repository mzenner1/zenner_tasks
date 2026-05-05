<?php

namespace App\Http\Controllers;

use App\Events\TaskUpdated;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Http\Request;

class MassTaskController extends Controller
{
    public function handle(Request $request, Project $project)
    {
        $request->validate([
            'task_ids'   => ['required', 'array', 'min:1', 'max:500'],
            'task_ids.*' => ['required', 'string'],
            'action'     => ['required', 'string', 'in:delete,watch,move_project,assignees,status,priority,due_date,tags'],
        ]);

        $user = auth()->user();

        // Only load tasks that actually belong to this project
        $tasks = $project->tasks()
            ->whereIn('id', $request->task_ids)
            ->get();

        if ($tasks->isEmpty()) {
            return back()->with('error', 'No matching tasks found.');
        }

        $count = $tasks->count();
        $noun  = $count === 1 ? 'task' : 'tasks';

        switch ($request->action) {

            case 'delete':
                foreach ($tasks as $task) {
                    $this->authorize('delete', $task);
                    $task->delete();
                }
                return back()->with('success', "Deleted {$count} {$noun}.");

            case 'watch':
                foreach ($tasks as $task) {
                    $task->addWatcher($user->id);
                }
                return back()->with('success', "Now watching {$count} {$noun}.");

            case 'move_project':
                $request->validate(['target_project_id' => ['required', 'string']]);

                $targetProject = Project::findOrFail($request->target_project_id);

                if (!$user->isAdmin() && !$targetProject->members()->where('user_id', $user->id)->exists()) {
                    abort(403, 'You are not a member of the target project.');
                }

                $defaultStatus = $targetProject->statuses()->where('is_default', true)->first()
                    ?? $targetProject->statuses()->orderBy('sort_order')->first();

                if (!$defaultStatus) {
                    return back()->with('error', 'Target project has no statuses configured.');
                }

                foreach ($tasks as $task) {
                    $this->authorize('update', $task);
                    $task->project_id  = $targetProject->id;
                    $task->status_id   = $defaultStatus->id;
                    $task->sort_order  = $targetProject->tasks()->where('status_id', $defaultStatus->id)->max('sort_order') + 1;
                    $task->task_number = Task::where('project_id', $targetProject->id)->max('task_number') + 1;
                    $task->save();
                    ActivityLog::create([
                        'task_id'    => $task->id,
                        'user_id'    => $user->id,
                        'event'      => 'moved_project',
                        'properties' => ['to' => $targetProject->name],
                    ]);
                }
                return back()->with('success', "Moved {$count} {$noun} to \"{$targetProject->name}\".");

            case 'assignees':
                $request->validate([
                    'assignees'   => ['nullable', 'array'],
                    'assignees.*' => ['string'],
                ]);
                $assigneeIds = array_filter((array) ($request->assignees ?? []), fn ($v) => $v !== '');
                foreach ($tasks as $task) {
                    $this->authorize('assign', $task);
                    $previousAssignees = $task->assignees()->get()->keyBy('id');
                    $syncResult = $task->assignees()->sync($assigneeIds);
                    $task->update(['last_activity_at' => now()]);
                    $task->addWatcher($user->id);

                    // Log each newly assigned user
                    foreach ($syncResult['attached'] as $userId) {
                        $name = \App\Models\User::find($userId)?->name ?? 'Unknown';
                        ActivityLog::create([
                            'task_id'    => $task->id,
                            'user_id'    => $user->id,
                            'event'      => 'assigned',
                            'properties' => ['to' => $name],
                        ]);
                    }
                    // Log each removed assignee
                    foreach ($syncResult['detached'] as $userId) {
                        $name = $previousAssignees[$userId]?->name ?? 'Unknown';
                        ActivityLog::create([
                            'task_id'    => $task->id,
                            'user_id'    => $user->id,
                            'event'      => 'unassigned',
                            'properties' => ['from' => $name],
                        ]);
                    }

                    // Notify newly-added assignees and auto-watch them
                    $newIds = $syncResult['attached'];
                    if (!empty($newIds)) {
                        $task->load('assignees', 'project');
                        foreach ($task->assignees->whereIn('id', $newIds) as $assignee) {
                            $task->addWatcher($assignee->id);
                            if ($assignee->id !== $user->id) {
                                $assignee->notify(new TaskAssignedNotification($task));
                            }
                        }
                    }
                }
                return back()->with('success', "Assignees updated on {$count} {$noun}.");

            case 'status':
                $request->validate(['status_id' => ['required', 'string']]);
                $status = $project->statuses()->findOrFail($request->status_id);
                foreach ($tasks as $task) {
                    $this->authorize('changeStatus', $task);
                    $oldId = $task->status_id;
                    if ($oldId === $status->id) {
                        continue;
                    }
                    $task->update(['status_id' => $status->id, 'last_activity_at' => now()]);
                    $task->addWatcher($user->id);
                    ActivityLog::create([
                        'task_id'    => $task->id,
                        'user_id'    => $user->id,
                        'event'      => 'status_changed',
                        'properties' => ['from' => optional(\App\Models\Status::find($oldId))->name, 'to' => $status->name],
                    ]);
                    $task->load('assignees', 'project', 'watchers');
                    TaskUpdated::dispatch($task, ['status_id' => [$oldId, $status->id]], $user->id);
                }
                return back()->with('success', "Status set to \"{$status->name}\" on {$count} {$noun}.");

            case 'priority':
                $request->validate(['priority' => ['required', 'in:urgent,high,normal,low']]);
                foreach ($tasks as $task) {
                    $this->authorize('update', $task);
                    $old = $task->priority;
                    if ($old === $request->priority) {
                        continue;
                    }
                    $task->update(['priority' => $request->priority, 'last_activity_at' => now()]);
                    $task->addWatcher($user->id);
                    ActivityLog::create([
                        'task_id'    => $task->id,
                        'user_id'    => $user->id,
                        'event'      => 'priority_changed',
                        'properties' => ['from' => $old, 'to' => $request->priority],
                    ]);
                    $task->load('assignees', 'project', 'watchers');
                    TaskUpdated::dispatch($task, ['priority' => [$old, $request->priority]], $user->id);
                }
                return back()->with('success', "Priority set to \"{$request->priority}\" on {$count} {$noun}.");

            case 'due_date':
                $request->validate(['due_date' => ['nullable', 'date']]);
                foreach ($tasks as $task) {
                    $this->authorize('changeDueDate', $task);
                    $old = $task->due_date;
                    $newDate = $request->due_date ?: null;
                    if ((string) $old === (string) $newDate) {
                        continue;
                    }
                    $task->update(['due_date' => $newDate, 'last_activity_at' => now()]);
                    $task->addWatcher($user->id);
                    ActivityLog::create([
                        'task_id'    => $task->id,
                        'user_id'    => $user->id,
                        'event'      => 'due_date_changed',
                        'properties' => ['from' => (string) $old, 'to' => $newDate],
                    ]);
                    $task->load('assignees', 'project', 'watchers');
                    TaskUpdated::dispatch($task, ['due_date' => [(string) $old, $newDate]], $user->id);
                }
                $msg = $request->due_date
                    ? "Due date set to {$request->due_date} on {$count} {$noun}."
                    : "Due date cleared on {$count} {$noun}.";
                return back()->with('success', $msg);

            case 'tags':
                $request->validate([
                    'tags'   => ['nullable', 'array'],
                    'tags.*' => ['string'],
                ]);
                $tagIds = array_filter((array) ($request->tags ?? []), fn ($v) => $v !== '');
                foreach ($tasks as $task) {
                    $this->authorize('update', $task);
                    $task->tags()->sync($tagIds);
                    $task->update(['last_activity_at' => now()]);
                    $task->addWatcher($user->id);
                }
                return back()->with('success', "Tags updated on {$count} {$noun}.");
        }

        return back()->with('error', 'Unknown action.');
    }
}
