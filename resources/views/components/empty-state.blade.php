@props([
    'title' => 'Belum ada data',
    'description' => 'Data untuk modul atau bagian ini belum tersedia saat ini.',
])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/50']) }}>
    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
        @if(isset($icon))
            {{ $icon }}
        @else
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
        @endif
    </div>

    <h3 class="text-sm font-semibold text-slate-700">{{ $title }}</h3>
    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">{{ $description }}</p>

    @if(isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
