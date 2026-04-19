<x-app-layout :title="'Edit: ' . $status->name">
<div class="max-w-lg space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('projects.statuses.index', $project) }}" class="hover:text-indigo-600">Statuses</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Edit Status</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Edit Status</h1>

    <form method="POST" action="{{ route('projects.statuses.update', [$project, $status]) }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $status->name) }}" required autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Color <span class="text-red-500">*</span></label>
            <input type="color" name="color" value="{{ old('color', $status->color) }}"
                   class="w-10 h-10 rounded cursor-pointer border border-gray-300">
        </div>
        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="is_default" value="1" {{ old('is_default', $status->is_default) ? 'checked':'' }}
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Default status for new tasks
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="is_closed" value="1" {{ old('is_closed', $status->is_closed) ? 'checked':'' }}
                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                Treat as "closed/done"
            </label>
        </div>
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('projects.statuses.index', $project) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
</x-app-layout>
