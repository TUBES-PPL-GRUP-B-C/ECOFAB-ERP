@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Mutasi & Pergerakan Stok (FR-11)"
        subtitle="Riwayat audit mutasi transfer antar-gudang dan penyesuaian stok opname fisik"
    >
        @can('gudang.create')
            <x-slot:actions>
                <a href="{{ route('gudang.mutasi.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    + Buat Mutasi Baru
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Log Audit Pergerakan Stok" subtitle="Riwayat perubahan stok bersifat permanen dan tidak dapat dihapus (NFR-12).">
        <form action="{{ route('gudang.mutasi.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari referensi atau catatan mutasi..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
            </div>
            <div class="sm:w-44">
                <select
                    name="type"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
                    <option value="">Semua Tipe</option>
                    <option value="transfer" @selected(request('type') === 'transfer')>Transfer</option>
                    <option value="adjustment" @selected(request('type') === 'adjustment')>Penyesuaian</option>
                    <option value="in" @selected(request('type') === 'in')>Masuk (In)</option>
                    <option value="out" @selected(request('type') === 'out')>Keluar (Out)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-900">
                    Filter
                </button>
                @if (request()->filled('cari') || request()->filled('type'))
                    <a href="{{ route('gudang.mutasi.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if ($movements->isEmpty())
            <x-empty-state
                title="Belum ada riwayat mutasi"
                description="Catatan keluar masuk dan pergerakan bahan akan muncul di sini."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Waktu</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Tipe</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Material</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Dari Gudang</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Ke Gudang</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Jumlah</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Referensi</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Aksi</th>
                    </tr>
                </x-slot:header>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($movements as $m)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-600">
                                <span class="font-semibold text-slate-800">{{ $m->movement_date?->format('d/m/Y') }}</span>
                                <span class="block text-[11px] text-slate-400">{{ $m->movement_date?->format('H:i') }} WIB</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <x-badge variant="{{ $m->type === 'in' ? 'success' : ($m->type === 'out' ? 'danger' : ($m->type === 'transfer' ? 'info' : 'warning')) }}">
                                    {{ strtoupper($m->type) }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                {{ $m->material?->name ?? 'Material #' . $m->material_id }}
                                <span class="block font-mono text-xs font-normal text-slate-500">{{ $m->material?->sku }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-700">{{ $m->fromWarehouse?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs text-slate-700">{{ $m->toWarehouse?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-slate-900">
                                {{ number_format($m->qty, 2) }} <span class="text-xs font-normal text-slate-500">{{ $m->material?->unit }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                <span class="font-semibold text-slate-700">{{ $m->reference_type ?? '-' }}</span>
                                <span class="block text-slate-400 truncate max-w-xs">{{ $m->notes }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                @can('gudang.update')
                                    <a href="{{ route('gudang.mutasi.edit', $m->id) }}" class="text-brand-600 hover:text-brand-800 text-xs font-semibold">
                                        Catatan
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>

            <div class="mt-4">
                {{ $movements->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
