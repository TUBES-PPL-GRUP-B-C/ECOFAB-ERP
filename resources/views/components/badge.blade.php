@props([
    'status' => 'default',
    'label' => null,
])

@php
    $normalized = strtolower(trim((string)$status));

    $config = match($normalized) {
        'selesai', 'sukses', 'aktif', 'terbit', 'disetujui' => [
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-500',
            'defaultLabel' => ucfirst($status),
        ],
        'proses', 'berjalan', 'dalam proses', 'siap verifikasi' => [
            'class' => 'bg-sky-50 text-sky-700 border-sky-200',
            'dot' => 'bg-sky-500',
            'defaultLabel' => ucfirst($status),
        ],
        'draft', 'konsep', 'antrean', 'tidak_aktif', 'ditarik' => [
            'class' => 'bg-slate-100 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-400',
            'defaultLabel' => ucfirst($status),
        ],
        'kritis', 'danger', 'ditolak', 'batal' => [
            'class' => 'bg-rose-50 text-rose-700 border-rose-200',
            'dot' => 'bg-rose-500',
            'defaultLabel' => ucfirst($status),
        ],
        'menipis', 'menunggu', 'warning', 'perlu approval' => [
            'class' => 'bg-amber-50 text-amber-800 border-amber-200',
            'dot' => 'bg-amber-500',
            'defaultLabel' => ucfirst($status),
        ],
        default => [
            'class' => 'bg-slate-50 text-slate-600 border-slate-200',
            'dot' => 'bg-slate-400',
            'defaultLabel' => ucfirst($status),
        ],
    };

    $displayLabel = $label ?? $slot->isNotEmpty() ? $slot : $config['defaultLabel'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $config['class']]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }} mr-1.5"></span>
    {{ $displayLabel }}
</span>
