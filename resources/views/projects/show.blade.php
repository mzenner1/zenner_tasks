<x-app-layout :title="$project->name">
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full" style="background-color: {{ $project->color ?? '#6366f1' }}"></span>
            <h1 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            {{-- View toggle --}}
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

    {{-- Filters --}}
    <div x-data="{ open: false }" class="bg-white rounded-xl border border-gray-200">
        <form method="GET" action="{{ route('projects.show', $project) }}" class="flex flex-wrap items-center gap-3 p-3">
            <input type="hidden" name="view" value="{{ request('view', 'list') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks…"
                   class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48">

            <select name="status_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                @endforeach
            </select>

            <select name="priority" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Priorities</option>
                @foreach(['low','normal','high','urgent'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>

            <select name="assignee" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Assignees</option>
                @foreach($members as $member)
                <option value="{{ $member->id }}" {{ request('assignee') === $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded-lg transition">Filter</button>
            @if(request()->hasAny(['search','status_id','priority','assignee']))
            <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>
    </div>

    {{-- Task List --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
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
                @forelse($statuses->flatMap->tasks as $task)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <a href="{{ route('projects.tasks.show', [$project, $task]) }}"
                           class="font-medium text-gray-900 hover:text-indigo-600"><span class="font-mono text-indigo-500 font-semibold">{{ $task->task_number_label }}</span><span class="text-gray-400 mx-0.5">–</span>{{ $task->title }}</a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium text-white"
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
</x-app-layout>
