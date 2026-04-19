<x-app-layout title="Project Members">
<div class="max-w-2xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-gray-800 font-medium">Members</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Project Members</h1>
        </div>
        <a href="{{ route('projects.edit', $project) }}"
           class="text-sm text-gray-500 hover:text-indigo-600">⚙ Project Settings</a>
    </div>

    {{-- Invite form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Invite a Member</h2>
        <form method="POST" action="{{ route('projects.members.store', $project) }}"
              class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       placeholder="name@example.com"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <select name="project_role"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="member">Member</option>
                    <option value="admin">Admin</option>
                    <option value="client">Client</option>
                </select>
            </div>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex-shrink-0">
                Add / Invite
            </button>
        </form>
    </div>

    {{-- Members list --}}
    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        @forelse($members as $member)
        <div class="flex items-center gap-4 px-5 py-4">
            <div class="w-9 h-9 rounded-full bg-indigo-400 flex items-center justify-center font-bold text-white flex-shrink-0">
                {{ strtoupper(substr($member->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                <p class="text-xs text-gray-500">{{ $member->email }}</p>
            </div>

            {{-- Role update --}}
            <form method="POST" action="{{ route('projects.members.update', [$project, $member]) }}"
                  class="flex items-center gap-2">
                @csrf @method('PUT')
                <select name="project_role" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(['admin','member','client'] as $role)
                    <option value="{{ $role }}" {{ $member->pivot->project_role === $role ? 'selected' : '' }}>
                        {{ ucfirst($role) }}
                    </option>
                    @endforeach
                </select>
            </form>

            {{-- Remove --}}
            @if($member->id !== auth()->id())
            <form method="POST" action="{{ route('projects.members.destroy', [$project, $member]) }}">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Remove {{ $member->name }} from this project?')"
                        class="text-xs text-red-400 hover:text-red-600 transition">Remove</button>
            </form>
            @endif
        </div>
        @empty
        <div class="px-5 py-10 text-center text-gray-400 text-sm">No members yet.</div>
        @endforelse
    </div>

</div>
</x-app-layout>
