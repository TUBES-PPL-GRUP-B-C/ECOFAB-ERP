@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Manajemen Pengguna & Peran"
        subtitle="Kelola akun internal pabrik dan penetapan hak akses berbasis peran (RBAC)"
        badge="Khusus Admin"
    >
        <x-slot:icon>
            <x-icon name="shield" class="w-5 h-5 text-emerald-400" />
        </x-slot:icon>

        <x-slot:actions>
            <button 
                type="button" 
                onclick="document.getElementById('modal-tambah-user').classList.remove('hidden')" 
                class="px-4 py-2 rounded-xl bg-brand-900 hover:bg-brand-950 text-white font-semibold text-xs shadow-xs transition-colors flex items-center space-x-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Pengguna</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    <!-- Tabel Daftar Pengguna -->
    <x-card title="Daftar Pengguna Internal">
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-6 py-3.5">Nama & Email</th>
                    <th class="px-6 py-3.5">Peran Aktif</th>
                    <th class="px-6 py-3.5">Kelompok Peran</th>
                    <th class="px-6 py-3.5">Terdaftar Sejak</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </x-slot:header>

            @forelse($users as $u)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full bg-brand-800 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800">{{ $u->name }}</p>
                                <p class="text-xs text-slate-500">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        @foreach($u->roles as $role)
                            <x-badge status="proses" :label="strtoupper($role->name)" />
                        @endforeach
                    </td>

                    <td class="px-6 py-4 text-xs font-medium text-slate-600">
                        {{ $u->kelompok_peran }}
                    </td>

                    <td class="px-6 py-4 text-xs text-slate-500">
                        {{ format_tanggal($u->created_at, 'd M Y, H:i') }}
                    </td>

                    <td class="px-6 py-4 text-right space-x-2">
                        @if($u->id !== auth()->id())
                            <x-confirm-delete
                                :action="route('admin.users.destroy', $u)"
                                :itemName="'pengguna ' . $u->name"
                            />
                        @else
                            <span class="text-xs text-slate-400 italic">Akun Anda</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-6 text-center">
                        <x-empty-state title="Belum ada pengguna" description="Belum ada pengguna terdaftar di sistem." />
                    </td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$users" />
    </x-card>
</div>

<!-- Modal Tambah Pengguna Sederhana -->
<div id="modal-tambah-user" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <h3 class="text-base font-bold text-slate-900">Tambah Akun Pengguna Baru</h3>
            <button type="button" onclick="document.getElementById('modal-tambah-user').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-form-input name="name" label="Nama Lengkap" required="true" placeholder="Contoh: Raden Wijaya" />
            <x-form-input name="email" label="Alamat Email" type="email" required="true" placeholder="nama@ecofab.local" />
            <x-form-input name="password" label="Kata Sandi Awal" type="password" required="true" placeholder="Minimal 8 karakter" />

            <div>
                <label for="role" class="block text-xs font-semibold text-slate-700 mb-1.5">Peran Sistem <span class="text-rose-500">*</span></label>
                <select name="role" id="role" required class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 bg-white">
                    <option value="">-- Pilih Peran --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ strtoupper($r->name) }} ({{ match($r->name) {
                            'admin' => 'Akses Penuh',
                            'produksi' => 'Operasional Produksi & BoM',
                            'gudang' => 'Operasional Stok & Gudang',
                            'pembelian' => 'Operasional Pengadaan PO',
                            'hr' => 'Operasional Karyawan & Absensi',
                            'penjualan' => 'Komersial & Bursa Limbah',
                            default => 'Staf Internal'
                        } }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-tambah-user').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold bg-brand-900 hover:bg-brand-950 text-white shadow-xs transition-colors">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
