@extends('layouts.public')

@section('meta')
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:type" content="book">
    <meta property="og:title" content="{{ $judul }}">
    @if ($coverUrl)
        <meta property="og:image" content="{{ $coverUrl }}">
    @endif
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($sinopsis ?? 'Terdaftar dalam Sistem IBID Penerbit Irfani.', 200) }}">
@endsection

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-[240px_1fr] gap-8">
        <div>
            @if ($coverUrl)
                <img src="{{ $coverUrl }}" alt="Cover {{ $judul }}" class="w-full rounded border border-line">
            @else
                <div class="w-full aspect-[2/3] rounded border border-line bg-card flex items-center justify-center text-ink/40 text-sm">
                    Cover belum tersedia
                </div>
            @endif
        </div>

        <div>
            <span class="inline-block rounded-full bg-accent/10 text-accent text-xs font-medium px-3 py-1 mb-3">
                Terdaftar dalam Sistem IBID &mdash; Dalam Proses Penerbitan
            </span>

            <h1 class="font-serif text-2xl font-semibold text-primary">{{ $judul }}</h1>
            @if ($subjudul)
                <p class="text-ink/70 mt-1">{{ $subjudul }}</p>
            @endif

            @if ($daftarPenulis->isNotEmpty())
                <p class="mt-3 text-sm"><span class="text-ink/50">Penulis:</span> {{ $daftarPenulis->join(', ') }}</p>
            @endif

            @if ($kategori)
                <p class="text-sm"><span class="text-ink/50">Kategori:</span> {{ $kategori }}</p>
            @endif

            <p class="text-sm mt-1"><span class="text-ink/50">Nomor IBID:</span> {{ $ibidNumber }}</p>

            @if ($sinopsis)
                <p class="mt-4 text-sm text-ink/80">{{ $sinopsis }}</p>
            @endif

            <p class="mt-6 text-xs text-ink/50">
                IBID (Irfani Book Identity) bukan pengganti ISBN. Buku ini masih dalam proses penerbitan oleh Penerbit Irfani.
            </p>
        </div>
    </div>
@endsection
