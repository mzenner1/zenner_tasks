@php $user = auth()->user(); @endphp
<div id="comment-{{ $comment->id }}" class="rounded-xl border p-4 space-y-2 {{ $comment->is_internal ? 'bg-amber-50 border-amber-200' : 'bg-white border-gray-200' }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-user-avatar :user="$comment->author" size="sm" />
            <span class="text-sm font-medium text-gray-900">{{ $comment->author->name }}</span>
            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
            @if($comment->is_internal)
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-200 text-amber-800">
                🔒 Internal
            </span>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @can('update', $comment)
            <button onclick="document.getElementById('edit-comment-{{ $comment->id }}').classList.toggle('hidden')"
                    class="text-xs text-gray-400 hover:text-indigo-600">Edit</button>
            @endcan
            @can('create', App\Models\Task::class)
            @if(!$comment->createdTask)
            <button onclick="document.getElementById('convert-to-task-modal-{{ $comment->id }}').classList.remove('hidden')"
                    class="text-xs text-gray-400 hover:text-emerald-600" title="Convert to Task">&#x2794; New Task</button>
            @endif
            @endcan
            @can('delete', $comment)
            <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Delete this comment?')"
                        class="text-xs text-gray-400 hover:text-red-600">Delete</button>
            </form>
            @endcan
        </div>
    </div>

    {{-- Comment body --}}
    <div class="text-sm text-gray-700 ml-9 leading-relaxed prose prose-sm max-w-none">{!! $comment->bodyHtml() !!}</div>

    {{-- "Task created from this comment" indicator --}}
    @if($comment->createdTask)
    @php $ct = $comment->createdTask; @endphp
    <div class="ml-9 flex items-center gap-1.5 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-1.5 w-fit">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Task created:
        <a href="{{ route('projects.tasks.show', [$ct->project_id, $ct]) }}"
           class="font-semibold hover:underline">{{ $ct->task_number_label }} – {{ $ct->title }}</a>
    </div>
    @endif

    {{-- Convert-to-task modal --}}
    @can('create', App\Models\Task::class)
    @if(!$comment->createdTask)
    <div id="convert-to-task-modal-{{ $comment->id }}"
         class="hidden ml-9 mt-2 bg-white border border-gray-200 rounded-xl p-4 shadow-sm space-y-3">
        <p class="text-sm font-medium text-gray-700">Create a new task from this comment</p>
        <form method="POST" action="{{ route('comments.convert-to-task', $comment) }}" class="space-y-2">
            @csrf
            <input type="text" name="title" required placeholder="Task title…"
                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">
                    Create Task
                </button>
                <button type="button"
                        onclick="document.getElementById('convert-to-task-modal-{{ $comment->id }}').classList.add('hidden')"
                        class="text-xs text-gray-500 hover:text-gray-700 px-3 py-1.5">Cancel</button>
            </div>
        </form>
    </div>
    @endif
    @endcan

    {{-- Comment attachments --}}
    @if($comment->attachments->count())
    <div class="ml-9 mt-2 space-y-2">
        @php $imgs = $comment->attachments->filter->isImage(); $docs = $comment->attachments->reject->isImage(); @endphp
        @if($imgs->count())
        <div class="flex flex-wrap gap-2">
            @foreach($imgs as $att)
            <div class="relative group">
                <a href="{{ route('attachments.download', $att) }}" target="_blank">
                    <img src="{{ route('attachments.download', $att) }}"
                         alt="{{ $att->filename }}"
                         class="h-20 w-20 object-cover rounded-lg border border-gray-200">
                </a>
                @can('delete', $att)
                <form method="POST" action="{{ route('attachments.destroy', $att) }}"
                      class="absolute top-1 right-1 hidden group-hover:block">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('Delete?')"
                            class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs leading-none">&times;</button>
                </form>
                @endcan
            </div>
            @endforeach
        </div>
        @endif
        @foreach($docs as $att)
        <div class="flex items-center gap-2 text-sm">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
            <a href="{{ route('attachments.download', $att) }}"
               class="truncate text-indigo-600 hover:underline">{{ $att->filename }}</a>
            <span class="text-gray-400 text-xs">{{ $att->humanSize() }}</span>
            @can('delete', $att)
            <form method="POST" action="{{ route('attachments.destroy', $att) }}" class="inline">
                @csrf @method('DELETE')
                <button onclick="return confirm('Delete?')" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
            </form>
            @endcan
        </div>
        @endforeach
    </div>
    @endif

    {{-- Edit form (hidden by default) --}}
    @can('update', $comment)
    <div id="edit-comment-{{ $comment->id }}" class="hidden ml-9 mt-2">
        <form method="POST" action="{{ route('comments.update', $comment) }}">
            @csrf @method('PUT')
            <textarea name="body" rows="3"
                      data-easymde
                      data-image-upload-url="{{ route('attachments.image-upload') }}"
                      data-mention-url="{{ route('projects.members.search', $project) }}"
                      data-csrf="{{ csrf_token() }}">{{ $comment->body }}</textarea>
            @php $editRole = auth()->user()->projectRole($project->id); @endphp
            @if($editRole !== 'client')
            <div class="mt-2">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="is_internal" value="1"
                           {{ $comment->is_internal ? 'checked' : '' }}
                           class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                    <span>Internal note (hidden from clients)</span>
                </label>
            </div>
            @endif
            <div class="flex gap-2 mt-2">
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">
                    Save
                </button>
                <button type="button"
                        onclick="document.getElementById('edit-comment-{{ $comment->id }}').classList.add('hidden')"
                        class="text-xs text-gray-500 hover:text-gray-700 px-3 py-1.5">Cancel</button>
            </div>
        </form>
    </div>
    @endcan

    {{-- Replies --}}
    @if($comment->replies->count())
    <div class="ml-9 space-y-3 pt-2 border-t border-gray-100">
        @foreach($comment->replies as $reply)
        @include('tasks._partials.comment', ['comment' => $reply, 'project' => $project])
        @endforeach
    </div>
    @endif

    {{-- Reply link --}}
    @can('create', App\Models\Comment::class)
    <div class="ml-9">
        <button onclick="document.getElementById('reply-{{ $comment->id }}').classList.toggle('hidden')"
                class="text-xs text-gray-400 hover:text-indigo-600">↩ Reply</button>
        <div id="reply-{{ $comment->id }}" class="hidden mt-2">
            <form method="POST" action="{{ route('comments.store', $comment->task_id) }}"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <textarea name="body" rows="2" placeholder="Write a reply…"
                          data-easymde
                          data-image-upload-url="{{ route('attachments.image-upload') }}"
                          data-mention-url="{{ route('projects.members.search', $project) }}"
                          data-csrf="{{ csrf_token() }}"></textarea>
                <div class="mt-2">
                    <input type="file" name="attachments[]" multiple
                           class="block w-full text-sm text-gray-500 file:mr-3 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs file:font-medium hover:file:bg-indigo-100">
                </div>
                @php $replyRole = auth()->user()->projectRole($project->id); @endphp
                @if($replyRole !== 'client')
                <div class="mt-2">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="is_internal" value="1"
                               class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                        <span>Internal note (hidden from clients)</span>
                    </label>
                </div>
                @endif
                <button type="submit"
                        class="mt-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg transition">
                    Post Reply
                </button>
            </form>
        </div>
    </div>
    @endcan
</div>
