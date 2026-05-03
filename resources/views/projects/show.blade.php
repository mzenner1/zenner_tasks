<x-app-layout :title="$project->name">
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $project->color ?? '#6366f1' }}"></span>
            <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $project->name }}</h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
               class="px-3 py-1.5 text-xs rounded-lg border transition
                      {{ request('view', 'list') === 'list' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400' }}">
                ☰ List
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'board']) }}"
               class="hidden lg:inline px-3 py-1.5 text-xs rounded-lg border transition
                      {{ request('view') === 'board' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400' }}">
                ⊞ Board
            </a>
            @can('update', $project)
            <a href="{{ route('projects.edit', $project) }}"
               class="px-3 py-1.5 text-xs rounded-lg border border-gray-300 bg-white text-gray-600 hover:border-indigo-400 transition">
                ⚙ Settings
            </a>
            @endcan
            @can('manageTags', $project)
            <a href="{{ route('projects.tags.index', $project) }}"
               class="px-3 py-1.5 text-xs rounded-lg border border-gray-300 bg-white text-gray-600 hover:border-indigo-400 transition">
                🏷 Tags
            </a>
            @endcan
            @can('create', App\Models\Task::class)
            <a href="{{ route('projects.tasks.create', $project) }}"
               class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition">
                + New Task
            </a>
            @endcan
        </div>
    </div>

    {{-- Filter + Sort toolbar --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <form method="GET" action="{{ route('projects.show', $project) }}" id="filter-form"
              x-data="filterBar()" @click.outside="closeAll()">

            <input type="hidden" name="view" value="{{ request('view', 'list') }}">

            <div class="flex flex-wrap items-center gap-2 p-3">

                {{-- Search --}}
                <div class="relative w-full sm:w-48" x-data="{ hasValue: {{ request('search') ? 'true' : 'false' }} }">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks…"
                           @input="hasValue = $el.value.length > 0"
                           class="border border-gray-300 rounded-lg px-3 py-1.5 pr-7 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-full">
                    <button type="button"
                            x-show="hasValue"
                            x-cloak
                            @click="$el.closest('.relative').querySelector('input').value = ''; hasValue = false; document.getElementById('filter-form').submit()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-lg leading-none"
                            title="Clear search">&times;</button>
                </div>

                {{-- Status multi-select --}}
                <div class="relative" x-data>
                    <button type="button" @click="toggle('status')"
                            class="flex items-center gap-2 border rounded-lg px-3 pr-8 py-1.5 text-sm min-w-[130px] relative focus:outline-none focus:ring-2 focus:ring-indigo-500 transition
                                   {{ count($filterStatusIds) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 bg-white text-gray-700' }}">
                        <span>
                            @if(count($filterStatusIds))
                                {{ count($filterStatusIds) }} Status{{ count($filterStatusIds) > 1 ? 'es' : '' }}
                            @else
                                All Statuses
                            @endif
                        </span>
                        <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 'status'" x-cloak
                         class="absolute z-50 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg min-w-[180px] py-1">
                        @foreach($statuses as $status)
                        <label class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm text-gray-700">
                            <input type="checkbox" name="status_ids[]" value="{{ $status->id }}"
                                   {{ in_array($status->id, $filterStatusIds) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full inline-block flex-shrink-0" style="background-color: {{ $status->color }}"></span>
                                {{ $status->name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Priority multi-select --}}
                <div class="relative" x-data>
                    <button type="button" @click="toggle('priority')"
                            class="flex items-center gap-2 border rounded-lg px-3 pr-8 py-1.5 text-sm min-w-[130px] relative focus:outline-none focus:ring-2 focus:ring-indigo-500 transition
                                   {{ count($filterPriorities) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 bg-white text-gray-700' }}">
                        <span>
                            @if(count($filterPriorities))
                                {{ count($filterPriorities) }} {{ count($filterPriorities) === 1 ? 'Priority' : 'Priorities' }}
                            @else
                                All Priorities
                            @endif
                        </span>
                        <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 'priority'" x-cloak
                         class="absolute z-50 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg min-w-[160px] py-1">
                        @foreach(['urgent' => 'bg-red-100 text-red-700', 'high' => 'bg-orange-100 text-orange-700', 'normal' => 'bg-blue-100 text-blue-700', 'low' => 'bg-gray-100 text-gray-600'] as $p => $cls)
                        <label class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm text-gray-700">
                            <input type="checkbox" name="priorities[]" value="{{ $p }}"
                                   {{ in_array($p, $filterPriorities) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $cls }}">{{ ucfirst($p) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Assignee multi-select --}}
                <div class="relative" x-data>
                    <button type="button" @click="toggle('assignee')"
                            class="flex items-center gap-2 border rounded-lg px-3 pr-8 py-1.5 text-sm min-w-[140px] relative focus:outline-none focus:ring-2 focus:ring-indigo-500 transition
                                   {{ count($filterAssignees) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 bg-white text-gray-700' }}">
                        <span>
                            @if(count($filterAssignees))
                                {{ count($filterAssignees) }} {{ count($filterAssignees) === 1 ? 'Assignee' : 'Assignees' }}
                            @else
                                All Assignees
                            @endif
                        </span>
                        <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 'assignee'" x-cloak
                         class="absolute z-50 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg min-w-[180px] py-1">
                        @foreach($members as $member)
                        <label class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm text-gray-700">
                            <input type="checkbox" name="assignees[]" value="{{ $member->id }}"
                                   {{ in_array($member->id, $filterAssignees) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <x-user-avatar :user="$member" size="sm" class="border-2 border-white" />
                            {{ $member->name }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Tag multi-select --}}
                @if($tags->isNotEmpty())
                <div class="relative" x-data>
                    <button type="button" @click="toggle('tag')"
                            class="flex items-center gap-2 border rounded-lg px-3 pr-8 py-1.5 text-sm min-w-[120px] relative focus:outline-none focus:ring-2 focus:ring-indigo-500 transition
                                   {{ count($filterTagIds) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 bg-white text-gray-700' }}">
                        <span>
                            @if(count($filterTagIds))
                                {{ count($filterTagIds) }} {{ count($filterTagIds) === 1 ? 'Tag' : 'Tags' }}
                            @else
                                All Tags
                            @endif
                        </span>
                        <svg class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === 'tag'" x-cloak
                         class="absolute z-50 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg min-w-[180px] py-1">
                        @foreach($tags as $tag)
                        <label class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm text-gray-700">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                                   {{ in_array($tag->id, $filterTagIds) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full inline-block flex-shrink-0" style="background-color: {{ $tag->color }}"></span>
                                {{ $tag->name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Sort (list view only) --}}
                @if(request('view', 'list') === 'list')                <select name="sort"
                        onchange="document.getElementById('filter-form').submit()"
                        class="border border-gray-300 rounded-lg px-3 pr-8 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-full sm:w-auto">
                    <option value="updated"      {{ ($activeSort ?? 'updated') === 'updated'      ? 'selected' : '' }}>Recently Updated</option>
                    <option value="updated_last" {{ ($activeSort ?? '') === 'updated_last'        ? 'selected' : '' }}>Least Recently Updated</option>
                    <option value="created"      {{ ($activeSort ?? '') === 'created'             ? 'selected' : '' }}>Newest First</option>
                    <option value="created_last" {{ ($activeSort ?? '') === 'created_last'        ? 'selected' : '' }}>Oldest First</option>
                    <option value="title"        {{ ($activeSort ?? '') === 'title'               ? 'selected' : '' }}>Title (A–Z)</option>
                    <option value="priority"     {{ ($activeSort ?? '') === 'priority'            ? 'selected' : '' }}>Priority (Urgent → Low)</option>
                    <option value="due_date"     {{ ($activeSort ?? '') === 'due_date'            ? 'selected' : '' }}>Due Date</option>
                </select>
                @endif

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Actions --}}
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded-lg transition">
                    Apply
                </button>
                @php
                    $hasActiveFilters = count($filterStatusIds) || count($filterPriorities) || count($filterAssignees) || count($filterTagIds) || request('search');
                @endphp
                @if($hasActiveFilters)
                @php
                    $clearParams = array_filter(['view' => request('view'), 'sort' => $activeSort ?? null]) + ['clear_filters' => 1];
                @endphp
                <a href="{{ route('projects.show', $project) . ($clearParams ? '?' . http_build_query($clearParams) : '') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 whitespace-nowrap">Clear filters</a>
                @endif

            </div>
        </form>
    </div>

    {{-- Task count + member avatars bar --}}
    @php $extraMembers = $members->count() - 4; @endphp
    <div class="bg-white rounded-t-xl border border-gray-200 border-b-0 px-5 py-2 flex items-center justify-between text-sm text-gray-500">
        {{-- Task count --}}
        <div>
            @if($filteredTaskCount === $totalTaskCount)
                <span class="font-medium text-gray-700">{{ $totalTaskCount }}</span>&nbsp;{{ Str::plural('task', $totalTaskCount) }}
            @else
                <span class="font-medium text-indigo-600">{{ $filteredTaskCount }}</span>&nbsp;of&nbsp;<span class="font-medium text-gray-700">{{ $totalTaskCount }}</span>&nbsp;{{ Str::plural('task', $totalTaskCount) }}<span class="ml-1.5 text-gray-400">— filtered</span>
            @endif
        </div>
        {{-- Member avatars --}}
        <div class="flex items-center gap-2">
            <div class="flex -space-x-2">
                @foreach($members->take(4) as $member)
                <x-user-avatar :user="$member" size="md" class="border-2 border-white" :title="$member->name" />
                @endforeach
            </div>
            @if($extraMembers > 0)
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 border-2 border-white text-xs font-semibold text-gray-600 flex-shrink-0">
                +{{ $extraMembers }}
            </span>
            @endif
            @can('update', $project)
            <a href="{{ route('projects.members.index', $project) }}"
               title="Manage members"
               class="w-8 h-8 flex items-center justify-center rounded-full border-2 border-dashed border-gray-300 text-gray-400 hover:border-indigo-400 hover:text-indigo-500 transition text-lg leading-none flex-shrink-0">
                +
            </a>
            @endcan
        </div>
    </div>

    {{-- Task List — mobile cards --}}
    <div class="bg-white rounded-b-xl border border-gray-200 overflow-hidden !mt-0">

        {{-- Mobile card list (hidden on sm+) --}}
        <div class="divide-y divide-gray-100 sm:hidden">
            @forelse($tasks as $task)
            @php $pColors = ['low'=>'bg-gray-100 text-gray-600','normal'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','urgent'=>'bg-red-100 text-red-700']; @endphp
            <a href="{{ route('projects.tasks.show', [$project, $task]) }}"
               class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition">
                {{-- Priority stripe --}}
                <div class="w-1 self-stretch rounded-full flex-shrink-0 mt-0.5
                    {{ $task->priority === 'urgent' ? 'bg-red-500' : ($task->priority === 'high' ? 'bg-orange-400' : ($task->priority === 'normal' ? 'bg-blue-400' : 'bg-gray-300')) }}">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="font-mono text-indigo-500 font-semibold text-xs flex-shrink-0">{{ $task->task_number_label }}</span>
                        <span class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</span>
                    </div>
                    @php
                        $commentExcerpt = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace(['/!\[[^\]]*\]\([^)]*\)/', '/@\[([^\]]+)\]\([^)]*\)/'], ['', '@$1'], $task->latestComment->body ?? ''))));
                    @endphp
                    @if($task->latestComment && $commentExcerpt)
                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ Str::limit($commentExcerpt, 100) }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white"
                              style="background-color: {{ $task->status->color }}">
                            {{ $task->status->name }}
                        </span>
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $pColors[$task->priority] ?? '' }}">{{ ucfirst($task->priority) }}</span>
                        @if($task->due_date)
                        <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            {{ $task->due_date->format('M j') }}
                        </span>
                        @endif
                        @foreach($task->tags as $tag)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium text-white"
                              style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
                {{-- Assignees --}}
                <div class="flex -space-x-1 flex-shrink-0 mt-0.5">
                    @foreach($task->assignees->take(3) as $a)
                    <x-user-avatar :user="$a" size="sm" class="border-2 border-white" />
                    @endforeach
                </div>
            </a>
            @empty
            <div class="px-5 py-10 text-center text-gray-400 text-sm">No tasks found.</div>
            @endforelse
        </div>

        {{-- Desktop table (hidden below sm) --}}
        <table class="hidden sm:table w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 w-full">Task</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Priority</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Assignees</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Due Date</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tasks as $task)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('projects.tasks.show', [$project, $task]) }}"
                               class="font-medium text-gray-900 hover:text-indigo-600">
                                <span class="font-mono text-indigo-500 font-semibold">{{ $task->task_number_label }}</span>
                                <span class="text-gray-400 mx-0.5">–</span>{{ $task->title }}
                            </a>
                            @if($task->comments_count > 0)
                            <span class="inline-flex items-center gap-1 text-xs text-gray-400" title="{{ $task->comments_count }} {{ Str::plural('comment', $task->comments_count) }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ $task->comments_count }}
                            </span>
                            @endif
                        </div>
                        @php
                            $commentExcerpt = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace(['/!\[[^\]]*\]\([^)]*\)/', '/@\[([^\]]+)\]\([^)]*\)/'], ['', '@$1'], $task->latestComment->body ?? ''))));
                        @endphp
                        @if($task->latestComment && $commentExcerpt)
                        <p class="text-xs text-gray-400 mt-0.5 line-clamp-1 max-w-lg">{{ Str::limit($commentExcerpt, 160) }}</p>
                        @endif
                        @if($task->tags->isNotEmpty())
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            @foreach($task->tags as $tag)
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-medium text-white"
                                  style="background-color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium text-white whitespace-nowrap"
                              style="background-color: {{ $task->status->color }}">
                            {{ $task->status->name }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @php $pColors = ['low'=>'bg-gray-100 text-gray-600','normal'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','urgent'=>'bg-red-100 text-red-700']; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $pColors[$task->priority] ?? '' }}">{{ ucfirst($task->priority) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex -space-x-1">
                            @foreach($task->assignees->take(3) as $a)
                            <x-user-avatar :user="$a" size="sm" class="border-2 border-white" />
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap {{ $task->due_date?->isPast() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                        {{ $task->due_date?->format('M j, Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                        {{ $task->created_at->format('M j, Y') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No tasks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
function filterBar() {
    return {
        open: null,
        toggle(name) {
            this.open = this.open === name ? null : name;
        },
        closeAll() {
            this.open = null;
        }
    }
}
</script>
</x-app-layout>
