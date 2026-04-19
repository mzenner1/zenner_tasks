<x-app-layout :title="$task->title">
<div class="max-w-5xl space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">{{ $project->name }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium truncate">{{ $task->title }}</span>
    </div>

    <div class="flex gap-6">

        {{-- ── Left: main task content ─────────────────────────────── --}}
        <div class="flex-1 min-w-0 space-y-5">

            {{-- Title --}}
            <h1 class="text-2xl font-bold text-gray-900 leading-snug">{{ $task->title }}</h1>

            {{-- Description --}}
            @if($descriptionHtml)
            <div class="prose prose-sm max-w-none bg-white border border-gray-200 rounded-xl p-5">
                {!! $descriptionHtml !!}
            </div>
            @endif

            {{-- Attachments --}}
            @if($task->attachments->count())
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Attachments</h3>
                <div class="space-y-2">
                    @foreach($task->attachments as $attachment)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span class="truncate text-gray-700">{{ $attachment->filename }}</span>
                            <span class="text-gray-400 text-xs flex-shrink-0">{{ $attachment->humanSize() }}</span>
                        </div>
                        @can('delete', $attachment)
                        <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" class="flex-shrink-0">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs"
                                    onclick="return confirm('Delete this attachment?')">Delete</button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Upload attachment --}}
            @can('create', App\Models\Attachment::class)
            <div class="bg-white rounded-xl border border-dashed border-gray-300 p-4">
                <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data"
                      class="flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="attachable_type" value="task">
                    <input type="hidden" name="attachable_id" value="{{ $task->id }}">
                    <input type="file" name="file" multiple
                           class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-sm file:font-medium hover:file:bg-indigo-100">
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded-lg transition flex-shrink-0">
                        Upload
                    </button>
                </form>
            </div>
            @endcan

            {{-- Comments --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Comments ({{ $comments->count() }})</h3>

                @forelse($comments as $comment)
                    @include('tasks._partials.comment', ['comment' => $comment, 'project' => $project])
                @empty
                <p class="text-sm text-gray-400">No comments yet. Be the first to comment.</p>
                @endforelse

                {{-- New comment form --}}
                @can('create', App\Models\Comment::class)
                <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                    <form method="POST" action="{{ route('comments.store', $task) }}">
                        @csrf
                        <div>
                            <textarea name="body" rows="3" placeholder="Write a comment…" required
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('body') }}</textarea>
                        </div>
                        <div class="flex items-center justify-between">
                            @php $role = auth()->user()->projectRole($project->id); @endphp
                            @if(in_array($role, ['admin','member']))
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="checkbox" name="is_internal" value="1"
                                       class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                                <span>Internal note (hidden from clients)</span>
                            </label>
                            @else
                            <div></div>
                            @endif
                            <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                                Post Comment
                            </button>
                        </div>
                    </form>
                </div>
                @endcan
            </div>

            {{-- Activity Log --}}
            <div x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-90':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Activity Log ({{ $activityLog->count() }})
                </button>
                <div x-show="open" x-transition class="mt-3 space-y-2">
                    @foreach($activityLog as $entry)
                        @include('tasks._partials.activity-item', ['entry' => $entry])
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ── Right: task meta sidebar ────────────────────────────── --}}
        <div class="w-64 flex-shrink-0 space-y-4">

            {{-- Status --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Status</label>
                @can('changeStatus', $task)
                <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}">
                    @csrf @method('PUT')
                    <select name="status_id" onchange="this.form.submit()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ $task->status_id == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                        @endforeach
                    </select>
                </form>
                @else
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium text-white"
                      style="background-color: {{ $task->status->color }}">
                    {{ $task->status->name }}
                </span>
                @endcan
            </div>

            {{-- Priority --}}
            @can('update', $task)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Priority</label>
                <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}">
                    @csrf @method('PUT')
                    <select name="priority" onchange="this.form.submit()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['low','normal','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ $task->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Due Date --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Due Date</label>
                <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}">
                    @csrf @method('PUT')
                    <input type="date" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}"
                           onchange="this.form.submit()"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </form>
            </div>

            {{-- Assignees --}}
            @can('assign', $task)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Assignees</label>
                <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}">
                    @csrf @method('PUT')
                    <div class="space-y-1 max-h-48 overflow-y-auto">
                        @foreach($members as $member)
                        <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 px-1 py-0.5 rounded">
                            <input type="checkbox" name="assignees[]" value="{{ $member->id }}"
                                   {{ $task->assignees->contains($member->id) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="w-5 h-5 rounded-full bg-indigo-400 flex items-center justify-center text-[9px] font-bold text-white flex-shrink-0">
                                {{ strtoupper(substr($member->name,0,1)) }}
                            </div>
                            <span class="truncate">{{ $member->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit"
                            class="mt-3 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-1.5 rounded-lg transition">
                        Update Assignees
                    </button>
                </form>
            </div>
            @endcan
            @endcan

            {{-- Meta: created by / date --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-xs text-gray-500 space-y-1">
                <div>Created by <span class="font-medium text-gray-700">{{ $task->creator->name }}</span></div>
                <div>{{ $task->created_at->diffForHumans() }}</div>
                <div>Updated {{ $task->updated_at->diffForHumans() }}</div>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-2">
                @can('update', $task)
                <a href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                   class="flex items-center gap-2 text-sm text-gray-600 hover:text-indigo-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Task
                </a>
                @endcan
                @can('delete', $task)
                <form method="POST" action="{{ route('projects.tasks.destroy', [$project, $task]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this task permanently?')"
                            class="flex items-center gap-2 text-sm text-red-500 hover:text-red-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Task
                    </button>
                </form>
                @endcan
            </div>

        </div>
    </div>
</div>
</x-app-layout>
