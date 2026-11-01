@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col md:flex-row md:items-center md:justify-between pb-6 mb-6 border-b border-slate-200/80 gap-4']) }}>
    <div class="flex items-start space-x-3">
        @if($icon)
            <div class="w-10 h-10 rounded-xl bg-brand-900 text-white flex items-center justify-center shrink-0 shadow-xs">
                {{ $icon }}
            </div>
        @endif
        <div>
            <div class="flex items-center space-x-2.5 flex-wrap">
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
                @if($badge)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        {{ $badge }}
                    </span>
                @endif
            </div>
            @if($subtitle)
                <p class="text-xs md:text-sm text-slate-500 mt-1 flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    </div>

    @if(isset($actions))
        <div class="flex items-center space-x-3 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
