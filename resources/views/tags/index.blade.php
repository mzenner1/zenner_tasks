<x-app-layout title="Tags">
<div class="max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-gray-800 font-medium">Tags</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Project Tags</h1>
        </div>
    </div>

    {{-- Create tag form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Tag
        </button>
        <div x-show="open" x-transition class="mt-4">
            <form method="POST" action="{{ route('projects.tags.store', $project) }}"
                  class="flex items-end gap-3 flex-wrap">
                @csrf
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tag Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="50"
                           placeholder="e.g. Needs Design"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color" value="{{ old('color', '#6366f1') }}"
                               class="w-10 h-9 rounded-lg border border-gray-300 cursor-pointer p-0.5">
                        <div class="flex gap-1.5 flex-wrap">
                            @foreach(['#6366f1','#8b5cf6','#ec4899','#ef4444','#f97316','#eab308','#22c55e','#14b8a6','#3b82f6','#6b7280'] as $preset)
                            <button type="button"
                                    @click="$el.closest('form').querySelector('[name=color]').value = '{{ $preset }}'"
                                    class="w-5 h-5 rounded-full border-2 border-white shadow-sm hover:scale-110 transition"
                                    style="background-color: {{ $preset }}"
                                    title="{{ $preset }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Create Tag
                </button>
            </form>
        </div>
    </div>

    {{-- Tag list --}}
    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        @forelse($tags as $tag)
        <div class="flex items-center gap-4 px-5 py-4 group" x-data="{ editing: false }">
            {{-- Color swatch + name (view mode) --}}
            <div class="flex items-center gap-3 flex-1 min-w-0" x-show="!editing">
                <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $tag->color }}"></span>
                <span class="text-sm font-medium text-gray-900">{{ $tag->name }}</span>
                <span class="text-xs text-gray-400">{{ $tag->tasks_count }} {{ Str::plural('task', $tag->tasks_count) }}</span>
            </div>

            {{-- Edit form (edit mode) --}}
            <form method="POST" action="{{ route('projects.tags.update', [$project, $tag]) }}"
                  class="flex items-center gap-3 flex-1 flex-wrap" x-show="editing" x-cloak>
                @csrf @method('PUT')
                <input type="text" name="name" value="{{ $tag->name }}" required maxlength="50"
                       class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-40">
                <div class="flex items-center gap-2">
                    <input type="color" name="color" value="{{ $tag->color }}"
                           class="w-9 h-8 rounded border border-gray-300 cursor-pointer p-0.5">
                    <div class="flex gap-1 flex-wrap">
                        @foreach(['#6366f1','#8b5cf6','#ec4899','#ef4444','#f97316','#eab308','#22c55e','#14b8a6','#3b82f6','#6b7280'] as $preset)
                        <button type="button"
                                @click="$el.closest('form').querySelector('[name=color]').value = '{{ $preset }}'"
                                class="w-4 h-4 rounded-full border border-white shadow-sm hover:scale-110 transition"
                                style="background-color: {{ $preset }}"></button>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Save</button>
                <button type="button" @click="editing = false" class="text-xs text-gray-400 hover:text-gray-600">Cancel</button>
            </form>

            {{-- Actions --}}
            <div class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition" x-show="!editing">
                <button type="button" @click="editing = true"
                        class="text-xs text-gray-500 hover:text-indigo-600">Edit</button>
                <form method="POST" action="{{ route('projects.tags.destroy', [$project, $tag]) }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Delete tag \'{{ addslashes($tag->name) }}\'? It will be removed from all tasks.')"
                            class="text-xs text-red-400 hover:text-red-600">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-5 py-10 text-center text-gray-400 text-sm">No tags yet. Create your first tag above.</div>
        @endforelse
    </div>
</div>
</x-app-layout>
