<x-app-layout title="Edit User">
<div class="max-w-lg space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-600">Users</a>
        <span>/</span>
        <a href="{{ route('admin.users.show', $user) }}" class="hover:text-indigo-600">{{ $user->name }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Edit</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>

    <form method="POST" action="{{ route('admin.users.update', $user) }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Global Role <span class="text-red-500">*</span></label>
            <select name="role"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach(['super_admin','admin','member','client'] as $role)
                <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
</x-app-layout>
