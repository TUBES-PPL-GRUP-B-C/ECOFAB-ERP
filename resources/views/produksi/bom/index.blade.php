@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Green BoM (FR-15 & FR-16)"
        subtitle="Resep ramah lingkungan dan emisi karbon"
    />

    <x-card>
        <x-empty-state
            title="Modul Green BoM (FR-15 & FR-16)"
            description="Fitur modul ini sedang dalam tahap pengembangan oleh anggota tim pada branch fitur terkait."
        />
    </x-card>
</div>
@endsection