@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Inventaris & Gudang"
        subtitle="Pusat pengelolaan gudang dan material"
    />

    <x-card>
        <x-empty-state
            title="Modul Inventaris & Gudang"
            description="Fitur modul ini sedang dalam tahap pengembangan oleh anggota tim pada branch fitur terkait."
        />
    </x-card>
</div>
@endsection