@extends('layouts.app')

@section('content')
<div class="max-w-4xl space-y-6">
    <x-page-header
        title="Penerimaan Barang / GRN (FR-08-C)"
        subtitle="Catat material yang masuk dari vendor dan tambahkan stok otomatis via StockService"
    >
        <x-slot:actions>
            <a href="{{ route('gudang.grn.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Dokumen Penerimaan">
        <form action="{{ route('gudang.grn.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nomor Dokumen GRN <span class="text-rose-500">*</span></label>
                    <input type="text" name="grn_number" value="{{ old('grn_number', 'GRN-' . date('Ymd') . '-' . rand(100, 999)) }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-mono text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Gudang Tujuan <span class="text-rose-500">*</span></label>
                    <select name="warehouse_id" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                        <option value="">-- Pilih Fasilitas Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected(old('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Tanggal Penerimaan <span class="text-rose-500">*</span></label>
                    <input type="date" name="received_date" value="{{ old('received_date', date('Y-m-d')) }}" required
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Referensi ID Purchase Order (PO)</label>
                    <input type="number" name="purchase_order_id" value="{{ old('purchase_order_id') }}" placeholder="ID PO Pembelian (Kosongkan jika non-PO)"
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nomor Surat Jalan / Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: SJ-0912 CV Rotan Lestari"
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20">
                </div>
            </div>

            <!-- Rincian Bahan yang Diterima -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-bold text-slate-800">Rincian Bahan Baku yang Diterima <span class="text-rose-500">*</span></h4>
                    <button type="button" onclick="addRow()" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                        + Tambah Baris Bahan
                    </button>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left text-sm" id="items-table">
                        <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-600">
                            <tr>
                                <th class="px-4 py-3" style="width: 45%;">Bahan Baku</th>
                                <th class="px-4 py-3" style="width: 25%;">Kuantitas Masuk</th>
                                <th class="px-4 py-3" style="width: 25%;">Keterangan</th>
                                <th class="px-4 py-3 text-center" style="width: 5%;">#</th>
                            </tr>
                        </thead>
                        <tbody id="items-body" class="divide-y divide-slate-100 bg-white">
                            <tr class="item-row">
                                <td class="p-2">
                                    <select name="items[0][material_id]" required class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-brand-600 focus:ring-1 focus:ring-brand-500/20">
                                        <option value="">-- Pilih Material --</option>
                                        @foreach($materials as $mat)
                                            <option value="{{ $mat->id }}">{{ $mat->sku }} - {{ $mat->name }} ({{ $mat->unit }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" min="0.01" name="items[0][qty_received]" placeholder="0.00" required
                                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-brand-600 focus:ring-1 focus:ring-brand-500/20">
                                </td>
                                <td class="p-2">
                                    <input type="text" name="items[0][notes]" placeholder="Kondisi baik, lot..."
                                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-brand-600 focus:ring-1 focus:ring-brand-500/20">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="removeRow(this)" disabled class="text-slate-300 hover:text-rose-600 disabled:opacity-30">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-lg bg-emerald-50 p-4 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Saat GRN disimpan, <strong>StockService</strong> otomatis menambahkan saldo kuantitas ke gudang tujuan dan mencatat mutasi riwayat masuk.</span>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('gudang.grn.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">
                    Simpan Penerimaan Barang
                </button>
            </div>
        </form>
    </x-card>
</div>

<script>
    let idx = 1;
    function addRow() {
        const body = document.getElementById('items-body');
        const firstSelectHtml = document.querySelector('.item-row select').innerHTML;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td class="p-2">
                <select name="items[${idx}][material_id]" required class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-brand-600 focus:ring-1 focus:ring-brand-500/20">
                    ${firstSelectHtml}
                </select>
            </td>
            <td class="p-2">
                <input type="number" step="0.01" min="0.01" name="items[${idx}][qty_received]" placeholder="0.00" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-brand-600 focus:ring-1 focus:ring-brand-500/20">
            </td>
            <td class="p-2">
                <input type="text" name="items[${idx}][notes]" placeholder="Kondisi baik, lot..."
                    class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-brand-600 focus:ring-1 focus:ring-brand-500/20">
            </td>
            <td class="p-2 text-center">
                <button type="button" onclick="removeRow(this)" class="text-slate-400 hover:text-rose-600 font-bold text-lg">
                    &times;
                </button>
            </td>
        `;
        body.appendChild(tr);
        idx++;
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            btn.closest('tr').remove();
        }
    }
</script>
@endsection
