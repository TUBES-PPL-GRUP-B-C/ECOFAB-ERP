@php
    $salesOrder = $salesOrder ?? null;
    $oldItems = old('items');
    if (is_array($oldItems)) {
        $items = array_values($oldItems);
    } elseif ($salesOrder && $salesOrder->items->isNotEmpty()) {
        $items = $salesOrder->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'qty' => (float) $item->qty,
            'unit_price' => (float) $item->unit_price,
        ])->all();
    } else {
        $items = [['product_id' => null, 'product_name' => '', 'qty' => '', 'unit_price' => '']];
    }
    $inputClass = 'w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20';
    $cellClass = 'w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-brand-600 focus:ring-1 focus:ring-brand-500/20';
@endphp

<div class="grid gap-5 sm:grid-cols-3">
    <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-700">Nomor SO</label>
        <input type="text" value="{{ $salesOrder?->so_number ?? $soNumber ?? '' }}" disabled
            class="{{ $inputClass }} bg-slate-50 font-mono text-slate-500">
        <p class="text-xs text-slate-500">Dibuat otomatis oleh sistem saat disimpan.</p>
    </div>

    <div class="space-y-1.5">
        <label for="customer_id" class="block text-xs font-semibold text-slate-700">Customer <span class="text-rose-500">*</span></label>
        <select id="customer_id" name="customer_id" required class="{{ $inputClass }} @error('customer_id') border-rose-500 @enderror">
            <option value="">-- Pilih Customer --</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected((string) old('customer_id', $salesOrder?->customer_id) === (string) $customer->id)>
                    {{ $customer->code }} — {{ $customer->name }}
                </option>
            @endforeach
        </select>
        @error('customer_id')
            <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-1.5">
        <label for="order_date" class="block text-xs font-semibold text-slate-700">Tanggal Order <span class="text-rose-500">*</span></label>
        <input type="date" id="order_date" name="order_date" required
            value="{{ old('order_date', $salesOrder?->order_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
            class="{{ $inputClass }} @error('order_date') border-rose-500 @enderror">
        @error('order_date')
            <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid gap-5 sm:grid-cols-3">
    <div class="space-y-1.5">
        <label for="status" class="block text-xs font-semibold text-slate-700">Status <span class="text-rose-500">*</span></label>
        <select id="status" name="status" required class="{{ $inputClass }} @error('status') border-rose-500 @enderror">
            @foreach (\App\Models\SalesOrder::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $salesOrder?->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-1.5 sm:col-span-2">
        <label for="notes" class="block text-xs font-semibold text-slate-700">Catatan</label>
        <input type="text" id="notes" name="notes" value="{{ old('notes', $salesOrder?->notes) }}"
            placeholder="Catatan pesanan (opsional)"
            class="{{ $inputClass }} @error('notes') border-rose-500 @enderror">
    </div>
</div>

<div class="pt-4 border-t border-slate-100">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-bold text-slate-800">Item Pesanan <span class="text-rose-500">*</span></h4>
        <button type="button" onclick="soAddRow()" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
            + Tambah Item
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-600">
                <tr>
                    <th class="px-4 py-3" style="width: 40%;">Produk Jadi</th>
                    <th class="px-4 py-3" style="width: 15%;">Jumlah</th>
                    <th class="px-4 py-3" style="width: 20%;">Harga Satuan (Rp)</th>
                    <th class="px-4 py-3 text-right" style="width: 20%;">Subtotal</th>
                    <th class="px-4 py-3 text-center" style="width: 5%;">#</th>
                </tr>
            </thead>
            <tbody id="so-items-body" class="divide-y divide-slate-100 bg-white">
                @foreach ($items as $i => $item)
                    <tr class="so-item-row">
                        <td class="p-2">
                            <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item['product_id'] ?? '' }}">
                            <input type="text" name="items[{{ $i }}][product_name]" value="{{ $item['product_name'] ?? '' }}" required
                                placeholder="Nama produk jadi" class="{{ $cellClass }}">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.001" min="0.001" name="items[{{ $i }}][qty]" value="{{ $item['qty'] ?? '' }}" required
                                placeholder="0" class="{{ $cellClass }} so-qty" oninput="soRecalc()">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}" required
                                placeholder="0" class="{{ $cellClass }} so-price" oninput="soRecalc()">
                        </td>
                        <td class="p-2 text-right text-xs font-semibold text-slate-700 so-subtotal">Rp 0</td>
                        <td class="p-2 text-center">
                            <button type="button" onclick="soRemoveRow(this)" class="text-slate-400 hover:text-rose-600 font-bold text-lg">&times;</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-600">Total (estimasi)</td>
                    <td class="px-4 py-3 text-right text-sm font-bold text-brand-900" id="so-total">Rp 0</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <p class="mt-2 text-xs text-slate-500">Subtotal dan total final dihitung ulang oleh server saat disimpan.</p>

    @if (! ($stockIntegrated ?? false))
        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
            Integrasi tabel stok produk jadi (<code class="font-mono">FR-14</code> / <code class="font-mono">product_stocks</code>) belum tersedia di branch ini. Validasi ketersediaan stok otomatis (<code class="font-mono">ProductStockChecker</code>) akan aktif otomatis setelah modul Produk terintegrasi.
        </div>
    @endif
</div>

<script>
    let soIdx = {{ count($items) }};
    const soCellClass = @json($cellClass);

    function soFormat(n) {
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    }

    function soRecalc() {
        let total = 0;
        document.querySelectorAll('.so-item-row').forEach(function (row) {
            const qty = parseFloat(row.querySelector('.so-qty').value) || 0;
            const price = parseFloat(row.querySelector('.so-price').value) || 0;
            const subtotal = qty * price;
            total += subtotal;
            row.querySelector('.so-subtotal').textContent = soFormat(subtotal);
        });
        document.getElementById('so-total').textContent = soFormat(total);
    }

    function soAddRow() {
        const tr = document.createElement('tr');
        tr.className = 'so-item-row';
        tr.innerHTML = `
            <td class="p-2">
                <input type="hidden" name="items[${soIdx}][product_id]" value="">
                <input type="text" name="items[${soIdx}][product_name]" required placeholder="Nama produk jadi" class="${soCellClass}">
            </td>
            <td class="p-2">
                <input type="number" step="0.001" min="0.001" name="items[${soIdx}][qty]" required placeholder="0" class="${soCellClass} so-qty" oninput="soRecalc()">
            </td>
            <td class="p-2">
                <input type="number" step="0.01" min="0" name="items[${soIdx}][unit_price]" required placeholder="0" class="${soCellClass} so-price" oninput="soRecalc()">
            </td>
            <td class="p-2 text-right text-xs font-semibold text-slate-700 so-subtotal">Rp 0</td>
            <td class="p-2 text-center">
                <button type="button" onclick="soRemoveRow(this)" class="text-slate-400 hover:text-rose-600 font-bold text-lg">&times;</button>
            </td>
        `;
        document.getElementById('so-items-body').appendChild(tr);
        soIdx++;
    }

    function soRemoveRow(btn) {
        if (document.querySelectorAll('.so-item-row').length > 1) {
            btn.closest('tr').remove();
            soRecalc();
        }
    }

    document.addEventListener('DOMContentLoaded', soRecalc);
</script>
