@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Penerimaan Barang / GRN (FR-08)"
        subtitle="Catat penerimaan material dari pemasok / PO dengan penambahan saldo stok otomatis"
    >
        @can('gudang.create')
            <x-slot:actions>
                <a href="{{ route('gudang.grn.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    + Dokumen GRN Baru
                </a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Daftar Dokumen Penerimaan Barang" subtitle="Mendukung penerimaan bertahap (parsial) dari pesanan pembelian.">
        <form action="{{ route('gudang.grn.index') }}" method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari nomor GRN atau keterangan..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
            </div>
            <div class="sm:w-44">
                <select
                    name="status"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
                    <option value="">Semua Status</option>
                    <option value="received" @selected(request('status') === 'received')>Diterima</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-900">
                    Cari
                </button>
                @if (request()->filled('cari') || request()->filled('status'))
                    <a href="{{ route('gudang.grn.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        @if ($receipts->isEmpty())
            <x-empty-state
                title="Belum ada dokumen GRN"
                description="Dokumen penerimaan bahan dari supplier akan muncul di sini."
            />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Nomor GRN</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Tanggal</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Gudang Penerima</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Ref. PO</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Item Bahan</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-600">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Aksi</th>
                    </tr>
                </x-slot:header>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($receipts as $r)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 font-mono text-xs font-bold text-brand-900">
                                <a href="{{ route('gudang.grn.show', $r->id) }}" class="hover:underline">
                                    {{ $r->grn_number }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-700">
                                {{ $r->received_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                {{ $r->warehouse?->name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center text-xs text-slate-500">
                                {{ $r->purchase_order_id ? 'PO #' . $r->purchase_order_id : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <x-badge variant="info">{{ $r->items->count() }} item</x-badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <x-badge variant="{{ $r->status === 'received' ? 'success' : 'neutral' }}">
                                    {{ strtoupper($r->status) }}
                                </x-badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('gudang.grn.show', $r->id) }}" class="text-slate-600 hover:text-slate-900" title="Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @can('gudang.update')
                                        <a href="{{ route('gudang.grn.edit', $r->id) }}" class="text-brand-600 hover:text-brand-800" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>

            <div class="mt-4">
                {{ $receipts->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
