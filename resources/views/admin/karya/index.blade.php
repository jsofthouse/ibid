@extends('layouts.admin')

@section('page-title', 'Karya')

@section('content')
    @php
        $arahJudul = ($filter['urutkan'] ?? 'created_at') === 'judul' && ($filter['arah'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
        $arahTanggal = ($filter['urutkan'] ?? 'created_at') === 'created_at' && ($filter['arah'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
    @endphp

    <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
        <form method="GET" action="{{ route('admin.karya.index') }}" class="flex gap-2 flex-wrap items-center">
            <input type="search" name="cari" value="{{ $filter['cari'] ?? '' }}" placeholder="Cari judul..."
                   class="rounded border border-line px-3 py-1.5 text-sm w-56">

            <select name="status_produksi" class="rounded border border-line px-2 py-1.5 text-sm">
                <option value="">Semua Status Produksi</option>
                @foreach (\App\Enums\StatusProduksi::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filter['status_produksi'] ?? null) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            <select name="status_identitas" class="rounded border border-line px-2 py-1.5 text-sm">
                <option value="">Semua Status Identitas</option>
                @foreach (\App\Enums\StatusIdentitas::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filter['status_identitas'] ?? null) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="rounded border border-line text-sm px-3 py-1.5 hover:bg-card">Filter</button>
            <a href="{{ route('admin.karya.index') }}" class="text-sm text-ink/60 hover:underline">Reset</a>
        </form>

        <a href="{{ route('admin.karya.create') }}"
           class="rounded bg-primary text-background text-sm font-medium px-4 py-1.5 hover:opacity-90">
            + Tambah Karya
        </a>
    </div>

    <div class="bg-card border border-line rounded-lg overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-line text-left text-ink/70">
                    <th class="px-4 py-2 font-medium">
                        <a href="{{ route('admin.karya.index', array_merge($filter, ['urutkan' => 'judul', 'arah' => $arahJudul])) }}">
                            Judul
                        </a>
                    </th>
                    <th class="px-4 py-2 font-medium">Kategori</th>
                    <th class="px-4 py-2 font-medium">Penulis</th>
                    <th class="px-4 py-2 font-medium">Status Produksi</th>
                    <th class="px-4 py-2 font-medium">Status Identitas</th>
                    <th class="px-4 py-2 font-medium">
                        <a href="{{ route('admin.karya.index', array_merge($filter, ['urutkan' => 'created_at', 'arah' => $arahTanggal])) }}">
                            Dibuat
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarKarya as $karya)
                    <tr class="border-b border-line last:border-0 hover:bg-background/50">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.karya.show', $karya) }}" class="text-primary hover:underline font-medium">
                                {{ $karya->judul }}
                            </a>
                            @if ($karya->ibid_number)
                                <div class="text-xs text-ink/50">{{ $karya->ibid_number }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $karya->kategori?->nama ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $karya->daftarOrang->pluck('nama')->join(', ') ?: '—' }}</td>
                        <td class="px-4 py-2">{{ $karya->status_produksi->label() }}</td>
                        <td class="px-4 py-2">{{ $karya->status_identitas->label() }}</td>
                        <td class="px-4 py-2">{{ $karya->created_at?->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-ink/50">Belum ada karya.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $daftarKarya])
@endsection
