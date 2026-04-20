<x-app-layout :title="$project->name">
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full" style="background-color: {{ $project->color ?? '#6366f1' }}"></span>
            <h1 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
               class="px-3 py-1.5 text-xs rounded-lg border transition
                      {{ request('view', 'list') === 'list' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400' }}">
                ☰ List
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'board']) }}"
               class="px-3 py-1.5 text-xs rounded-lg border transition
                      {{ request('view') === 'board' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400' }}">
                ⊞ Board
            </a>
            @can('update', $project)
            <a href="{{ route('projects.edit', $project) }}"
               class="px-3 py-1.5 text-xs rounded-lg border border-gray-300 bg-white text-gray-600 hover:border-indigo-400 transition">
                ⚙ Settings
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

            <div class="flex flex-wrap items-center gap-3 p-3">

                {{-- Search --}}
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks…"
                       class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48">

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
                            <div class="w-5 h-5 rounded-full bg-indigo-400 flex items-center justify-center text-[9px] font-bold text-white flex-shrink-0">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            {{ $member->name }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Divider --}}
                @if(request('view', 'list') === 'list')
                <span class="h-5 w-px bg-gray-200"></span>

                {{-- Sort (list view only) --}}
                <div class="flex items-center gap-2">
                    <label for="sort-select" class="text-sm text-gray-500 whitespace-nowrap">Sort by:</label>
                    <select id="sort-select" name="sort"
                            onchange="document.getElementById('filter-form').submit()"
                            class="border border-gray-300 rounded-lg px-3 pr-8 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 min-w-[200px]">
                        <option value="updated"      {{ ($activeSort ?? 'updated') === 'updated'      ? 'selected' : '' }}>Recently Updated</option>
                        <option value="updated_last" {{ ($activeSort ?? '') === 'updated_last'        ? 'selected' : '' }}>Least Recently Updated</option>
                        <option value="created"      {{ ($activeSort ?? '') === 'created'             ? 'selected' : '' }}>Newest First</option>
                        <option value="created_last" {{ ($activeSort ?? '') === 'created_last'        ? 'selected' : '' }}>Oldest First</option>
                        <option value="title"        {{ ($activeSort ?? '') === 'title'               ? 'selected' : '' }}>Title (A–Z)</option>
                        <option value="priority"     {{ ($activeSort ?? '') === 'priority'            ? 'selected' : '' }}>Priority (Urgent → Low)</option>
                        <option value="due_date"     {{ ($activeSort ?? '') === 'due_date'            ? 'selected' : '' }}>Due Date</option>
                    </select>
                </div>
                @endif

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Actions --}}
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded-lg transition">
                    Apply
                </button>
                @php
                    $hasActiveFilters = count($filterStatusIds) || count($filterPriorities) || count($filterAssignees) || request('search');
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

    {{-- Task List --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Count bar --}}
        <div class="flex items-center px-5 py-2.5 border-b border-gray-100 bg-gray-50 text-sm text-gray-500">
            @if($filteredTaskCount === $totalTaskCount)
                <span class="font-medium text-gray-700">{{ $totalTaskCount }}</span>&nbsp;{{ Str::plural('task', $totalTaskCount) }}
            @else
                <span class="font-medium text-indigo-600">{{ $filteredTaskCount }}</span>&nbsp;of&nbsp;<span class="font-medium text-gray-700">{{ $totalTaskCount }}</span>&nbsp;{{ Str::plural('task', $totalTaskCount) }}<span class="ml-1.5 text-gray-400">— filtered</span>
            @endif
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 w-full">Task</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Priority</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Assignees</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 whitespace-nowrap">Due Date</th>
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
                            <div class="w-6 h-6 rounded-full bg-indigo-400 border-2 border-white flex items-center justify-center text-[10px] font-bold text-white" title="{{ $a->name }}">
                                {{ strtoupper(substr($a->name,0,1)) }}
                            </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap {{ $task->due_date?->isPast() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                        {{ $task->due_date?->format('M j, Y') ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">No tasks found.</td></tr>
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
