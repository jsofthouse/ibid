@extends('layouts.admin')

@section('page-title', 'Pengajuan')

@section('content')
    @php
        $arahJudul = ($filter['urutkan'] ?? 'created_at') === 'judul' && ($filter['arah'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
        $arahTanggal = ($filter['urutkan'] ?? 'created_at') === 'created_at' && ($filter['arah'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
    @endphp

    <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
        <form method="GET" action="{{ route('admin.pengajuan.index') }}" class="flex gap-2 flex-wrap items-center">
            <select name="status" class="rounded border border-line px-2 py-1.5 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Enums\StatusPengajuan::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filter['status'] ?? null) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="rounded border border-line text-sm px-3 py-1.5 hover:bg-card">Filter</button>
            <a href="{{ route('admin.pengajuan.index') }}" class="text-sm text-ink/60 hover:underline">Reset</a>
        </form>
    </div>

    <div class="bg-card border border-line rounded-lg overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-line text-left text-ink/70">
                    <th class="px-4 py-2 font-medium">
                        <a href="{{ route('admin.pengajuan.index', array_merge($filter, ['urutkan' => 'judul', 'arah' => $arahJudul])) }}">
                            Judul
                        </a>
                    </th>
                    <th class="px-4 py-2 font-medium">Pengaju</th>
                    <th class="px-4 py-2 font-medium">Kategori</th>
                    <th class="px-4 py-2 font-medium">Status</th>
                    <th class="px-4 py-2 font-medium">
                        <a href="{{ route('admin.pengajuan.index', array_merge($filter, ['urutkan' => 'created_at', 'arah' => $arahTanggal])) }}">
                            Masuk
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarPengajuan as $pengajuan)
                    <tr class="border-b border-line last:border-0 hover:bg-background/50">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.pengajuan.show', $pengajuan) }}" class="text-primary hover:underline font-medium">
                                {{ $pengajuan->judul }}
                            </a>
                        </td>
                        <td class="px-4 py-2">{{ $pengajuan->nama }}</td>
                        <td class="px-4 py-2">{{ $pengajuan->kategori?->nama ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $pengajuan->status->label() }}</td>
                        <td class="px-4 py-2">{{ $pengajuan->created_at?->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-ink/50">Belum ada pengajuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $daftarPengajuan])
@endsection
