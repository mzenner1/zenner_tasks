<x-app-layout title="Statuses">
<div class="max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-gray-800 font-medium">Statuses</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Status Workflow</h1>
        </div>
        <a href="{{ route('projects.statuses.create', $project) }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Add Status
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100" id="status-list">
        @forelse($statuses as $status)
        <div class="flex items-center gap-4 px-5 py-4 group" data-id="{{ $status->id }}">
            {{-- Drag handle --}}
            <div class="cursor-grab text-gray-300 hover:text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
            </div>

            {{-- Color swatch --}}
            <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $status->color }}"></span>

            {{-- Name + badges --}}
            <div class="flex-1 flex items-center gap-2 min-w-0">
                <span class="text-sm font-medium text-gray-900">{{ $status->name }}</span>
                @if($status->is_default)
                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">Default</span>
                @endif
                @if($status->is_closed)
                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Closed</span>
                @endif
                <span class="text-xs text-gray-400">{{ $status->tasks_count }} tasks</span>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition">
                <a href="{{ route('projects.statuses.edit', [$project, $status]) }}"
                   class="text-xs text-gray-500 hover:text-indigo-600">Edit</a>
                @if($status->tasks_count === 0)
                <form method="POST" action="{{ route('projects.statuses.destroy', [$project, $status]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this status?')"
                            class="text-xs text-red-400 hover:text-red-600">Delete</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="px-5 py-10 text-center text-gray-400 text-sm">No statuses yet.</div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('status-list');
    if (!list) return;
    Sortable.create(list, {
        animation: 150,
        handle: '[data-id]',
        onEnd: function () {
            const items = [...list.querySelectorAll('[data-id]')].map((el, i) => ({
                id: parseInt(el.dataset.id),
                sort_order: i + 1
            }));
            fetch('{{ route('projects.statuses.reorder', $project) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ statuses: items })
            });
        }
    });
});
</script>
</x-app-layout>
