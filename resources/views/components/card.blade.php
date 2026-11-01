@props([
    'title' => null,
    'subtitle' => null,
    'accent' => false,
    'accentColor' => 'bg-brand-800',
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-xs border border-slate-100 overflow-hidden relative']) }}>
    @if($accent)
        <div class="h-1 w-full {{ $accentColor }}"></div>
    @endif

    @if($title || $actions)
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-base font-semibold text-slate-800">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actions)
                <div class="flex items-center space-x-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
