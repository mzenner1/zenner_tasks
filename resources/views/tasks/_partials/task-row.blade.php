<div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
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

        {{-- Mobile-only meta row --}}
        <div class="flex items-center gap-2 mt-1 sm:hidden">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white"
                  style="background-color: {{ $task->status->color }}">
                {{ $task->status->name }}
            </span>
            @if($task->due_date)
            <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                {{ $task->due_date->format('M j') }}
            </span>
            @endif
        </div>
    </div>

    {{-- Status badge — hidden on mobile --}}
    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium text-white flex-shrink-0"
          style="background-color: {{ $task->status->color }}">
        {{ $task->status->name }}
    </span>

    {{-- Assignee avatars — hidden on mobile --}}
    <div class="hidden sm:flex -space-x-1 flex-shrink-0">
        @foreach($task->assignees->take(3) as $assignee)
        <x-user-avatar :user="$assignee" size="sm" class="border-2 border-white" />
        @endforeach
    </div>

    {{-- Due date — hidden on mobile --}}
    @if($task->due_date)
    <span class="hidden sm:block text-xs flex-shrink-0 {{ $task->due_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
        {{ $task->due_date->format('M j') }}
    </span>
    @endif
</div>
