@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Pesanan Limbah (FR-25)"
        subtitle="Daftar pesanan dan tindak lanjut pemesanan limbah"
    >
        <x-slot:actions>
            <a href="{{ route('bursa.listing.index') }}" class="inline-flex rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Lihat listing limbah</a>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Daftar pesanan" subtitle="Informasi pemesan, limbah, berat pesanan, dan status penanganan">
        <x-table aria-label="Daftar pesanan limbah">
            <x-slot:header>
                <tr>
                    <th scope="col" class="px-6 py-3.5">Pesanan</th>
                    <th scope="col" class="px-6 py-3.5">Pemesan</th>
                    <th scope="col" class="px-6 py-3.5">Limbah</th>
                    <th scope="col" class="px-6 py-3.5">Berat pesanan</th>
                    <th scope="col" class="px-6 py-3.5">Status</th>
                </tr>
            </x-slot:header>
            <tr>
                <td colspan="5" class="px-4 py-6 sm:px-6">
                    <x-empty-state
                        title="Daftar pesanan belum tersedia"
                        description="Pengelolaan pesanan limbah belum dapat digunakan. Data pesanan belum ditampilkan pada halaman ini."
                    />
                </td>
            </tr>
        </x-table>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Detail pesanan" class="min-w-0">
            <x-empty-state
                title="Detail pesanan belum tersedia"
                description="Informasi kontak pemesan, limbah yang dipesan, dan riwayat penanganan belum dapat ditampilkan."
            />
        </x-card>

        @can('order-limbah.approve')
            <x-card title="Persetujuan Admin" class="min-w-0">
                <x-empty-state
                    title="Persetujuan pesanan belum tersedia"
                    description="Pesanan belum dapat disetujui atau ditolak dari halaman ini."
                />
            </x-card>
        @endcan
    </div>
</div>
@endsection
