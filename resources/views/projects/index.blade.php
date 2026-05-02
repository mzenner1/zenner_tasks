<x-app-layout title="Projects">
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.projects.archived') }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-300 hover:border-gray-400 px-3 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8M10 12v4m4-4v4"/></svg>
                Archived Projects
            </a>
            @endif
            @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">New Project</span>
            </a>
            @endcan
        </div>
    </div>

    @if($projects->count())
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($projects as $project)
        <a href="{{ route('projects.show', $project) }}"
           class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 hover:shadow-md hover:border-indigo-200 transition group">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-3 h-3 rounded-full flex-shrink-0 mt-0.5" style="background-color: {{ $project->color ?? '#6366f1' }}"></span>
                    <h2 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition truncate">{{ $project->name }}</h2>
                </div>
                <span class="text-xs text-gray-400 flex-shrink-0">{{ $project->tasks_count }} tasks</span>
            </div>
            @if($project->description)
            <p class="text-sm text-gray-500 mt-2 ml-6 line-clamp-2">{{ $project->description }}</p>
            @endif
            <div class="flex items-center gap-2 mt-4 ml-6">
                @foreach($project->members->take(4) as $member)
                <x-user-avatar :user="$member" size="sm" class="border-2 border-white -ml-1 first:ml-0" />
                @endforeach
                @if($project->members->count() > 4)
                <span class="text-xs text-gray-400">+{{ $project->members->count() - 4 }}</span>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">No projects yet.</p>
        @can('create', App\Models\Project::class)
        <a href="{{ route('projects.create') }}" class="mt-3 inline-block text-indigo-600 text-sm hover:underline">Create your first project →</a>
        @endcan
    </div>
    @endif

</div>
</x-app-layout>
