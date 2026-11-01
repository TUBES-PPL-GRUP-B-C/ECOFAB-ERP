@props([
    'title',
    'value',
    'badge' => null,
    'badgeType' => 'success', // success, info, warning, danger
    'subtext' => null,
    'icon' => null,
    'accentColor' => 'bg-brand-800',
])

@php
    $badgeClasses = match($badgeType) {
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'info' => 'bg-sky-50 text-sky-700 border-sky-200',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-slate-50 text-slate-700 border-slate-200',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-xs border border-slate-100 p-5 relative overflow-hidden transition-all duration-200 hover:shadow-md hover:border-slate-200']) }}>
    <div class="absolute top-0 left-0 right-0 h-1 {{ $accentColor }}"></div>

    <div class="flex items-start justify-between">
        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ $title }}</span>
        @if($icon)
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-3 flex items-baseline space-x-2">
        <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</span>
        @if($badge)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClasses }}">
                {{ $badge }}
            </span>
        @endif
    </div>

    @if($subtext || isset($chips))
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            @if(isset($chips))
                <div class="flex items-center space-x-1.5 flex-wrap">
                    {{ $chips }}
                </div>
            @endif
            @if($subtext)
                <span>{{ $subtext }}</span>
            @endif
        </div>
    @endif
</div>
