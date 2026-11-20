@php
    $fieldValues = [];
    foreach (['buyer_name', 'buyer_contact', 'requested_weight'] as $field) {
        $value = old($field, $order?->{$field});
        $fieldValues[$field] = is_scalar($value) ? (string) $value : '';
    }
@endphp
<div class="space-y-1.5">
    <label for="buyer_name" class="block text-sm font-semibold text-slate-700">Nama pemesan</label>
    <input id="buyer_name" name="buyer_name" type="text" maxlength="100" autocomplete="name" value="{{ $fieldValues['buyer_name'] }}" required aria-invalid="{{ $errors->has('buyer_name') ? 'true' : 'false' }}" @error('buyer_name') aria-describedby="buyer_name_error" @enderror class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('buyer_name') border-rose-500 @enderror">
    @error('buyer_name')
        <p id="buyer_name_error" role="alert" class="text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>
<div class="space-y-1.5">
    <label for="buyer_contact" class="block text-sm font-semibold text-slate-700">Kontak pemesan</label>
    <input id="buyer_contact" name="buyer_contact" type="text" maxlength="100" value="{{ $fieldValues['buyer_contact'] }}" required aria-invalid="{{ $errors->has('buyer_contact') ? 'true' : 'false' }}" aria-describedby="buyer_contact_help{{ $errors->has('buyer_contact') ? ' buyer_contact_error' : '' }}" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('buyer_contact') border-rose-500 @enderror">
    <p id="buyer_contact_help" class="text-xs text-slate-500">Masukkan nomor telepon atau email yang dapat dihubungi.</p>
    @error('buyer_contact')
        <p id="buyer_contact_error" role="alert" class="text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>
<div class="space-y-1.5">
    <label for="requested_weight" class="block text-sm font-semibold text-slate-700">Berat pesanan (kg)</label>
    <input id="requested_weight" name="requested_weight" type="number" min="0.001" max="{{ $availableWeight }}" step="0.001" inputmode="decimal" value="{{ $fieldValues['requested_weight'] }}" required aria-invalid="{{ $errors->has('requested_weight') ? 'true' : 'false' }}" aria-describedby="requested_weight_help{{ $errors->has('requested_weight') ? ' requested_weight_error' : '' }}" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('requested_weight') border-rose-500 @enderror">
    <p id="requested_weight_help" class="text-xs text-slate-500">Minimal 0,001 kg. Berat tersedia saat ini: {{ $availableWeight }} kg.</p>
    @error('requested_weight')
        <p id="requested_weight_error" role="alert" class="text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>
