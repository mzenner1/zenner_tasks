<?php

namespace App\Http\Controllers;

use App\Events\CommentPosted;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Task;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Task $task)
    {
        $this->authorize('create', Comment::class);

        $isInternal = $request->boolean('is_internal');

        // Clients cannot post internal comments
        if ($isInternal) {
            $this->authorize('createInternal', [Comment::class, $task->project_id]);
        }

        $comment = $task->comments()->create([
            'user_id'     => auth()->id(),
            'body'        => $request->body,
            'parent_id'   => $request->parent_id,
            'is_internal' => $isInternal,
        ]);

        ActivityLog::create([
            'task_id'    => $task->id,
            'user_id'    => auth()->id(),
            'event'      => 'commented',
            'properties' => ['comment_id' => $comment->id, 'is_internal' => $isInternal],
        ]);

        // Fire CommentPosted event → notifies assignees + creator
        $comment->load('author', 'task.project', 'task.assignees', 'task.creator');
        CommentPosted::dispatch($comment);

        return redirect()->route('projects.tasks.show', [$task->project_id, $task])
            ->with('success', 'Comment posted.');
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $comment->update(['body' => $request->body]);

        return redirect()->route('projects.tasks.show', [$comment->task->project_id, $comment->task_id])
            ->with('success', 'Comment updated.');
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $task      = $comment->task;
        $projectId = $task->project_id;

        $comment->delete();

        return redirect()->route('projects.tasks.show', [$projectId, $task])
            ->with('success', 'Comment deleted.');
    }
}
