@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Manufaktur Sirkular"
        subtitle="Pusat produksi dan resep ramah lingkungan"
    />

    <x-card>
        <x-empty-state
            title="Modul Manufaktur Sirkular"
            description="Fitur modul ini sedang dalam tahap pengembangan oleh anggota tim pada branch fitur terkait."
        />
    </x-card>
</div>
@endsection