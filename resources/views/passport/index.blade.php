@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Eco-Passport Produk" subtitle="Daftar paspor digital produk berdasarkan Work Order" />

    @can('passport.create')
        <x-card title="Buat Draft Eco-Passport">
            @if ($workOrders->isEmpty())
                <p class="text-sm text-slate-600">Belum ada Work Order selesai yang bisa dipilih.</p>
                @error('work_order_id')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @else
                <form method="POST" action="{{ route('passport.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="work_order_id" class="mb-1.5 block text-sm font-medium text-slate-700">Work Order selesai</label>
                        <select id="work_order_id" name="work_order_id" required
                            class="block w-full rounded-lg border-slate-300 text-sm focus:border-brand-700 focus:ring-brand-700 sm:max-w-lg"
                            @error('work_order_id') aria-invalid="true" aria-describedby="work_order_id_error" @enderror>
                            <option value="">Pilih Work Order</option>
                            @foreach ($workOrders as $workOrder)
                                <option value="{{ $workOrder->id }}" @selected(old('work_order_id') === (string) $workOrder->id)>
                                    {{ $workOrder->wo_number }} — {{ $workOrder->product_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('work_order_id')
                            <p id="work_order_id_error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Simpan draft</button>
                </form>
            @endif
        </x-card>
    @endcan

    <x-card title="Daftar Eco-Passport">
        @if ($passports->isEmpty())
            <x-empty-state title="Belum ada Eco-Passport pada halaman ini" />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Kode</th>
                        <th scope="col" class="px-6 py-3.5">Work Order</th>
                        <th scope="col" class="px-6 py-3.5">Produk</th>
                        <th scope="col" class="px-6 py-3.5">Status</th>
                    </tr>
                </x-slot:header>
                @foreach ($passports as $passport)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 font-mono text-xs">{{ $passport->unique_code }}</td>
                        <td class="px-6 py-4">{{ $passport->workOrder->wo_number }}</td>
                        <td class="px-6 py-4">{{ $passport->workOrder->product_name }}</td>
                        <td class="px-6 py-4">{{ ucfirst($passport->publication_status) }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
        <x-pagination :paginator="$passports" />
    </x-card>
</div>
@endsection
