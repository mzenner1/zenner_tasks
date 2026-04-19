<x-app-layout title="Notifications">
<div class="max-w-2xl space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        @if($notifications->total() > 0)
        <form method="POST" action="{{ route('notifications.read', 'all') }}">
            @csrf
            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">Mark all as read</button>
        </form>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        @forelse($notifications as $notification)
        <div class="flex items-start gap-4 px-5 py-4 {{ is_null($notification->read_at) ? 'bg-indigo-50' : '' }}">
            <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0 {{ is_null($notification->read_at) ? 'bg-indigo-500' : 'bg-transparent' }}"></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800">
                    {{ $notification->data['message'] ?? 'You have a new notification.' }}
                </p>
                @if(!empty($notification->data['url']))
                <a href="{{ $notification->data['url'] }}" class="text-xs text-indigo-600 hover:underline mt-0.5 block">View →</a>
                @endif
                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
            </div>
            @if(is_null($notification->read_at))
            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                @csrf
                <button type="submit" class="text-xs text-gray-400 hover:text-indigo-600 flex-shrink-0">Mark read</button>
            </form>
            @endif
        </div>
        @empty
        <div class="px-5 py-12 text-center text-gray-400 text-sm">
            You have no notifications.
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div>{{ $notifications->links() }}</div>
    @endif

</div>
</x-app-layout>
