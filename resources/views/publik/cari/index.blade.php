@extends('layouts.public')

@section('meta')
    <meta name="robots" content="noindex, follow">
@endsection

@section('content')
    <section class="py-8">
        <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-primary">Cari IBID</h1>
        <p class="mt-2 text-sm text-ink/70">Cari berdasarkan nomor IBID, judul buku, nama penulis, atau ISBN.</p>

        <form method="GET" action="{{ route('cari') }}"
              class="mt-6 flex items-center gap-3 bg-card border border-line rounded-full p-2 pl-5 max-w-2xl">
            <svg viewBox="0 0 24 24" class="w-5 h-5 text-ink/40 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="7"/>
                <path d="m20 20-3.5-3.5"/>
            </svg>
            <input type="search" name="q" value="{{ $filter['q'] ?? '' }}"
                   placeholder="Cari nomor IBID, judul buku, atau nama penulis..."
                   class="flex-1 bg-transparent text-sm placeholder:text-ink/40 focus:outline-none">
            <button type="submit"
                    class="rounded-full bg-primary text-background text-sm font-medium px-6 py-2.5 hover:opacity-90 shrink-0">
                Cari
            </button>
        </form>
    </section>

    <section class="pb-16">
        @if ($daftarKarya->isEmpty())
            <p class="text-sm text-ink/60 py-12 text-center">
                @if ($filter['q'] ?? null)
                    Tidak ada karya yang cocok dengan pencarian &ldquo;{{ $filter['q'] }}&rdquo;.
                @else
                    Belum ada karya yang dipublikasikan.
                @endif
            </p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-8">
                @foreach ($daftarKarya as $karya)
                    <a href="{{ route('buku.show', $karya->ibid_number) }}" class="group block">
                        <div class="aspect-2/3 rounded border border-line overflow-hidden bg-card">
                            @if ($karya->cover_path)
                                <img src="{{ asset('storage-karya/'.$karya->cover_path) }}" alt="Cover {{ $karya->judul }}"
                                     class="w-full h-full object-cover group-hover:opacity-90">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-ink/30 text-xs text-center px-2">
                                    Tanpa cover
                                </div>
                            @endif
                        </div>
                        <p class="mt-2 text-sm font-medium text-primary group-hover:underline line-clamp-2">{{ $karya->judul }}</p>
                        @if ($karya->daftarOrang->isNotEmpty())
                            <p class="text-xs text-ink/60 line-clamp-1">
                                {{ $karya->daftarOrang->map(fn ($orang) => $orang->nama_pena ?: $orang->nama)->join(', ') }}
                            </p>
                        @endif
                        <p class="text-xs text-ink/40 mt-0.5">{{ $karya->ibid_number }}</p>
                    </a>
                @endforeach
            </div>

            @include('partials.pagination', ['paginator' => $daftarKarya])
        @endif
    </section>
@endsection
