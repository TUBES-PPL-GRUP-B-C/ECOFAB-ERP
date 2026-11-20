@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Work Order (FR-17)" subtitle="Kelola rencana dan status perintah produksi.">
        @can('produksi.create')
            <x-slot:actions>
                <a href="{{ route('produksi.wo.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Tambah Work Order</a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-card title="Daftar Work Order" subtitle="Cari nomor Work Order atau nama produk, lalu saring berdasarkan status.">
        <form action="{{ route('produksi.wo.index') }}" method="GET" class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_14rem_auto] lg:items-end">
            <div class="space-y-1.5">
                <label for="cari" class="block text-xs font-semibold text-slate-700">Cari Work Order</label>
                <input id="cari" name="cari" type="search" value="{{ is_scalar(old('cari', request('cari'))) ? old('cari', request('cari')) : '' }}" maxlength="255" placeholder="Nomor WO atau nama produk" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div class="space-y-1.5">
                <label for="status" class="block text-xs font-semibold text-slate-700">Status</label>
                <select id="status" name="status" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', request('status')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Terapkan</button>
                @if (request()->filled('cari') || request()->filled('status'))
                    <a href="{{ route('produksi.wo.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                @endif
            </div>
        </form>

        @if ($workOrders->isEmpty())
            @if (request()->filled('cari') || request()->filled('status'))
                <x-empty-state title="Tidak ada Work Order yang cocok" description="Coba kata pencarian atau status lain, atau reset filter untuk melihat seluruh Work Order." />
            @else
                <x-empty-state title="Belum ada Work Order" description="Rencana produksi akan muncul di sini setelah Work Order ditambahkan." />
            @endif
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Nomor WO / Produk</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Target Kuantitas</th>
                        <th scope="col" class="px-6 py-3.5">Jadwal</th>
                        <th scope="col" class="px-6 py-3.5">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:header>
                @foreach ($workOrders as $workOrder)
                    <tr class="transition-colors hover:bg-slate-50/70">
                        <td class="px-6 py-4">
                            <a href="{{ route('produksi.wo.show', $workOrder) }}" class="font-semibold text-brand-900 hover:underline">{{ $workOrder->wo_number }}</a>
                            <p class="mt-1 break-words text-slate-600">{{ $workOrder->product_name }}</p>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-medium text-slate-800">{{ number_format((float) $workOrder->target_quantity, 2, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-600">
                            <p>Mulai: {{ $workOrder->start_date?->format('d/m/Y') ?? 'Belum diatur' }}</p>
                            <p class="mt-1">Tenggat: {{ $workOrder->due_date?->format('d/m/Y') ?? 'Belum diatur' }}</p>
                        </td>
                        <td class="px-6 py-4">@include('produksi.wo._status', ['status' => $workOrder->status])</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('produksi.wo.show', $workOrder) }}" class="text-xs font-semibold text-slate-600 hover:text-brand-800" aria-label="Detail {{ $workOrder->wo_number }}">Detail</a>
                                @if ($workOrder->isDraft())
                                    @can('produksi.update')
                                        <a href="{{ route('produksi.wo.edit', $workOrder) }}" class="text-xs font-semibold text-brand-800 hover:text-brand-950" aria-label="Ubah {{ $workOrder->wo_number }}">Ubah</a>
                                    @endcan
                                    @can('produksi.delete')
                                        @if ($workOrder->materials_count === 0 && $workOrder->waste_records_count === 0)
                                            <form action="{{ route('produksi.wo.destroy', $workOrder) }}" method="POST" onsubmit="return confirm('Hapus Work Order Draft ini? Tindakan ini tidak dapat dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800" aria-label="Hapus {{ $workOrder->wo_number }}">Hapus</button>
                                            </form>
                                        @endif
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
        <x-pagination :paginator="$workOrders->withQueryString()" />
    </x-card>
</div>
@endsection
