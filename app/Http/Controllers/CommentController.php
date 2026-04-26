<?php

namespace App\Http\Controllers;

use App\Events\CommentPosted;
use App\Notifications\CommentMentionNotification;
use App\Support\MentionParser;
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

        // Touch task's last_activity_at so it surfaces in 'updated' sort
        $task->update(['last_activity_at' => now()]);

        // Auto-watch: commenter becomes a watcher
        $task->addWatcher(auth()->id());

        // Save any uploaded attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments/comments/' . $comment->id, 'local');
                $comment->attachments()->create([
                    'user_id'   => auth()->id(),
                    'filename'  => $file->getClientOriginalName(),
                    'path'      => $path,
                    'mime_type' => $file->getMimeType(),
                    'size'      => $file->getSize(),
                ]);
            }
        }

        // Fire CommentPosted event → notifies assignees + creator
        $comment->load('author', 'task.project', 'task.assignees', 'task.creator');
        CommentPosted::dispatch($comment);

        // Process @mentions in comment body
        $this->processMentions($comment);

        return redirect()->route('projects.tasks.show', [$task->project_id, $task])
            ->with('success', 'Comment posted.');
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $isInternal = $request->boolean('is_internal');

        // Clients cannot mark comments as internal
        if ($isInternal) {
            $this->authorize('createInternal', [Comment::class, $comment->task->project_id]);
        }

        $comment->update([
            'body'        => $request->body,
            'is_internal' => $isInternal,
        ]);

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

    /**
     * Process @mentions in a comment: auto-watch mentioned users and notify them.
     * Watchers who are already notified via CommentPosted (general comment notification)
     * still receive the targeted mention notification so they know they were called out.
     */
    private function processMentions(\App\Models\Comment $comment): void
    {
        $mentionedIds = MentionParser::extractIds($comment->body);

        if (empty($mentionedIds)) {
            return;
        }

        $task  = $comment->task;
        $users = \App\Models\User::whereIn('id', $mentionedIds)->get();

        foreach ($users as $user) {
            // Auto-watch
            $task->addWatcher($user->id);

            // Skip the commenter themselves
            if ($user->id === $comment->user_id) {
                continue;
            }

            // Don't notify clients about internal comments
            if ($comment->is_internal && $user->projectRole($task->project_id) === 'client') {
                continue;
            }

            $user->notify(new CommentMentionNotification($comment));
        }
    }
}
