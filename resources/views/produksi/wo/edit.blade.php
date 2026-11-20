@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Ubah Work Order" :subtitle="'Perbarui rencana produksi ' . $workOrder->wo_number . '.'">
        <x-slot:actions>
            <a href="{{ route('produksi.wo.show', $workOrder) }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900">Kembali ke detail</a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Form Work Order" subtitle="Work Order hanya dapat diubah selama berstatus Draft. Kolom bertanda * wajib diisi.">
        @if ($workOrder->isDraft())
            @can('produksi.update')
                <form action="{{ route('produksi.wo.update', $workOrder) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    @include('produksi.wo._form', ['submitLabel' => 'Simpan Perubahan', 'cancelUrl' => route('produksi.wo.show', $workOrder)])
                </form>
            @endcan
        @else
            <p class="text-sm text-slate-600">Work Order ini tidak lagi berstatus Draft dan tidak dapat diubah.</p>
        @endif
    </x-card>
</div>
@endsection
