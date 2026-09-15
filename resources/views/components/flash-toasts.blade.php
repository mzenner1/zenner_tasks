@php
    $styles = [
        'success' => ['bg-white border-green-200', 'text-green-600', 'M5 13l4 4L19 7'],
        'error'   => ['bg-white border-red-200',   'text-red-600',   'M6 18L18 6M6 6l12 12'],
        'warning' => ['bg-white border-amber-200', 'text-amber-600', 'M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
        'info'    => ['bg-white border-blue-200',  'text-blue-600',  'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];

    $toasts = collect(['success', 'error', 'warning', 'info'])
        ->map(fn ($type) => ['type' => $type, 'message' => session($type)])
        ->filter(fn ($toast) => filled($toast['message']) && is_string($toast['message']))
        ->values();
@endphp

@if($toasts->isNotEmpty())
<div class="fixed bottom-20 right-4 sm:right-6 z-[60] flex flex-col items-end gap-3 w-[calc(100%-2rem)] sm:w-auto sm:max-w-sm pointer-events-none"
     aria-live="polite">
    @foreach($toasts as $index => $toast)
    @php([$box, $accent, $icon] = $styles[$toast['type']])
    <div
        x-data="{
            show: false,
            timer: null,
            duration: {{ $toast['type'] === 'error' ? 8000 : 5000 }},
            start() { this.stop(); this.timer = setTimeout(() => this.show = false, this.duration); },
            stop() { clearTimeout(this.timer); this.timer = null; },
        }"
        x-init="$nextTick(() => setTimeout(() => { show = true; start(); }, {{ $index * 120 }}))"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 sm:translate-x-4 sm:translate-y-0"
        x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-4"
        @mouseenter="stop()"
        @mouseleave="start()"
        @focusin="stop()"
        @focusout="start()"
        role="{{ $toast['type'] === 'error' ? 'alert' : 'status' }}"
        class="pointer-events-auto w-full flex items-start gap-3 {{ $box }} border shadow-lg rounded-lg px-4 py-3 text-sm text-gray-800"
        style="display: none;">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $accent }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
        </svg>
        <span class="flex-1 break-words">{{ $toast['message'] }}</span>
        <button type="button" @click="show = false"
                class="flex-shrink-0 self-center leading-none text-gray-400 hover:text-gray-700 transition"
                aria-label="Dismiss notification">&#x2715;</button>
    </div>
    @endforeach
</div>
@endif
