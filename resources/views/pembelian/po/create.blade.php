@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Buat Purchase Order Cepat"
        subtitle="Form pengajuan PO baru"
    />

    <x-card>
        <x-empty-state
            title="Modul Buat Purchase Order Cepat"
            description="Fitur modul ini sedang dalam tahap pengembangan oleh anggota tim pada branch fitur terkait."
        />
    </x-card>
</div>
@endsection