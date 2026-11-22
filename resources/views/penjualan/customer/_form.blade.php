@php
    $customer = $customer ?? null;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <x-form-input
        name="code"
        label="Kode Customer"
        :value="$customer?->code"
        :required="true"
        placeholder="Contoh: CUST-001"
        help="Kode unik maksimal 20 karakter."
    />

    <x-form-input
        name="name"
        label="Nama Customer"
        :value="$customer?->name"
        :required="true"
        placeholder="Contoh: PT Mebel Hijau Nusantara"
    />
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <x-form-input
        name="phone"
        label="Nomor Telepon"
        :value="$customer?->phone"
        placeholder="Contoh: 081234567890"
    />

    <x-form-input
        name="email"
        type="email"
        label="Alamat Email"
        :value="$customer?->email"
        placeholder="Contoh: purchasing@mebelhijau.co.id"
    />
</div>

<div class="space-y-1.5">
    <label for="address" class="block text-xs font-semibold text-slate-700">Alamat</label>
    <textarea
        id="address"
        name="address"
        rows="3"
        placeholder="Alamat lengkap customer / alamat pengiriman utama"
        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 @error('address') border-rose-500 @enderror"
    >{{ old('address', $customer?->address) }}</textarea>
    @error('address')
        <p class="text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
    @enderror
</div>
