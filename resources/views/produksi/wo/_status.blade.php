@php
    $statusClasses = [
        'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
        'in_progress' => 'bg-sky-50 text-sky-800 border-sky-200',
        'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200',
    ];
@endphp
<span class="inline-flex whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
    {{ $statuses[$status] ?? $status }}
</span>
