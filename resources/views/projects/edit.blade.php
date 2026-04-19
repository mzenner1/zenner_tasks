<x-app-layout title="Edit Project">
<div class="max-w-xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Edit Project</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.members.index', $project) }}" class="text-sm text-gray-500 hover:text-indigo-600">Manage Members</a>
            <a href="{{ route('projects.statuses.index', $project) }}" class="text-sm text-gray-500 hover:text-indigo-600">Manage Statuses</a>
        </div>
    </div>

    <form method="POST" action="{{ route('projects.update', $project) }}" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Project Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
            <div class="flex items-center gap-3">
                <input type="color" name="color" value="{{ old('color', $project->color ?? '#6366f1') }}"
                       class="w-10 h-10 rounded cursor-pointer border border-gray-300">
                <span class="text-xs text-gray-500">Sidebar dot indicator color</span>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            @can('archive', $project)
            <form method="POST" action="{{ route('projects.archive', $project) }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-red-500 hover:text-red-700"
                        onclick="return confirm('Archive this project?')">Archive Project</button>
            </form>
            @else
            <div></div>
            @endcan

            <div class="flex items-center gap-3">
                <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>
</x-app-layout>
