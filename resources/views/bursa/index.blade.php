@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Bursa Limbah Internal"
        subtitle="Manajemen limbah sirkular"
    />

    <x-card>
        <x-empty-state
            title="Modul Bursa Limbah Internal"
            description="Fitur modul ini sedang dalam tahap pengembangan oleh anggota tim pada branch fitur terkait."
        />
    </x-card>
</div>
@endsection