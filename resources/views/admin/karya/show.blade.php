@extends('layouts.admin')

@section('page-title', $karya->judul)

@section('content')
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.karya.index') }}" class="text-sm text-ink/60 hover:underline">&larr; Kembali ke daftar</a>
        <div class="flex gap-3">
            <a href="{{ route('admin.karya.edit', $karya) }}" class="rounded border border-line text-sm px-4 py-1.5 hover:bg-card">
                Ubah Data
            </a>
            @if ($karya->status_identitas === \App\Enums\StatusIdentitas::BelumBerIbid)
                <form method="POST" action="{{ route('admin.karya.destroy', $karya) }}"
                      onsubmit="return confirm('Hapus karya {{ $karya->judul }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded border border-line text-sm px-4 py-1.5 text-red-700 hover:bg-card">
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Ringkasan --}}
    <section class="bg-card border border-line rounded-lg p-6 mb-6">
        <div class="flex gap-6 flex-wrap">
            @if ($karya->cover_path)
                <img src="{{ asset('storage-karya/'.$karya->cover_path) }}" alt="Cover {{ $karya->judul }}"
                     class="w-40 rounded border border-line shrink-0">
            @endif

            <div class="flex-1 min-w-64">
                <h2 class="font-serif text-xl font-semibold text-primary">{{ $karya->judul }}</h2>
                @if ($karya->subjudul)
                    <p class="text-ink/70">{{ $karya->subjudul }}</p>
                @endif

                <dl class="grid grid-cols-2 gap-x-4 gap-y-1 mt-4 text-sm">
                    <dt class="text-ink/50">Nomor IBID</dt>
                    <dd>{{ $karya->ibid_number ?? '—' }}</dd>

                    <dt class="text-ink/50">Status Produksi</dt>
                    <dd>{{ $karya->status_produksi->label() }}</dd>

                    <dt class="text-ink/50">Status Identitas</dt>
                    <dd>{{ $karya->status_identitas->label() }}</dd>

                    <dt class="text-ink/50">Kategori</dt>
                    <dd>{{ $karya->kategori?->nama ?? '—' }}</dd>

                    <dt class="text-ink/50">ISBN</dt>
                    <dd>{{ $karya->isbn ?? '—' }}</dd>

                    <dt class="text-ink/50">Tahun / Kota Terbit</dt>
                    <dd>{{ $karya->tahun_terbit ?? '—' }} / {{ $karya->kota_terbit ?? '—' }}</dd>

                    <dt class="text-ink/50">Edisi / Bahasa</dt>
                    <dd>{{ $karya->edisi ?? '—' }} / {{ $karya->bahasa ?? '—' }}</dd>

                    <dt class="text-ink/50">Halaman / Ukuran</dt>
                    <dd>{{ $karya->jumlah_halaman ?? '—' }} / {{ $karya->ukuran ?? '—' }}</dd>

                    <dt class="text-ink/50">Tampil Pra-terbit</dt>
                    <dd>{{ $karya->tampil_pra_terbit ? 'Ya' : 'Tidak' }}</dd>
                </dl>

                @foreach (\App\Enums\PeranOrang::cases() as $peran)
                    @php $orangPeran = $karya->daftarOrang->filter(fn ($o) => $o->pivot->role === $peran); @endphp
                    @if ($orangPeran->isNotEmpty())
                        <p class="mt-2 text-sm"><span class="text-ink/50">{{ $peran->label() }}:</span> {{ $orangPeran->pluck('nama')->join(', ') }}</p>
                    @endif
                @endforeach

                @if ($karya->sinopsis)
                    <p class="mt-3 text-sm text-ink/80">{{ $karya->sinopsis }}</p>
                @endif
            </div>
        </div>
    </section>

    {{-- Ubah Status --}}
    <section class="bg-card border border-line rounded-lg p-6 mb-6">
        <h3 class="font-serif text-lg font-semibold text-primary mb-4">Ubah Status</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @php $tujuanProduksi = collect($karya->status_produksi->allowedTransitions())->reject(fn ($s) => $s === \App\Enums\StatusProduksi::Dibatalkan); @endphp
            @if ($tujuanProduksi->isNotEmpty())
                <form method="POST" action="{{ route('admin.karya.status-produksi', $karya) }}" class="space-y-2">
                    @csrf
                    <label class="block text-sm font-medium">Status Produksi</label>
                    <select name="status_produksi" class="w-full rounded border border-line px-3 py-2 text-sm">
                        @foreach ($tujuanProduksi as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <textarea name="alasan" rows="2" placeholder="Alasan (wajib untuk perubahan mundur)"
                              class="w-full rounded border border-line px-3 py-2 text-sm">{{ old('alasan') }}</textarea>
                    @error('status_produksi')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                    @error('alasan')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="rounded bg-primary text-background text-sm font-medium px-4 py-1.5 hover:opacity-90">
                        Ubah Status Produksi
                    </button>
                </form>
            @endif

            @if ($karya->status_identitas !== \App\Enums\StatusIdentitas::BelumBerIbid)
                <form method="POST" action="{{ route('admin.karya.status-identitas', $karya) }}" class="space-y-2">
                    @csrf
                    <label class="block text-sm font-medium">Status Identitas</label>
                    <select name="status_identitas" class="w-full rounded border border-line px-3 py-2 text-sm">
                        @foreach ($karya->status_identitas->allowedTransitions() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <textarea name="alasan" rows="2" placeholder="Alasan (wajib untuk Tidak Aktif/Diarsipkan)"
                              class="w-full rounded border border-line px-3 py-2 text-sm">{{ old('alasan') }}</textarea>
                    @error('status_identitas')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="rounded bg-primary text-background text-sm font-medium px-4 py-1.5 hover:opacity-90">
                        Ubah Status Identitas
                    </button>
                </form>
            @else
                <p class="text-sm text-ink/50">Belum ber-IBID - generate IBID dulu untuk mengubah status identitas.</p>
            @endif
        </div>

        @if (in_array($karya->status_produksi, [\App\Enums\StatusProduksi::Draft, \App\Enums\StatusProduksi::Disetujui, \App\Enums\StatusProduksi::DalamProses], true))
            <div class="mt-6 pt-6 border-t border-line">
                <h4 class="text-sm font-semibold text-red-700 mb-2">Batalkan Penerbitan</h4>
                <form method="POST" action="{{ route('admin.karya.batalkan-penerbitan', $karya) }}" class="space-y-2 max-w-md"
                      onsubmit="return confirm('Yakin membatalkan penerbitan karya ini? Tindakan ini tidak bisa dibatalkan.');">
                    @csrf
                    <textarea name="alasan" rows="2" placeholder="Alasan pembatalan (wajib)" required
                              class="w-full rounded border border-line px-3 py-2 text-sm">{{ old('alasan') }}</textarea>
                    @if ($karya->ibid_number)
                        <input type="text" name="konfirmasi_nomor_ibid" placeholder="Ketik ulang nomor IBID: {{ $karya->ibid_number }}"
                               class="w-full rounded border border-line px-3 py-2 text-sm">
                    @endif
                    @error('alasan')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                    @error('konfirmasi_nomor_ibid')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="rounded bg-red-700 text-white text-sm font-medium px-4 py-1.5 hover:opacity-90">
                        Batalkan Penerbitan
                    </button>
                </form>
            </div>
        @endif
    </section>

    {{-- Riwayat --}}
    <section class="bg-card border border-line rounded-lg p-6">
        <h3 class="font-serif text-lg font-semibold text-primary mb-4">Riwayat</h3>

        <div class="divide-y divide-line">
            @forelse ($riwayat as $log)
                <div class="py-3 text-sm">
                    <div class="flex justify-between flex-wrap gap-2">
                        <span class="font-medium">{{ ucwords(str_replace('_', ' ', $log->aksi)) }}</span>
                        <span class="text-ink/50">{{ $log->created_at?->format('d/m/Y H:i:s') }} &middot; {{ $log->user?->name ?? 'Sistem' }}</span>
                    </div>
                    @if ($log->keterangan)
                        <p class="text-ink/70 mt-1">Alasan: {{ $log->keterangan }}</p>
                    @endif
                    @if ($log->data_before || $log->data_after)
                        <details class="mt-1">
                            <summary class="cursor-pointer text-accent text-xs">Lihat detail</summary>
                            <div class="mt-1 space-y-1">
                                @if ($log->data_before)
                                    <pre class="whitespace-pre-wrap break-all text-xs bg-background rounded p-2">Sebelum: {{ json_encode($log->data_before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @endif
                                @if ($log->data_after)
                                    <pre class="whitespace-pre-wrap break-all text-xs bg-background rounded p-2">Sesudah: {{ json_encode($log->data_after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @endif
                            </div>
                        </details>
                    @endif
                </div>
            @empty
                <p class="text-sm text-ink/50 py-3">Belum ada riwayat.</p>
            @endforelse
        </div>

        @include('partials.pagination', ['paginator' => $riwayat])
    </section>
@endsection
