<x-app-layout title="Archived Projects">
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('projects.index') }}" class="hover:text-gray-700">Projects</a>
                <span>&rsaquo;</span>
                <span>Archived</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Archived Projects</h1>
        </div>
        <a href="{{ route('projects.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-300 hover:border-gray-400 px-3 py-2 rounded-lg transition">
            &larr; Back to Projects
        </a>
    </div>

    @if($projects->count())
    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">

        @foreach($projects as $project)
        <div class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50 transition">

            {{-- Project name + description (always visible) --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                          style="background-color: {{ $project->color ?? '#6366f1' }}"></span>
                    <a href="{{ route('projects.show', $project) }}"
                       class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate">{{ $project->name }}</a>
                </div>
                @if($project->description)
                <p class="text-xs text-gray-400 mt-0.5 ml-5 truncate">{{ $project->description }}</p>
                @endif

                {{-- Mobile-only meta row --}}
                <div class="flex items-center gap-2 mt-1 ml-5 sm:hidden text-xs text-gray-500">
                    <span>{{ $project->tasks_count }} tasks</span>
                    <span>&middot;</span>
                    <span>{{ $project->creator?->name ?? '—' }}</span>
                    <span>&middot;</span>
                    <span>{{ $project->created_at->format('M j, Y') }}</span>
                </div>
            </div>

            {{-- Tasks count — hidden on mobile --}}
            <span class="hidden sm:block text-sm text-gray-500 w-12 text-center flex-shrink-0"
                  title="{{ $project->tasks_count }} tasks">{{ $project->tasks_count }}</span>

            {{-- Member avatars — hidden on mobile --}}
            <div class="hidden sm:flex items-center -space-x-1 flex-shrink-0 w-24">
                @foreach($project->members->take(4) as $member)
                <x-user-avatar :user="$member" size="sm" class="border-2 border-white" />
                @endforeach
                @if($project->members->count() > 4)
                <span class="text-xs text-gray-400 pl-2">+{{ $project->members->count() - 4 }}</span>
                @endif
            </div>

            {{-- Created by — hidden on mobile --}}
            <span class="hidden sm:block text-sm text-gray-500 flex-shrink-0 w-32 truncate">{{ $project->creator?->name ?? '—' }}</span>

            {{-- Created date — hidden on mobile --}}
            <span class="hidden sm:block text-xs text-gray-400 flex-shrink-0 w-24">{{ $project->created_at->format('M j, Y') }}</span>

            {{-- Unarchive button (always visible) --}}
            <form method="POST" action="{{ route('admin.projects.unarchive', $project) }}" class="flex-shrink-0">
                @csrf
                <button type="submit"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800 hover:underline whitespace-nowrap">
                    Unarchive
                </button>
            </form>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8M10 12v4m4-4v4"/>
        </svg>
        <p class="text-gray-400 text-sm">No archived projects.</p>
    </div>
    @endif

</div>
</x-app-layout>
