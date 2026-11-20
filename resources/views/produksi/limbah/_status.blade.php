@php
    $statusClasses = [
        'available' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'listed' => 'bg-sky-50 text-sky-800 border-sky-200',
        'sold' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];
@endphp
<span class="inline-flex whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
    {{ $statuses[$status] ?? $status }}
</span>
