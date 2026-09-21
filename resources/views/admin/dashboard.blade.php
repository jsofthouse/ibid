@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
    <p class="text-sm text-ink/70 mb-6">Selamat datang, <strong>{{ auth()->user()->name }}</strong>.</p>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-card border border-line rounded-lg p-4">
            <p class="text-xs text-ink/50">Total Karya</p>
            <p class="font-serif text-2xl font-semibold text-primary mt-1">{{ $totalKarya }}</p>
        </div>
        <div class="bg-card border border-line rounded-lg p-4">
            <p class="text-xs text-ink/50">Total IBID</p>
            <p class="font-serif text-2xl font-semibold text-primary mt-1">{{ $totalIbid }}</p>
        </div>
        <div class="bg-card border border-line rounded-lg p-4">
            <p class="text-xs text-ink/50">IBID Tahun {{ now()->year }}</p>
            <p class="font-serif text-2xl font-semibold text-primary mt-1">{{ $ibidTahunBerjalan }}</p>
        </div>
        <div class="bg-card border border-line rounded-lg p-4">
            <p class="text-xs text-ink/50">Jumlah Orang</p>
            <p class="font-serif text-2xl font-semibold text-primary mt-1">{{ $jumlahOrang }}</p>
        </div>
        <div class="bg-card border border-line rounded-lg p-4">
            <p class="text-xs text-ink/50">Dengan ISBN</p>
            <p class="font-serif text-2xl font-semibold text-primary mt-1">{{ $denganIsbn }}</p>
        </div>
        <div class="bg-card border border-line rounded-lg p-4">
            <p class="text-xs text-ink/50">Tanpa ISBN</p>
            <p class="font-serif text-2xl font-semibold text-primary mt-1">{{ $tanpaIsbn }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-card border border-line rounded-lg p-6">
            <h3 class="font-serif text-base font-semibold text-primary mb-3">Per Kategori</h3>
            <ul class="text-sm divide-y divide-line">
                @forelse ($perKategori as $baris)
                    <li class="py-2 flex justify-between">
                        <span>{{ $baris->kategori }}</span>
                        <span class="font-medium">{{ $baris->total }}</span>
                    </li>
                @empty
                    <li class="py-2 text-ink/50">Belum ada data.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-card border border-line rounded-lg p-6">
            <h3 class="font-serif text-base font-semibold text-primary mb-3">Per Status Produksi</h3>
            <ul class="text-sm divide-y divide-line">
                @forelse ($perStatusProduksi as $baris)
                    <li class="py-2 flex justify-between">
                        <span>{{ $baris->status_produksi->label() }}</span>
                        <span class="font-medium">{{ $baris->total }}</span>
                    </li>
                @empty
                    <li class="py-2 text-ink/50">Belum ada data.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-card border border-line rounded-lg p-6">
            <h3 class="font-serif text-base font-semibold text-primary mb-3">Per Status Identitas</h3>
            <ul class="text-sm divide-y divide-line">
                @forelse ($perStatusIdentitas as $baris)
                    <li class="py-2 flex justify-between">
                        <span>{{ $baris->status_identitas->label() }}</span>
                        <span class="font-medium">{{ $baris->total }}</span>
                    </li>
                @empty
                    <li class="py-2 text-ink/50">Belum ada data.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
