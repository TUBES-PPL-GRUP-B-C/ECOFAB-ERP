@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Listing Bursa Limbah (FR-23)" subtitle="Daftar limbah siap jual">
        @can('bursa.create')
            <x-slot:actions>
                <a href="{{ route('bursa.listing.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Tambah listing</a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    @if (session('success'))
        <p role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('success') }}</p>
    @endif

    <x-card>
        @if ($listings->isEmpty())
            <x-empty-state title="Belum ada listing" description="Listing limbah produksi akan muncul di sini setelah dibuat." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead><tr class="border-b"><th class="p-3">ID</th><th class="p-3">Limbah produksi</th><th class="p-3">Kategori</th><th class="p-3">Berat tersedia (kg)</th><th class="p-3">Harga per kg (Rp)</th><th class="p-3">Status</th><th class="p-3">Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($listings as $listing)
                            <tr class="border-b">
                                <td class="p-3">{{ $listing->id }}</td>
                                <td class="p-3">#{{ $listing->waste_record_id }}</td>
                                <td class="p-3">{{ $listing->category }}</td>
                                <td class="p-3">{{ $listing->available_weight }}</td>
                                <td class="p-3">{{ $listing->price_per_kg }}</td>
                                <td class="p-3">{{ $listing->status }}</td>
                                <td class="p-3 space-x-3 whitespace-nowrap">
                                    <a href="{{ route('bursa.listing.show', $listing) }}" class="font-semibold text-brand-800 hover:underline">Detail</a>
                                    @can('bursa.update')
                                        <a href="{{ route('bursa.listing.edit', $listing) }}" class="font-semibold text-brand-800 hover:underline">Edit</a>
                                    @endcan
                                    @can('bursa.delete')
                                        <form action="{{ route('bursa.listing.destroy', $listing) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Hapus listing ini?')" class="font-semibold text-rose-700 hover:underline">Hapus</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $listings->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
