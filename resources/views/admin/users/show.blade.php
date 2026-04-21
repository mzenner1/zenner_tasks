<x-app-layout title="{{ $user->name }}">
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-600">Users</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">{{ $user->name }}</span>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-indigo-400 flex items-center justify-center text-2xl font-bold text-white">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
            </div>
            <div class="ml-auto">
                @php $roleColors = ['super_admin'=>'bg-purple-100 text-purple-700','admin'=>'bg-indigo-100 text-indigo-700','member'=>'bg-blue-100 text-blue-700','client'=>'bg-gray-100 text-gray-600']; @endphp
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $roleColors[$user->role] ?? '' }}">
                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100 text-sm">
            <div><span class="text-gray-500">Joined:</span> <span class="font-medium">{{ $user->created_at->format('M j, Y') }}</span></div>
            <div><span class="text-gray-500">Last active:</span> <span class="font-medium">{{ $user->last_active_at?->diffForHumans() ?? 'Never' }}</span></div>
            <div><span class="text-gray-500">Projects:</span> <span class="font-medium">{{ $user->projects->count() }}</span></div>
            <div><span class="text-gray-500">Verified:</span> <span class="font-medium">{{ $user->email_verified_at ? 'Yes' : 'No' }}</span></div>
        </div>
    </div>

    {{-- Projects --}}
    @if($user->projects->count())
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Projects</h2>
        <div class="space-y-2">
            @foreach($user->projects as $project)
            <div class="flex items-center gap-3 text-sm">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $project->color ?? '#6366f1' }}"></span>
                <a href="{{ route('projects.show', $project) }}" class="text-gray-700 hover:text-indigo-600">{{ $project->name }}</a>
                <span class="text-xs text-gray-400 ml-auto">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.edit', $user) }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            Edit User
        </a>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Users</a>
    </div>
</div>
</x-app-layout>
