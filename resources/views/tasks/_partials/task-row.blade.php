<div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition">
    {{-- Priority indicator --}}
    <div class="w-1 self-stretch rounded-full flex-shrink-0
        {{ $task->priority === 'urgent' ? 'bg-red-500' : ($task->priority === 'high' ? 'bg-orange-400' : ($task->priority === 'normal' ? 'bg-blue-400' : 'bg-gray-300')) }}">
    </div>

    {{-- Title + project --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}"
               class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate">
                {{ $task->title }}
            </a>
            <span class="text-xs text-gray-400 font-mono flex-shrink-0">{{ $task->task_number_label }}</span>
        </div>
        <span class="text-xs text-gray-400">{{ $task->project->name }}</span>
    </div>

    {{-- Status badge --}}
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium text-white flex-shrink-0"
          style="background-color: {{ $task->status->color }}">
        {{ $task->status->name }}
    </span>

    {{-- Assignee avatars --}}
    <div class="flex -space-x-1 flex-shrink-0">
        @foreach($task->assignees->take(3) as $assignee)
        <div class="w-6 h-6 rounded-full bg-indigo-400 border-2 border-white flex items-center justify-center text-[10px] font-bold text-white"
             title="{{ $assignee->name }}">
            {{ strtoupper(substr($assignee->name, 0, 1)) }}
        </div>
        @endforeach
    </div>

    {{-- Due date --}}
    @if($task->due_date)
    <span class="text-xs flex-shrink-0 {{ $task->due_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
        {{ $task->due_date->format('M j') }}
    </span>
    @endif
</div>
