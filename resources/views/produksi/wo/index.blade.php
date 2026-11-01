@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Work Order (FR-17 & FR-18)"
        subtitle="Perintah kerja dan pelacakan batch"
    />

    <x-card>
        <x-empty-state
            title="Modul Work Order (FR-17 & FR-18)"
            description="Fitur modul ini sedang dalam tahap pengembangan oleh anggota tim pada branch fitur terkait."
        />
    </x-card>
</div>
@endsection