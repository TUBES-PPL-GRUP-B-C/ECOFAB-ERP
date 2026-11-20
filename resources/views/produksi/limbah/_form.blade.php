@php
    $inputValue = static function ($name, $default = null) {
        $value = old($name, $default);
        return is_scalar($value) ? (string) $value : '';
    };
    $fields = [
        'waste_type' => ['label' => 'Jenis Limbah', 'type' => 'text', 'maxlength' => 100, 'help' => 'Contoh: potongan kayu atau sisa rotan. Maksimal 100 karakter.'],
        'weight_kg' => ['label' => 'Berat (kg)', 'type' => 'number', 'help' => 'Minimal 0,001 kg, maksimal tiga angka desimal.'],
        'condition' => ['label' => 'Kondisi', 'type' => 'text', 'maxlength' => 50, 'help' => 'Jelaskan kondisi limbah, maksimal 50 karakter.'],
    ];
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="space-y-1.5 sm:col-span-2">
        <label for="work_order_id" class="block text-xs font-semibold text-slate-700">Asal Work Order (opsional)</label>
        <select id="work_order_id" name="work_order_id"
            aria-invalid="{{ $errors->has('work_order_id') ? 'true' : 'false' }}"
            aria-describedby="work_order_id-help{{ $errors->has('work_order_id') ? ' work_order_id-error' : '' }}"
            class="w-full rounded-lg border bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 {{ $errors->has('work_order_id') ? 'border-rose-500' : 'border-slate-300' }}">
            <option value="">Pencatatan manual tanpa Work Order</option>
            @foreach ($workOrders as $workOrder)
                <option value="{{ $workOrder->id }}" @selected($inputValue('work_order_id', $wasteRecord->work_order_id) === (string) $workOrder->id)>{{ $workOrder->wo_number }} — {{ $workOrder->product_name }}</option>
            @endforeach
        </select>
        <p id="work_order_id-help" class="text-xs text-slate-500">
            @if ($workOrders->isEmpty())
                Belum ada Work Order selesai. Limbah tetap dapat dicatat secara manual tanpa Work Order.
            @else
                Pilih Work Order yang sudah selesai, atau biarkan kosong untuk pencatatan manual.
            @endif
        </p>
        @error('work_order_id')
            <p id="work_order_id-error" class="text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    @foreach ($fields as $name => $field)
        <div class="space-y-1.5">
            <label for="{{ $name }}" class="block text-xs font-semibold text-slate-700">{{ $field['label'] }} <span class="text-rose-500" aria-hidden="true">*</span></label>
            <input type="{{ $field['type'] }}" id="{{ $name }}" name="{{ $name }}"
                value="{{ $inputValue($name, $wasteRecord->{$name} ?? ($name === 'condition' ? 'Bagus' : null)) }}" required
                @if (isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                @if ($name === 'weight_kg') min="0.001" max="999999999.999" step="0.001" @endif
                aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
                aria-describedby="{{ $name }}-help{{ $errors->has($name) ? ' ' . $name . '-error' : '' }}"
                class="w-full rounded-lg border bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 {{ $errors->has($name) ? 'border-rose-500' : 'border-slate-300' }}">
            <p id="{{ $name }}-help" class="text-xs text-slate-500">{{ $field['help'] }}</p>
            @error($name)
                <p id="{{ $name }}-error" class="text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <div class="space-y-1.5">
        <label for="status" class="block text-xs font-semibold text-slate-700">Status <span class="text-rose-500" aria-hidden="true">*</span></label>
        <select id="status" name="status" required
            aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
            aria-describedby="status-help{{ $errors->has('status') ? ' status-error' : '' }}"
            class="w-full rounded-lg border bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 {{ $errors->has('status') ? 'border-rose-500' : 'border-slate-300' }}">
            <option value="">Pilih status</option>
            @foreach (['available', 'sold'] as $value)
                <option value="{{ $value }}" @selected($inputValue('status', $wasteRecord->status ?? 'available') === $value)>{{ $statuses[$value] }}</option>
            @endforeach
        </select>
        <p id="status-help" class="text-xs text-slate-500">Pilih Tersedia atau Terjual. Pendaftaran di Bursa dikelola melalui modul Bursa.</p>
        @error('status')
            <p id="status-error" class="text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-1.5 sm:col-span-2">
        <label for="notes" class="block text-xs font-semibold text-slate-700">Catatan</label>
        <textarea id="notes" name="notes" rows="4" maxlength="5000"
            aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}"
            aria-describedby="notes-help{{ $errors->has('notes') ? ' notes-error' : '' }}"
            class="w-full rounded-lg border bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 {{ $errors->has('notes') ? 'border-rose-500' : 'border-slate-300' }}">{{ $inputValue('notes', $wasteRecord->notes) }}</textarea>
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
