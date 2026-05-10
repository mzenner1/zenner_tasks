<x-app-layout title="Users">
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">All Users</h1>
        <span class="text-sm text-gray-500">{{ $users->total() }} total</span>
    </div>

    {{-- ── Invite User Form ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">
        <button @click="open = !open"
                class="flex items-center gap-2 text-sm font-semibold text-purple-600 hover:text-purple-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span x-text="open ? 'Cancel' : 'Invite New User'"></span>
        </button>

        <div x-show="open" x-transition class="mt-4 pt-4 border-t border-gray-100">
            <form method="POST" action="{{ route('admin.users.invite') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="user@example.com"
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-purple-500 focus:border-purple-500" />
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="w-44">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                    <select name="role" required
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        <option value="client"      {{ old('role', 'client') === 'client'      ? 'selected' : '' }}>Client</option>
                        <option value="member"      {{ old('role')           === 'member'      ? 'selected' : '' }}>Member</option>
                        <option value="admin"       {{ old('role')           === 'admin'       ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role')           === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition">
                    Send Invitation
                </button>
            </form>
        </div>
    </div>

    {{-- ── Users Table ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Email</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Role</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Joined</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <x-user-avatar :user="$user" size="sm" />
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="font-medium text-gray-900 hover:text-purple-600">{{ $user->name }}</a>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @php $roleColors = ['super_admin'=>'bg-purple-100 text-purple-700','admin'=>'bg-indigo-100 text-indigo-700','member'=>'bg-blue-100 text-blue-700','client'=>'bg-gray-100 text-gray-600']; @endphp
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$user->role] ?? '' }}">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $user->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="text-xs text-gray-400 hover:text-purple-600 mr-3">Edit</a>
                        @if(auth()->user()->role === 'super_admin' && $user->id !== auth()->id() && $user->role !== 'super_admin')
                        <form method="POST" action="{{ route('admin.impersonate', $user) }}" class="inline mr-3">
                            @csrf
                            <button type="submit"
                                    class="text-xs text-blue-400 hover:text-blue-600">Login as</button>
                        </form>
                        @endif
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete {{ $user->name }}?')"
                                    class="text-xs text-red-400 hover:text-red-600">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div>{{ $users->links() }}</div>
    @endif

    {{-- ── Role Permissions reference ──────────────────────────────── --}}
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
                        ['Delete tasks',                     true,  true,  false],
                        ['Change task due date',             true,  true,  false],
                        ['Change task status',               true,  true,  true ],
                        ['Assign tasks to others',           true,  true,  true ],
                        ['View internal comments',           true,  true,  false],
                        ['Post internal comments',           true,  true,  false],
                        ['Post public comments',             true,  true,  true ],
                        ['Manage project settings',          true,  false, false],
                        ['Manage statuses & workflow',       true,  false, false],
                        ['Manage tags',                      true,  true,  false],
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
        <p class="text-xs text-gray-400 mt-4">* Super Admins have full access across all projects regardless of membership.</p>
    </div>
</div>
</x-app-layout>
