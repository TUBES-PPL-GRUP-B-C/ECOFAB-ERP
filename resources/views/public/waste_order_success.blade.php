@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <x-page-header title="Pesanan diterima" subtitle="Terima kasih atas pengajuan pesanan Anda" />
    <x-card>
        <p role="status" class="text-sm text-slate-700">{{ session('success', 'Pesanan Anda telah diterima dan menunggu peninjauan Admin.') }}</p>
        <p class="mt-3 text-sm text-slate-500">Admin akan meninjau ketersediaan limbah dan menghubungi Anda melalui kontak yang diberikan.</p>
        <a href="{{ route('public.bursa.index') }}" class="mt-6 inline-flex rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Kembali ke katalog</a>
    </x-card>
</div>
@endsection
