@php $user = auth()->user(); @endphp
<div class="rounded-xl border p-4 space-y-2 {{ $comment->is_internal ? 'bg-amber-50 border-amber-200' : 'bg-white border-gray-200' }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-indigo-400 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                {{ strtoupper(substr($comment->author->name, 0, 1)) }}
            </div>
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
    <div class="text-sm text-gray-700 ml-9 leading-relaxed">{{ $comment->body }}</div>

    {{-- Edit form (hidden by default) --}}
    @can('update', $comment)
    <div id="edit-comment-{{ $comment->id }}" class="hidden ml-9 mt-2">
        <form method="POST" action="{{ route('comments.update', $comment) }}">
            @csrf @method('PUT')
            <textarea name="body" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ $comment->body }}</textarea>
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
            <form method="POST" action="{{ route('comments.store', $comment->task_id) }}">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <textarea name="body" rows="2" placeholder="Write a reply…"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                <button type="submit"
                        class="mt-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg transition">
                    Post Reply
                </button>
            </form>
        </div>
    </div>
    @endcan
</div>
