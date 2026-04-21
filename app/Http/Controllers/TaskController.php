<?php

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\MoveTaskRequest;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;

class TaskController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $project->tasks()
            ->where('is_archived', false)
            ->with(['assignees', 'status', 'creator'])
            ->withCount('comments')
            ->when($request->assignee,  fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('user_id', $request->assignee)))
            ->when($request->status_id, fn ($q) => $q->where('status_id', $request->status_id))
            ->when($request->priority,  fn ($q) => $q->where('priority', $request->priority))
            ->when($request->due_date,  fn ($q) => $q->whereDate('due_date', $request->due_date))
            ->when($request->search,    fn ($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->orderBy('sort_order')
            ->get();

        $statuses = $project->statuses;
        $members  = $project->members;

        return view('tasks.index', compact('project', 'tasks', 'statuses', 'members'));
    }

    public function create(Project $project)
    {
        $this->authorize('create', Task::class);

        $statuses = $project->statuses;
        $members  = $project->members;

        return view('tasks.create', compact('project', 'statuses', 'members'));
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        $this->authorize('create', Task::class);

        $task = $project->tasks()->create([
            'status_id'   => $request->status_id,
            'created_by'  => auth()->id(),
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority ?? 'normal',
            'due_date'    => $request->due_date,
            'sort_order'  => $project->tasks()->where('status_id', $request->status_id)->max('sort_order') + 1,
        ]);

        if ($request->filled('assignees')) {
            $task->assignees()->sync($request->assignees);
        }

        $task->update(['last_activity_at' => now()]);

        ActivityLog::create([
            'task_id'    => $task->id,
            'user_id'    => auth()->id(),
            'event'      => 'created',
            'properties' => null,
        ]);

        // Save any uploaded attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments/tasks/' . $task->id, 'local');
                $task->attachments()->create([
                    'user_id'   => auth()->id(),
                    'filename'  => $file->getClientOriginalName(),
                    'path'      => $path,
                    'mime_type' => $file->getMimeType(),
                    'size'      => $file->getSize(),
                ]);
            }
        }

        // Fire TaskCreated event → notifies assignees
        $task->load('assignees', 'project');
        TaskCreated::dispatch($task);

        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Task created successfully.');
    }

    public function show(Project $project, Task $task)
    {
        $this->authorize('view', $task);

        $user = auth()->user();

        $task->load(['assignees', 'status', 'creator', 'attachments.uploader', 'activityLog.user']);

        $converter = new CommonMarkConverter(['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $descriptionHtml = $task->description
            ? $converter->convert($task->description)->getContent()
            : null;

        $comments = $task->comments()
            ->visibleTo($user, $project->id)
            ->topLevel()
            ->with(['author', 'attachments', 'replies' => fn ($q) => $q->with('author')])
            ->get();

        $statuses    = $project->statuses;
        $members     = $project->members;
        $activityLog = $task->activityLog()->with('user')->get();

        return view('tasks.show', compact(
            'project', 'task', 'comments', 'statuses',
            'members', 'descriptionHtml', 'activityLog'
        ));
    }

    public function edit(Project $project, Task $task)
    {
        $this->authorize('update', $task);

        $statuses = $project->statuses;
        $members  = $project->members;

        return view('tasks.edit', compact('project', 'task', 'statuses', 'members'));
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        $hasTaskFields = $request->hasAny(['title', 'description', 'priority', 'due_date']);
        $hasStatus     = $request->has('status_id');
        $hasAssignees  = $request->has('assignees');

        // Authorize each action independently so clients/members can change
        // status and assignees on any task, not just ones they created.
        if ($hasTaskFields) {
            $this->authorize('update', $task);
        }
        if ($hasStatus) {
            $this->authorize('changeStatus', $task);
        }
        if ($hasAssignees) {
            $this->authorize('assign', $task);
        }

        if ($hasTaskFields) {
            $task->fill($request->only(['title', 'description', 'priority', 'due_date']));
        }
        if ($hasStatus) {
            $task->fill($request->only(['status_id']));
        }

        // Detect changes and write to activity_log BEFORE saving
        $changes     = [];
        $eventChanges = [];

        if ($task->isDirty('status_id')) {
            $oldId = $task->getOriginal('status_id');
            $newId = $task->status_id;
            $fromStatus = \App\Models\Status::find($oldId);
            $toStatus   = \App\Models\Status::find($newId);
            $changes[] = [
                'event'      => 'status_changed',
                'properties' => ['from' => $fromStatus?->name, 'to' => $toStatus?->name],
            ];
            $eventChanges['status_id'] = [$oldId, $newId];
        }

        if ($task->isDirty('due_date')) {
            $changes[] = [
                'event'      => 'due_date_changed',
                'properties' => ['from' => $task->getOriginal('due_date'), 'to' => (string) $task->due_date],
            ];
            $eventChanges['due_date'] = [$task->getOriginal('due_date'), $task->due_date];
        }

        if ($task->isDirty('priority')) {
            $changes[] = [
                'event'      => 'priority_changed',
                'properties' => ['from' => $task->getOriginal('priority'), 'to' => $task->priority],
            ];
            $eventChanges['priority'] = [$task->getOriginal('priority'), $task->priority];
        }

        $task->save();
        $task->update(['last_activity_at' => now()]);

        // Sync assignees if provided
        if ($hasAssignees) {
            $task->assignees()->sync($request->assignees ?? []);
        }

        foreach ($changes as $change) {
            ActivityLog::create([
                'task_id'    => $task->id,
                'user_id'    => auth()->id(),
                'event'      => $change['event'],
                'properties' => $change['properties'],
            ]);
        }

        // Save any newly uploaded attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments/tasks/' . $task->id, 'local');
                $task->attachments()->create([
                    'user_id'   => auth()->id(),
                    'filename'  => $file->getClientOriginalName(),
                    'path'      => $path,
                    'mime_type' => $file->getMimeType(),
                    'size'      => $file->getSize(),
                ]);
            }
        }

        // Fire TaskUpdated event → notifies assignees of relevant changes
        if (!empty($eventChanges)) {
            $task->load('assignees', 'project');
            TaskUpdated::dispatch($task, $eventChanges);
        }

        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Project $project, Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Task deleted.');
    }

    /**
     * Kanban drag-drop endpoint. Accepts JSON {status_id, sort_order}.
     */
    public function move(MoveTaskRequest $request, Task $task)
    {
        $this->authorize('changeStatus', $task);

        $oldStatusId = $task->status_id;

        $task->update([
            'status_id'  => $request->status_id,
            'sort_order' => $request->sort_order,
        ]);

        ActivityLog::create([
            'task_id'    => $task->id,
            'user_id'    => auth()->id(),
            'event'      => 'status_changed',
            'properties' => ['to' => $task->fresh()->status->name],
        ]);

        // Fire TaskUpdated event for status change via Kanban
        $task->load('assignees', 'project');
        TaskUpdated::dispatch($task, ['status_id' => [$oldStatusId, $request->status_id]]);

        return response()->json(['success' => true]);
    }
}
