@props([
    'user',
    'size' => 'md',   // xs | sm | md | lg
    'initial' => null, // override text shown when no avatar
])

@php
    $sizes = [
        'xs' => 'w-5 h-5 text-[10px]',
        'sm' => 'w-6 h-6 text-xs',
        'md' => 'w-8 h-8 text-sm',
        'lg' => 'w-12 h-12 text-lg',
    ];
    $cls = $sizes[$size] ?? $sizes['md'];

    $letter = $initial ?? strtoupper(substr($user->name, 0, 1));
@endphp

@if($user->avatar)
    <img
        src="{{ Storage::url($user->avatar) }}"
        alt="{{ $user->name }}"
        title="{{ $user->name }}"
        {{ $attributes->merge(['class' => $cls . ' rounded-full object-cover flex-shrink-0']) }}
    >
@else
    <div
        title="{{ $user->name }}"
        {{ $attributes->merge(['class' => $cls . ' rounded-full bg-indigo-500 flex items-center justify-center font-bold text-white flex-shrink-0']) }}
    >{{ $letter }}</div>
@endif
