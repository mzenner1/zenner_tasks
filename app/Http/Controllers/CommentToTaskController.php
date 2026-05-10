<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentToTaskController extends Controller
{
    public function store(Request $request, Comment $comment)
    {
        $this->authorize('create', Task::class);

        // Prevent converting the same comment twice
        if ($comment->createdTask()->exists()) {
            return redirect()->back()->with('error', 'A task has already been created from this comment.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $parentTask = $comment->task;
        $project    = $parentTask->project;

        $newTask = $project->tasks()->create([
            'status_id'        => $parentTask->status_id,
            'created_by'       => auth()->id(),
            'title'            => $validated['title'],
            'priority'         => $parentTask->priority,
            'sort_order'       => $project->tasks()->where('status_id', $parentTask->status_id)->max('sort_order') + 1,
            'source_comment_id' => $comment->id,
        ]);

        ActivityLog::create([
            'task_id'    => $newTask->id,
            'user_id'    => auth()->id(),
            'event'      => 'created_from_comment',
            'properties' => [
                'source_task_id'     => $parentTask->id,
                'source_task_number' => $parentTask->task_number,
                'source_comment_id'  => $comment->id,
            ],
        ]);

        ActivityLog::create([
            'task_id'    => $parentTask->id,
            'user_id'    => auth()->id(),
            'event'      => 'comment_converted_to_task',
            'properties' => [
                'new_task_id'     => $newTask->id,
                'new_task_number' => $newTask->task_number,
                'comment_id'      => $comment->id,
            ],
        ]);

        return redirect()
            ->route('projects.tasks.show', [$project, $newTask])
            ->with('success', 'Task created from comment.');
    }
}
