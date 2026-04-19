<x-app-layout :title="$project->name . ' — Board'">
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full" style="background-color: {{ $project->color ?? '#6366f1' }}"></span>
            <h1 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.show', $project) }}"
               class="px-3 py-1.5 text-xs rounded-lg border border-gray-300 bg-white text-gray-600 hover:border-indigo-400 transition">
                ☰ List
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'board']) }}"
               class="px-3 py-1.5 text-xs rounded-lg border bg-indigo-600 text-white border-indigo-600">
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

    {{-- Kanban columns --}}
    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach($statuses as $status)
        <div class="flex-shrink-0 w-72">
            {{-- Column header --}}
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $status->color }}"></span>
                <span class="font-semibold text-sm text-gray-700">{{ $status->name }}</span>
                <span class="ml-auto bg-gray-200 text-gray-600 text-xs font-medium px-2 py-0.5 rounded-full">{{ $status->tasks->count() }}</span>
            </div>

            {{-- Task cards drop zone --}}
            <div class="kanban-column space-y-2 min-h-24 rounded-xl p-2 bg-gray-100"
                 data-status-id="{{ $status->id }}">
                @foreach($status->tasks as $task)
                @include('tasks._partials.task-card', ['task' => $task, 'project' => $project])
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.kanban-column').forEach(function (column) {
        Sortable.create(column, {
            group: 'tasks',
            animation: 150,
            ghostClass: 'opacity-40',
            onEnd: function (evt) {
                fetch('/tasks/' + evt.item.dataset.taskId + '/move', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status_id:  parseInt(evt.to.dataset.statusId),
                        sort_order: evt.newIndex
                    })
                });
            }
        });
    });
});
</script>
</x-app-layout>
