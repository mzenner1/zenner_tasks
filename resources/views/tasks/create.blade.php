<x-app-layout :title="'New Task — ' . $project->name">
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">{{ $project->name }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">New Task</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">New Task</h1>

    <form method="POST" action="{{ route('projects.tasks.store', $project) }}"
          enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
            @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-xs font-normal text-gray-400">(Markdown — drag &amp; drop images supported)</span></label>
            <textarea name="description"
                      data-easymde
                      data-image-upload-url="{{ route('attachments.image-upload') }}"
                      data-mention-url="{{ route('projects.members.search', $project) }}"
                      data-csrf="{{ csrf_token() }}">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ $status->is_default ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select name="priority"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(['low','normal','high','urgent'] as $p)
                    <option value="{{ $p }}" {{ old('priority','normal') === $p ? 'selected':'' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assignees</label>
                <div class="border border-gray-300 rounded-lg p-2 max-h-32 overflow-y-auto space-y-1">
                    @foreach($members as $member)
                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 px-1 py-0.5 rounded">
                        <input type="checkbox" name="assignees[]" value="{{ $member->id }}"
                               {{ in_array($member->id, old('assignees',[])) ? 'checked':'' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        {{ $member->name }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        @if($tags->isNotEmpty())
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
            <div class="flex flex-wrap gap-2">
                @foreach($tags as $tag)
                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                           {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                           class="sr-only peer">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium text-white opacity-40 peer-checked:opacity-100 transition ring-2 ring-transparent peer-checked:ring-offset-1 cursor-pointer"
                          style="background-color: {{ $tag->color }}; --ring-color: {{ $tag->color }}"
                          :style="'ring-color: ' + '{{ $tag->color }}'">
                        {{ $tag->name }}
                    </span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Attachments</label>
            <input type="file" name="attachments[]" multiple
                   class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:font-medium hover:file:bg-indigo-100">
            <p class="text-xs text-gray-400 mt-1">Attach any number of files (max 20 MB each).</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            <button type="submit" name="_action" value="create_another"
                    class="bg-white hover:bg-gray-50 text-indigo-600 text-sm font-medium px-5 py-2 rounded-lg border border-indigo-300 transition">
                Save &amp; Create Another
            </button>
            <button type="submit" name="_action" value="create"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                Create Task
            </button>
        </div>
    </form>
</div>
</x-app-layout>
