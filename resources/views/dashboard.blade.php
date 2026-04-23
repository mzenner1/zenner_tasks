<x-app-layout title="Dashboard">
<div class="space-y-8">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}</p>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Overdue</p>
            <p class="text-3xl font-bold text-red-600 mt-1">{{ $overdue->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Due This Week</p>
            <p class="text-3xl font-bold text-amber-600 mt-1">{{ $dueSoon->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Recently Updated</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $recentlyUpdated->count() }}</p>
        </div>
    </div>

    {{-- Overdue --}}
    @if($overdue->count())
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-red-600 mb-3">⚠ Overdue</h2>
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @foreach($overdue as $task)
                @include('tasks._partials.task-row', ['task' => $task])
            @endforeach
        </div>
    </div>
    @endif

    {{-- Due Soon --}}
    @if($dueSoon->count())
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-amber-600 mb-3">📅 Due This Week</h2>
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @foreach($dueSoon as $task)
                @include('tasks._partials.task-row', ['task' => $task])
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recently Updated --}}
    @if($recentlyUpdated->count())
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-indigo-600 mb-3">🕐 Recently Updated</h2>
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @foreach($recentlyUpdated as $task)
                @include('tasks._partials.task-row', ['task' => $task])
            @endforeach
        </div>
    </div>
    @endif

    @if(!$overdue->count() && !$dueSoon->count() && !$recentlyUpdated->count())
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">No tasks assigned to you yet.</p>
    </div>
    @endif

</div>
</x-app-layout>
