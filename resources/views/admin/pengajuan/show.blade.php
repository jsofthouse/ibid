@extends('layouts.admin')

@section('page-title', $pengajuan->judul)

@section('content')
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.pengajuan.index') }}" class="text-sm text-ink/60 hover:underline">&larr; Kembali ke daftar</a>
    </div>

    {{-- Ringkasan --}}
    <section class="bg-card border border-line rounded-lg p-6 mb-6">
        <h2 class="font-serif text-xl font-semibold text-primary">{{ $pengajuan->judul }}</h2>

        <dl class="grid grid-cols-2 gap-x-4 gap-y-1 mt-4 text-sm">
            <dt class="text-ink/50">Status</dt>
            <dd>{{ $pengajuan->status->label() }}</dd>

            <dt class="text-ink/50">Kategori</dt>
            <dd>{{ $pengajuan->kategori?->nama ?? '—' }}</dd>

            <dt class="text-ink/50">Pengaju</dt>
            <dd>{{ $pengajuan->nama }}{{ $pengajuan->nama_pena ? ' ('.$pengajuan->nama_pena.')' : '' }}</dd>

            <dt class="text-ink/50">Email</dt>
            <dd>{{ $pengajuan->email }}</dd>

            <dt class="text-ink/50">WhatsApp</dt>
            <dd>{{ $pengajuan->nomor_wa ?? '—' }}</dd>

            <dt class="text-ink/50">Kota / Provinsi</dt>
            <dd>{{ $pengajuan->kota ?? '—' }} / {{ $pengajuan->provinsi ?? '—' }}</dd>

            <dt class="text-ink/50">Alamat</dt>
            <dd>{{ $pengajuan->alamat ?? '—' }}</dd>

            <dt class="text-ink/50">Masuk</dt>
            <dd>{{ $pengajuan->created_at?->format('d/m/Y H:i') }}</dd>
        </dl>

        @if ($pengajuan->sinopsis)
            <p class="mt-3 text-sm text-ink/80">{{ $pengajuan->sinopsis }}</p>
        @endif

        <div class="flex gap-3 mt-4">
            <a href="{{ route('admin.berkas', ['path' => $pengajuan->naskah_path]) }}" target="_blank" rel="noopener"
               class="rounded border border-line text-sm px-4 py-1.5 hover:bg-background">
                Unduh Naskah
            </a>
            @if ($pengajuan->surat_keaslian_path)
                <a href="{{ route('admin.berkas', ['path' => $pengajuan->surat_keaslian_path]) }}" target="_blank" rel="noopener"
                   class="rounded border border-line text-sm px-4 py-1.5 hover:bg-background">
                    Unduh Surat Keaslian
                </a>
            @endif
        </div>

        @if ($pengajuan->catatan_admin)
            <p class="mt-3 text-sm"><span class="text-ink/50">Catatan admin:</span> {{ $pengajuan->catatan_admin }}</p>
        @endif

        @if ($pengajuan->karya)
            <p class="mt-3 text-sm">
                Sudah dikonversi menjadi karya:
                <a href="{{ route('admin.karya.show', $pengajuan->karya) }}" class="text-primary hover:underline font-medium">
                    {{ $pengajuan->karya->judul }}
                </a>
            </p>
        @endif
    </section>

    {{-- Ubah Status --}}
    @if ($pengajuan->karya_id === null && $pengajuan->status->allowedTransitions() !== [])
        <section class="bg-card border border-line rounded-lg p-6">
            <h3 class="font-serif text-lg font-semibold text-primary mb-4">Ubah Status</h3>

            <div class="flex flex-wrap gap-6">
                @foreach ($pengajuan->status->allowedTransitions() as $tujuan)
                    <form method="POST" action="{{ route('admin.pengajuan.status', $pengajuan) }}" class="space-y-2 max-w-sm">
                        @csrf
                        <input type="hidden" name="status" value="{{ $tujuan->value }}">

                        @if ($tujuan === \App\Enums\StatusPengajuan::Ditolak)
                            <textarea name="catatan_admin" rows="2" placeholder="Alasan penolakan (wajib)" required
                                      class="w-full rounded border border-line px-3 py-2 text-sm">{{ old('catatan_admin') }}</textarea>
                        @endif

                        <button type="submit"
                                class="rounded {{ $tujuan === \App\Enums\StatusPengajuan::Ditolak ? 'bg-red-700' : 'bg-primary' }} text-background text-sm font-medium px-4 py-1.5 hover:opacity-90">
                            @if ($tujuan === \App\Enums\StatusPengajuan::Disetujui)
                                Setujui &amp; Jadikan Karya
                            @else
                                Ubah ke {{ $tujuan->label() }}
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>

            @error('status')
                <p class="text-sm text-red-700 mt-2">{{ $message }}</p>
            @enderror
            @error('catatan_admin')
                <p class="text-sm text-red-700 mt-2">{{ $message }}</p>
            @enderror
        </section>
    @endif
@endsection
