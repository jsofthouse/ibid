@extends('layouts.admin')

@section('page-title', 'Orang')

@section('content')
    @php
        $arahNama = ($filter['urutkan'] ?? 'nama') === 'nama' && ($filter['arah'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
        $arahTanggal = ($filter['urutkan'] ?? 'nama') === 'created_at' && ($filter['arah'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
    @endphp

    <div class="flex items-center justify-between mb-4">
        <form method="GET" action="{{ route('admin.orang.index') }}" class="flex gap-2">
            <input type="search" name="cari" value="{{ $filter['cari'] ?? '' }}" placeholder="Cari nama..."
                   class="rounded border border-line px-3 py-1.5 text-sm w-64">
            <button type="submit" class="rounded border border-line text-sm px-3 py-1.5 hover:bg-card">Cari</button>
        </form>

        <a href="{{ route('admin.orang.create') }}"
           class="rounded bg-primary text-background text-sm font-medium px-4 py-1.5 hover:opacity-90">
            + Tambah Orang
        </a>
    </div>

    <div class="bg-card border border-line rounded-lg overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-line text-left text-ink/70">
                    <th class="px-4 py-2 font-medium">
                        <a href="{{ route('admin.orang.index', array_merge($filter, ['urutkan' => 'nama', 'arah' => $arahNama])) }}">
                            Nama
                        </a>
                    </th>
                    <th class="px-4 py-2 font-medium">Nama Pena</th>
                    <th class="px-4 py-2 font-medium">Kontak</th>
                    <th class="px-4 py-2 font-medium">
                        <a href="{{ route('admin.orang.index', array_merge($filter, ['urutkan' => 'created_at', 'arah' => $arahTanggal])) }}">
                            Dibuat
                        </a>
                    </th>
                    <th class="px-4 py-2 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarOrang as $orang)
                    <tr class="border-b border-line last:border-0">
                        <td class="px-4 py-2">{{ $orang->nama }}</td>
                        <td class="px-4 py-2">{{ $orang->nama_pena ?? '—' }}</td>
                        <td class="px-4 py-2">
                            <div>{{ $orang->email ?? '—' }}</div>
                            <div class="text-ink/50">{{ $orang->nomor_wa ?? '' }}</div>
                        </td>
                        <td class="px-4 py-2">{{ $orang->created_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-right space-x-3">
                            <a href="{{ route('admin.orang.edit', $orang) }}" class="text-accent hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('admin.orang.destroy', $orang) }}" class="inline"
                                  onsubmit="return confirm('Hapus data {{ $orang->nama }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-ink/50">Belum ada data orang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $daftarOrang])
@endsection
