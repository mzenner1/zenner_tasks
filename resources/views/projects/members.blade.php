<x-app-layout :title="$project->name . ' — Members'">
<div class="max-w-3xl space-y-6">

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

    {{-- ── Add member ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-5" x-data="{ mode: '{{ $existingUsers->isEmpty() ? 'email' : 'existing' }}' }">
        <h2 class="text-sm font-semibold text-gray-700">Add a Member</h2>

        {{-- Mode toggle --}}
        @if($existingUsers->isNotEmpty())
        <div class="flex gap-1 p-1 bg-gray-100 rounded-lg w-fit text-xs font-medium">
            <button type="button" @click="mode = 'existing'"
                    :class="mode === 'existing' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 rounded-md transition">
                Pick existing user
            </button>
            <button type="button" @click="mode = 'email'"
                    :class="mode === 'email' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 rounded-md transition">
                Invite by email
            </button>
        </div>
        @endif

        {{-- Existing user form --}}
        @if($existingUsers->isNotEmpty())
        <form method="POST" action="{{ route('projects.members.store', $project) }}"
              x-show="mode === 'existing'" class="flex items-end gap-3">
            @csrf
            <input type="hidden" name="_mode" value="existing">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">User</label>
                <select name="user_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— select a user —</option>
                    @foreach($existingUsers as $u)
                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->email }})
                    </option>
                    @endforeach
                </select>
                @error('user_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
                Add
            </button>
        </form>
        @endif

        {{-- Email invite form --}}
        <form method="POST" action="{{ route('projects.members.store', $project) }}"
              x-show="mode === 'email'" {{ $existingUsers->isEmpty() ? '' : 'style="display:none"' }}
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
                Invite
            </button>
        </form>
    </div>

    {{-- ── Current members ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        <div class="px-5 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Current Members ({{ $members->count() }})</h2>
        </div>
        @forelse($members as $member)
        <div class="flex items-center gap-4 px-5 py-4">
            <div class="w-9 h-9 rounded-full bg-indigo-400 flex items-center justify-center font-bold text-white flex-shrink-0">
                {{ strtoupper(substr($member->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                <p class="text-xs text-gray-500">{{ $member->email }}</p>
            </div>
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

    {{-- ── Role reference ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Role Permissions</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="pb-2 pr-4 font-semibold text-gray-600 w-2/5">Permission</th>
                        <th class="pb-2 px-4 text-center font-semibold text-gray-600">Admin</th>
                        <th class="pb-2 px-4 text-center font-semibold text-gray-600">Member</th>
                        <th class="pb-2 pl-4 text-center font-semibold text-gray-600">Client</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                    $yes = '<span class="text-green-600 font-bold text-sm">✓</span>';
                    $no  = '<span class="text-gray-300 text-sm">—</span>';
                    $rows = [
                        ['View project & tasks',             true,  true,  true ],
                        ['Create tasks',                     true,  true,  true ],
                        ['Edit any task',                    true,  true,  false],
                        ['Edit own tasks only',              false, false, true ],
                        ['Delete tasks',                     true,  false, false],
                        ['Change task status',               true,  true,  true ],
                        ['Assign tasks to others',           true,  true,  false],
                        ['View internal comments',           true,  true,  false],
                        ['Post internal comments',           true,  true,  false],
                        ['Post public comments',             true,  true,  true ],
                        ['Manage project settings',          true,  false, false],
                        ['Manage statuses & workflow',       true,  false, false],
                        ['Add / remove project members',     true,  false, false],
                    ];
                    @endphp
                    @foreach($rows as [$label, $admin, $member, $client])
                    <tr>
                        <td class="py-2 pr-4 text-gray-700">{{ $label }}</td>
                        <td class="py-2 px-4 text-center">{!! $admin  ? $yes : $no !!}</td>
                        <td class="py-2 px-4 text-center">{!! $member ? $yes : $no !!}</td>
                        <td class="py-2 pl-4 text-center">{!! $client ? $yes : $no !!}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 mt-4">* Super Admins have full access across all projects regardless of project role.</p>
    </div>

</div>
</x-app-layout>
