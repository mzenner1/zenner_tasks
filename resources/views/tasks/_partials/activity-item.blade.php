<div class="flex items-start gap-3 text-sm">
    @if($entry->user)
    <x-user-avatar :user="$entry->user" size="sm" class="mt-0.5" />
    @else
    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-500 flex-shrink-0 mt-0.5">S</div>
    @endif
    <div class="flex-1 min-w-0">
        <span class="font-medium text-gray-700">{{ $entry->user?->name ?? 'System' }}</span>
        <span class="text-gray-500"> {{ $entry->description() }}</span>
        <span class="text-xs text-gray-400 ml-2">{{ $entry->created_at->diffForHumans() }}</span>
    </div>
</div>
