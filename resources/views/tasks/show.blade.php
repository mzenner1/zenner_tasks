<x-app-layout :title="$task->task_number_label . ' – ' . $task->title">
<div class="max-w-5xl space-y-6" x-data="{ detailsOpen: false, copyModal: false, moveModal: false }">

    {{-- Breadcrumb --}}
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600 flex-shrink-0">{{ $project->name }}</a>
            <span class="flex-shrink-0">/</span>
            <span class="font-mono text-indigo-500 font-semibold flex-shrink-0">{{ $task->task_number_label }}</span>
            <span class="flex-shrink-0">/</span>
            <span class="text-gray-800 font-medium truncate">{{ $task->title }}</span>
        </div>
        {{-- Details toggle — visible below lg only --}}
        <button @click="detailsOpen = true"
                class="lg:hidden flex-shrink-0 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 border border-indigo-300 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
            Details
        </button>
    </div>

    {{-- Right panel backdrop --}}
    <div x-show="detailsOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="detailsOpen = false"
         class="fixed inset-0 z-20 bg-black/60 lg:hidden"
         style="display:none"></div>

    <div class="flex gap-6">

        {{-- ── Left: main task content ─────────────────────────────── --}}
        <div class="flex-1 min-w-0 space-y-5">

            {{-- Title --}}
            <div class="flex items-baseline gap-3">
                <span class="text-xl font-mono font-semibold text-indigo-500 flex-shrink-0">{{ $task->task_number_label }}</span>
                <h1 class="text-2xl font-bold text-gray-900 leading-snug">{{ $task->title }}</h1>
            </div>

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
                @php $images = $task->attachments->filter->isImage(); $files = $task->attachments->reject->isImage(); @endphp
                @if($images->count())
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach($images as $attachment)
                    <div class="relative group">
                        <a href="{{ route('attachments.download', $attachment) }}" target="_blank">
                            <img src="{{ route('attachments.download', $attachment) }}"
                                 alt="{{ $attachment->filename }}"
                                 class="h-24 w-24 object-cover rounded-lg border border-gray-200">
                        </a>
                        @can('delete', $attachment)
                        <form method="POST" action="{{ route('attachments.destroy', $attachment) }}"
                              class="absolute top-1 right-1 hidden group-hover:block">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Delete this attachment?')"
                                    class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs leading-none">&times;</button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @endif
                @if($files->count())
                <div class="space-y-2">
                    @foreach($files as $attachment)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <a href="{{ route('attachments.download', $attachment) }}"
                               class="truncate text-indigo-600 hover:underline">{{ $attachment->filename }}</a>
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
                @endif
            </div>
            @endif

            {{-- Upload additional attachments --}}
            @can('create', App\Models\Attachment::class)
            <div class="bg-white rounded-xl border border-dashed border-gray-300 p-4">
                <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data"
                      class="flex items-center gap-3 flex-wrap">
                    @csrf
                    <input type="hidden" name="attachable_type" value="task">
                    <input type="hidden" name="attachable_id" value="{{ $task->id }}">
                    <input type="file" name="files[]" multiple
                           class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-sm file:font-medium hover:file:bg-indigo-100 flex-1 min-w-0">
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

                @can('create', App\Models\Comment::class)
                <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                    <form method="POST" action="{{ route('comments.store', $task) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div>
                            <textarea name="body" rows="3" placeholder="Write a comment…" required
                                      data-easymde
                                      data-image-upload-url="{{ route('attachments.image-upload') }}"
                                      data-mention-url="{{ route('projects.members.search', $project) }}"
                                      data-csrf="{{ csrf_token() }}">{{ old('body') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Attachments</label>
                            <input type="file" name="attachments[]" multiple
                                   class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:font-medium hover:file:bg-indigo-100">
                        </div>
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            @php $role = auth()->user()->projectRole($project->id); @endphp
                            @if($role !== 'client')
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
        {{-- Fixed flyout on < lg, static column on lg+ --}}
        <div :class="detailsOpen ? 'translate-x-0' : 'translate-x-full'"
             class="fixed inset-y-0 right-0 z-30 w-72 bg-gray-50 border-l border-gray-200 overflow-y-auto
                    transform transition-transform duration-200 ease-in-out
                    lg:relative lg:inset-auto lg:z-auto lg:translate-x-0 lg:w-64 lg:bg-transparent lg:border-0
                    flex-shrink-0 space-y-4 p-4 lg:p-0">

            {{-- Close button (mobile/tablet only) --}}
            <div class="flex items-center justify-between mb-2 lg:hidden">
                <span class="text-sm font-semibold text-gray-700">Task Details</span>
                <button @click="detailsOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Created-from-comment origin indicator --}}
            @if($task->sourceComment)
            @php $sc = $task->sourceComment; $originTask = $sc->task; @endphp
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1.5">Origin</p>
                <div class="flex items-start gap-1.5 text-xs text-emerald-800">
                    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>Created from a comment on
                        <a href="{{ route('projects.tasks.show', [$originTask->project_id, $originTask]) }}#comment-{{ $sc->id }}"
                           class="font-semibold hover:underline">{{ $originTask->task_number_label }} – {{ $originTask->title }}</a>
                    </span>
                </div>
            </div>
            @endif

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
                @can('changeDueDate', $task)
                <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}">
                    @csrf @method('PUT')
                    <input type="date" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}"
                           onchange="this.form.submit()"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </form>
                @else
                <p class="text-sm text-gray-700">{{ $task->due_date?->format('M j, Y') ?? '—' }}</p>
                @endcan
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
                            <x-user-avatar :user="$member" size="xs" />
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

            {{-- Tags --}}
            @if($tags->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Tags</label>
                @can('update', $task)
                <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}">
                    @csrf @method('PUT')
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                        <label class="flex items-center gap-1.5 cursor-pointer select-none">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   {{ $task->tags->contains($tag->id) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-white opacity-40 peer-checked:opacity-100 transition cursor-pointer"
                                  style="background-color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit"
                            class="mt-3 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-1.5 rounded-lg transition">
                        Update Tags
                    </button>
                </form>
                @else
                <div class="flex flex-wrap gap-1.5">
                    @forelse($task->tags as $tag)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white"
                          style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                    @empty
                    <span class="text-xs text-gray-400">No tags</span>
                    @endforelse
                </div>
                @endcan
            </div>
            @endif

            {{-- Meta: created by / date --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-xs text-gray-500 space-y-1">
                <div>Created by <span class="font-medium text-gray-700">{{ $task->creator->name }}</span></div>
                <div>{{ $task->created_at->diffForHumans() }}</div>
                <div>Updated {{ $task->updated_at->diffForHumans() }}</div>
            </div>

            {{-- Watch / Unwatch --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                @if($isWatching)
                <form method="POST" action="{{ route('tasks.unwatch', $task) }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="flex items-center gap-2 w-full text-sm font-medium text-indigo-600 hover:text-indigo-800 transition group">
                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 group-hover:bg-indigo-200 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                        </span>
                        <div class="text-left leading-tight">
                            <div>Watching</div>
                            <div class="text-xs text-indigo-400 font-normal group-hover:text-indigo-600">Click to unwatch</div>
                        </div>
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('tasks.watch', $task) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 w-full text-sm font-medium text-gray-500 hover:text-indigo-600 transition group">
                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 group-hover:bg-indigo-100 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </span>
                        <div class="text-left leading-tight">
                            <div>Watch</div>
                            <div class="text-xs text-gray-400 font-normal group-hover:text-indigo-400">Get email notifications</div>
                        </div>
                    </button>
                </form>
                @endif
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-2">
                @can('update', $task)
                <a href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                   class="flex items-center gap-2 text-sm text-gray-600 hover:text-indigo-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Task
                </a>
                @if($availableProjects->isNotEmpty())
                <button type="button" @click="copyModal = true"
                        class="flex items-center gap-2 text-sm text-gray-600 hover:text-indigo-600 transition w-full text-left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Copy to Project
                </button>
                <button type="button" @click="moveModal = true"
                        class="flex items-center gap-2 text-sm text-gray-600 hover:text-amber-600 transition w-full text-left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                    Move to Project
                </button>
                @endif
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

{{-- Copy to Project Modal --}}
<div x-show="copyModal"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 !mt-0"
     @click.self="copyModal = false"
     style="display:none">
    <div class="bg-white rounded-xl border border-gray-200 shadow-xl p-6 w-full max-w-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">Copy Task to Project</h3>
            <button @click="copyModal = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-sm text-gray-500">Select a project to copy this task to:</p>
        <form method="POST" action="{{ route('tasks.copyToProject', $task) }}">
            @csrf
            <div class="space-y-1 max-h-64 overflow-y-auto">
                @foreach($availableProjects as $ap)
                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="target_project_id" value="{{ $ap->id }}" required
                           class="text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">{{ $ap->name }}</span>
                </label>
                @endforeach
            </div>
            <button type="submit"
                    class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                Copy Task
            </button>
        </form>
    </div>
</div>

{{-- Move to Project Modal --}}
<div x-show="moveModal"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 !mt-0"
     @click.self="moveModal = false"
     style="display:none">
    <div class="bg-white rounded-xl border border-gray-200 shadow-xl p-6 w-full max-w-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">Move Task to Project</h3>
            <button @click="moveModal = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-sm text-gray-500">Select a project to move this task to:</p>
        <form method="POST" action="{{ route('tasks.moveToProject', $task) }}">
            @csrf
            <div class="space-y-1 max-h-64 overflow-y-auto">
                @foreach($availableProjects as $ap)
                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="target_project_id" value="{{ $ap->id }}" required
                           class="text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">{{ $ap->name }}</span>
                </label>
                @endforeach
            </div>
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-xs text-amber-700">
                    <strong>Note:</strong> The task will be placed in the first available status of the target project.
                </p>
            </div>
            <button type="submit"
                    class="mt-3 w-full bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                Move Task
            </button>
        </form>
    </div>
</div>

</div>{{-- end x-data --}}
</x-app-layout>
