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

    <x-card>
        @if ($listings->isEmpty())
            <x-empty-state title="Belum ada listing" description="Listing limbah produksi akan muncul di sini setelah dibuat." />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-6 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Limbah produksi</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5">Berat tersedia (kg)</th>
                        <th class="px-6 py-3.5">Harga per kg (Rp)</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Aksi</th>
                    </tr>
                </x-slot:header>
                @foreach ($listings as $listing)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">{{ $listing->id }}</td>
                        <td class="px-6 py-4">#{{ $listing->waste_record_id }}</td>
                        <td class="px-6 py-4">{{ $listing->category }}</td>
                        <td class="px-6 py-4">{{ $listing->available_weight }}</td>
                        <td class="px-6 py-4">{{ $listing->price_per_kg }}</td>
                        <td class="px-6 py-4">{{ $listing->status }}</td>
                        <td class="px-6 py-4 space-x-3 whitespace-nowrap">
                            <a href="{{ route('bursa.listing.show', $listing) }}" class="font-semibold text-brand-800 hover:underline">Detail</a>
                            @can('bursa.update')
                                <a href="{{ route('bursa.listing.edit', $listing) }}" class="font-semibold text-brand-800 hover:underline">Edit</a>
                            @endcan
                            @can('bursa.delete')
                                <x-confirm-delete :action="route('bursa.listing.destroy', $listing)" itemName="listing ini" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-table>
            <x-pagination :paginator="$listings" />
        @endif
    </x-card>
</div>
@endsection
