@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Pesanan Limbah (FR-25)"
        subtitle="Antrean persetujuan pesanan pengepul"
    />

    <x-card>
        <x-empty-state
            title="Modul Pesanan Limbah (FR-25)"
            description="Fitur modul ini sedang dalam tahap pengembangan oleh anggota tim pada branch fitur terkait."
        />
    </x-card>
</div>
@endsection