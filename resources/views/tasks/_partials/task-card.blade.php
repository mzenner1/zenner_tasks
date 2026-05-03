<div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm cursor-grab hover:shadow-md transition group"
     data-task-id="{{ $task->id }}">
    <div class="flex items-start gap-1">
        <a href="{{ route('projects.tasks.show', [$project, $task]) }}"
           class="block text-sm font-medium text-gray-900 group-hover:text-indigo-600 leading-snug">
            <span class="font-mono text-indigo-500 font-semibold">{{ $task->task_number_label }}</span>
            <span class="text-gray-400 mx-0.5">–</span>{{ $task->title }}
        </a>
    </div>

    <div class="flex items-center justify-between mt-2">
        {{-- Priority --}}
        @php $pColors = ['low'=>'bg-gray-100 text-gray-500','normal'=>'bg-blue-100 text-blue-600','high'=>'bg-orange-100 text-orange-600','urgent'=>'bg-red-100 text-red-600']; @endphp
        <span class="text-[11px] font-medium px-1.5 py-0.5 rounded {{ $pColors[$task->priority] ?? '' }}">
            {{ ucfirst($task->priority) }}
        </span>

        {{-- Due date --}}
        @if($task->due_date)
        <span class="text-[11px] {{ $task->due_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
            {{ $task->due_date->format('M j') }}
        </span>
        @endif
    </div>

    {{-- Assignee avatars --}}
    @if($task->assignees->count())
    <div class="flex -space-x-1 mt-2">
        @foreach($task->assignees->take(4) as $a)
        <x-user-avatar :user="$a" size="xs" class="border border-white" />
        @endforeach
    </div>
    @endif

    {{-- Tags --}}
    @if($task->relationLoaded('tags') && $task->tags->isNotEmpty())
    <div class="flex flex-wrap gap-1 mt-2">
        @foreach($task->tags as $tag)
        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium text-white"
              style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
        @endforeach
    </div>
    @endif
</div>
