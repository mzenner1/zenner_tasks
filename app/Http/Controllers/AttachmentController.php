<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Task;
use App\Http\Requests\StoreAttachmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Store one or more file attachments on a task or comment.
     */
    public function store(StoreAttachmentRequest $request)
    {
        $this->authorize('create', Attachment::class);

        if ($request->attachable_type === 'task') {
            $attachable = Task::findOrFail($request->attachable_id);
            $dir = 'attachments/tasks/' . $attachable->id;
        } else {
            $attachable = Comment::findOrFail($request->attachable_id);
            $dir = 'attachments/comments/' . $attachable->id;
        }

        foreach ($request->file('files') as $file) {
            $path = $file->store($dir, 'local');
            $attachable->attachments()->create([
                'user_id'   => auth()->id(),
                'filename'  => $file->getClientOriginalName(),
                'path'      => $path,
                'mime_type' => $file->getMimeType(),
                'size'      => $file->getSize(),
            ]);
        }

        $task = $attachable instanceof Task ? $attachable : $attachable->task;

        return redirect()->route('projects.tasks.show', [$task->project_id, $task])
            ->with('success', 'File(s) uploaded.');
    }

    /**
     * Accept an image dropped/pasted into EasyMDE, store it publicly, return JSON {url}.
     */
    public function imageUpload(Request $request)
    {
        $this->authorize('create', Attachment::class);

        $request->validate(['image' => ['required', 'image', 'max:10240']]);

        $file = $request->file('image');
        $path = $file->store('inline-images', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    /**
     * Stream a private attachment to the authenticated user.
     */
    public function download(Attachment $attachment)
    {
        $this->authorize('view', $attachment);

        return Storage::disk('local')->download($attachment->path, $attachment->filename);
    }

    public function destroy(Attachment $attachment)
    {
        $this->authorize('delete', $attachment);

        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        $attachable = $attachment->attachable;
        $task = $attachable instanceof Task ? $attachable : $attachable->task;

        return redirect()->route('projects.tasks.show', [$task->project_id, $task])
            ->with('success', 'Attachment deleted.');
    }
}
