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

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">{{ session('error') }}</div>
    @endif

    {{-- ── Add member ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-5" x-data="{ mode: '{{ $existingUsers->isEmpty() ? 'email' : 'existing' }}' }">
        <h2 class="text-sm font-semibold text-gray-700">Add a Member</h2>

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
                        {{ $u->name }} ({{ $u->email }}) — {{ ucfirst($u->role) }}
                    </option>
                    @endforeach
                </select>
                @error('user_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex-shrink-0">
                Invite
            </button>
        </form>

        <p class="text-xs text-gray-400">The user's site-wide role determines their permissions in this project.</p>
    </div>

    {{-- ── Current members ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        <div class="px-5 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Current Members ({{ $members->count() }})</h2>
        </div>
        @forelse($members as $member)
        @php
            $roleColors = [
                'super_admin' => 'bg-purple-100 text-purple-700',
                'admin'       => 'bg-indigo-100 text-indigo-700',
                'member'      => 'bg-blue-100 text-blue-700',
                'client'      => 'bg-gray-100 text-gray-600',
            ];
            $roleLabel = ucfirst(str_replace('_', ' ', $member->role));
            $roleClass = $roleColors[$member->role] ?? 'bg-gray-100 text-gray-600';
        @endphp
        <div class="flex items-center gap-4 px-5 py-4">
            <x-user-avatar :user="$member" size="md" />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                <p class="text-xs text-gray-500">{{ $member->email }}</p>
            </div>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $roleClass }}">{{ $roleLabel }}</span>
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
                        ['Assign tasks to others',           true,  true,  true ],
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
        <p class="text-xs text-gray-400 mt-4">* Super Admins and Admins have full access across all projects regardless of membership.</p>
    </div>

</div>
</x-app-layout>
