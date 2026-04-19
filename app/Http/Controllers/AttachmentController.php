<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Task;
use App\Http\Requests\StoreAttachmentRequest;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request)
    {
        $this->authorize('create', Attachment::class);

        // Resolve the attachable model (task or comment)
        if ($request->attachable_type === 'task') {
            $attachable = Task::findOrFail($request->attachable_id);
            $dir = 'attachments/tasks/' . $attachable->id;
        } else {
            $attachable = Comment::findOrFail($request->attachable_id);
            $dir = 'attachments/comments/' . $attachable->id;
        }

        $file = $request->file('file');
        $path = $file->store($dir, 'local');

        $attachable->attachments()->create([
            'user_id'   => auth()->id(),
            'filename'  => $file->getClientOriginalName(),
            'path'      => $path,
            'mime_type' => $file->getMimeType(),
            'size'      => $file->getSize(),
        ]);

        // Redirect back to the task regardless of whether it was a task or comment attachment
        $task = $attachable instanceof Task ? $attachable : $attachable->task;

        return redirect()->route('projects.tasks.show', [$task->project_id, $task])
            ->with('success', 'File uploaded.');
    }

    public function destroy(Attachment $attachment)
    {
        $this->authorize('delete', $attachment);

        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        // Resolve task for redirect
        $attachable = $attachment->attachable;
        $task = $attachable instanceof Task ? $attachable : $attachable->task;

        return redirect()->route('projects.tasks.show', [$task->project_id, $task])
            ->with('success', 'Attachment deleted.');
    }
}
