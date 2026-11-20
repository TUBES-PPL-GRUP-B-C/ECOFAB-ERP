<div class="grid gap-5 sm:grid-cols-2">
    @php
        $fields = [
            'wo_number' => ['label' => 'Nomor Work Order', 'type' => 'text', 'required' => true, 'maxlength' => 50],
            'product_name' => ['label' => 'Nama Produk', 'type' => 'text', 'required' => true, 'maxlength' => 255],
            'target_quantity' => ['label' => 'Target Kuantitas', 'type' => 'number', 'required' => true],
            'start_date' => ['label' => 'Tanggal Mulai', 'type' => 'date', 'required' => false],
            'due_date' => ['label' => 'Tanggal Tenggat', 'type' => 'date', 'required' => false],
        ];
    @endphp

    @foreach ($fields as $name => $field)
        @php
            $value = $workOrder->{$name};
            if ($field['type'] === 'date') {
                $value = $value?->format('Y-m-d');
            }
        @endphp
        <div class="space-y-1.5">
            <label for="{{ $name }}" class="block text-xs font-semibold text-slate-700">
                {{ $field['label'] }}
                @if ($field['required'])
                    <span class="text-rose-500" aria-hidden="true">*</span>
                @endif
            </label>
            <input
                type="{{ $field['type'] }}"
                id="{{ $name }}"
                name="{{ $name }}"
                value="{{ is_scalar(old($name, $value)) ? old($name, $value) : '' }}"
                @required($field['required'])
                @if (isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                @if ($name === 'target_quantity') min="0.01" max="9999999999.99" step="0.01" @endif
                aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
                aria-describedby="{{ $name }}-help{{ $errors->has($name) ? ' ' . $name . '-error' : '' }}"
                class="w-full rounded-lg border bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 {{ $errors->has($name) ? 'border-rose-500' : 'border-slate-300' }}"
            >
            <p id="{{ $name }}-help" class="text-xs text-slate-500">
                @switch($name)
                    @case('wo_number') Gunakan nomor unik, maksimal 50 karakter. @break
                    @case('product_name') Nama produk yang akan diproduksi, maksimal 255 karakter. @break
                    @case('target_quantity') Masukkan jumlah lebih dari nol, maksimal dua angka desimal. @break
                    @case('start_date') Opsional. Tanggal rencana mulai produksi. @break
                    @case('due_date') Opsional. Tidak boleh sebelum tanggal mulai. @break
                @endswitch
            </p>
            @error($name)
                <p id="{{ $name }}-error" class="text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <div class="space-y-1.5 sm:col-span-2">
        <label for="notes" class="block text-xs font-semibold text-slate-700">Catatan</label>
        <textarea
            id="notes"
            name="notes"
            rows="4"
            maxlength="5000"
            aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}"
            aria-describedby="notes-help{{ $errors->has('notes') ? ' notes-error' : '' }}"
            class="w-full rounded-lg border bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 {{ $errors->has('notes') ? 'border-rose-500' : 'border-slate-300' }}"
        >{{ is_scalar(old('notes', $workOrder->notes)) ? old('notes', $workOrder->notes) : '' }}</textarea>
        <p id="notes-help" class="text-xs text-slate-500">Opsional, maksimal 5.000 karakter.</p>
        @error('notes')
            <p id="notes-error" class="text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
    <button type="submit" class="rounded-lg bg-brand-800 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-900">{{ $submitLabel }}</button>
    <a href="{{ $cancelUrl }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
</div>
