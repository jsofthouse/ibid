@extends('layouts.public')

@section('meta')
    <meta name="robots" content="noindex, follow">
@endsection

@section('content')
    <section class="py-8 max-w-xl">
        <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-primary">Verifikasi IBID</h1>
        <p class="mt-2 text-sm text-ink/70">Masukkan nomor IBID untuk memeriksa keabsahannya.</p>

        <form method="GET" action="{{ route('verifikasi') }}"
              class="mt-6 flex items-center gap-3 bg-card border border-line rounded-full p-2 pl-5">
            <input type="text" name="nomor" value="{{ $nomor }}" placeholder="Contoh: IRF-2026-000123"
                   class="flex-1 bg-transparent text-sm placeholder:text-ink/40 focus:outline-none">
            <button type="submit"
                    class="rounded-full bg-primary text-background text-sm font-medium px-6 py-2.5 hover:opacity-90 shrink-0">
                Verifikasi
            </button>
        </form>

        @error('nomor')
            <p class="text-sm text-red-700 mt-2">{{ $message }}</p>
        @enderror

        @if ($hasil)
            <div class="mt-6 rounded-lg border border-line bg-card p-5">
                @if ($hasil['status'] === 'valid')
                    <p class="text-sm font-medium text-primary">Nomor IBID valid dan terdaftar.</p>
                    <p class="text-sm text-ink/70 mt-1">{{ $hasil['ibidNumber'] }}</p>
                    <a href="{{ route('buku.show', $hasil['ibidNumber']) }}"
                       class="inline-block mt-3 text-sm text-primary hover:underline">
                        Lihat halaman identitas karya &rarr;
                    </a>
                @elseif ($hasil['status'] === 'dalam_proses')
                    <p class="text-sm font-medium text-primary">Terdaftar dalam Sistem IBID &mdash; Dalam Proses Penerbitan.</p>
                    <p class="text-sm text-ink/70 mt-1">{{ $hasil['ibidNumber'] }}</p>
                @else
                    <p class="text-sm font-medium text-ink/70">Nomor IBID tidak ditemukan atau tidak berlaku.</p>
                @endif
            </div>
        @endif
    </section>
@endsection
