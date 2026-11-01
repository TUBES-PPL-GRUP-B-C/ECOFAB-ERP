@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Faktor Emisi Karbon (FR-12)"
        subtitle="Referensi faktor emisi kg CO₂e"
    />

    <x-card>
        <x-empty-state
            title="Modul Faktor Emisi Karbon (FR-12)"
            description="Fitur modul ini sedang dalam tahap pengembangan oleh anggota tim pada branch fitur terkait."
        />
    </x-card>
</div>
@endsection