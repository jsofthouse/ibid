@extends('layouts.public')

@section('meta')
    <meta property="og:type" content="book">
    <meta property="og:title" content="{{ $judul }}">
    @if ($coverUrl)
        <meta property="og:image" content="{{ $coverUrl }}">
    @endif
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($sinopsis ?? $judul, 200) }}">
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
            <h1 class="font-serif text-2xl font-semibold text-primary">{{ $judul }}</h1>
            @if ($subjudul)
                <p class="text-ink/70 mt-1">{{ $subjudul }}</p>
            @endif

            @foreach ($daftarPeran as $labelPeran => $namaOrang)
                <p class="mt-2 text-sm"><span class="text-ink/50">{{ $labelPeran }}:</span> {{ $namaOrang->join(', ') }}</p>
            @endforeach

            <dl class="grid grid-cols-2 gap-x-4 gap-y-1 mt-4 text-sm">
                <dt class="text-ink/50">Nomor IBID</dt>
                <dd>{{ $ibidNumber }}</dd>

                @if ($isbn)
                    <dt class="text-ink/50">ISBN</dt>
                    <dd>{{ $isbn }}</dd>
                @endif

                @if ($kategori)
                    <dt class="text-ink/50">Kategori</dt>
                    <dd>{{ $kategori }}</dd>
                @endif

                @if ($tahunTerbit)
                    <dt class="text-ink/50">Tahun Terbit</dt>
                    <dd>{{ $tahunTerbit }}</dd>
                @endif

                @if ($kotaTerbit)
                    <dt class="text-ink/50">Kota Terbit</dt>
                    <dd>{{ $kotaTerbit }}</dd>
                @endif

                @if ($edisi)
                    <dt class="text-ink/50">Edisi</dt>
                    <dd>{{ $edisi }}</dd>
                @endif

                @if ($bahasa)
                    <dt class="text-ink/50">Bahasa</dt>
                    <dd>{{ $bahasa }}</dd>
                @endif

                @if ($jumlahHalaman)
                    <dt class="text-ink/50">Jumlah Halaman</dt>
                    <dd>{{ $jumlahHalaman }}</dd>
                @endif

                @if ($ukuran)
                    <dt class="text-ink/50">Ukuran</dt>
                    <dd>{{ $ukuran }}</dd>
                @endif
            </dl>

            @if ($sinopsis)
                <p class="mt-4 text-sm text-ink/80">{{ $sinopsis }}</p>
            @endif

            <p class="mt-6 text-xs text-ink/50">IBID (Irfani Book Identity) bukan pengganti ISBN.</p>
        </div>
    </div>
@endsection
