<x-app-layout :title="'Edit: ' . $task->title">
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">{{ $project->name }}</a>
        <span>/</span>
        <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="hover:text-indigo-600 truncate max-w-xs">{{ $task->title }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Edit</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">Edit Task</h1>

    <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}"
          enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $task->title) }}" required autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
            @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-xs font-normal text-gray-400">(Markdown — drag &amp; drop images supported)</span></label>
            <textarea name="description"
                      data-easymde
                      data-image-upload-url="{{ route('attachments.image-upload') }}"
                      data-mention-url="{{ route('projects.members.search', $project) }}"
                      data-csrf="{{ csrf_token() }}">{{ old('description', $task->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ $task->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select name="priority"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(['low','normal','high','urgent'] as $p)
                    <option value="{{ $p }}" {{ old('priority', $task->priority) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            @can('assign', $task)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assignees</label>
                <div class="border border-gray-300 rounded-lg p-2 max-h-36 overflow-y-auto space-y-1">
                    @foreach($members as $member)
                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 px-1 py-0.5 rounded">
                        <input type="checkbox" name="assignees[]" value="{{ $member->id }}"
                               {{ $task->assignees->contains($member->id) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        {{ $member->name }}
                    </label>
                    @endforeach
                </div>
            </div>
            @endcan
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Add Attachments</label>
            <input type="file" name="attachments[]" multiple
                   class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:font-medium hover:file:bg-indigo-100">
            <p class="text-xs text-gray-400 mt-1">Attach any number of files (max 20 MB each).</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('projects.tasks.show', [$project, $task]) }}"
               class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
</x-app-layout>
