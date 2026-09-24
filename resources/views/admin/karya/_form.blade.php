@csrf

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label for="judul" class="block text-sm font-medium mb-1">Judul</label>
        <input id="judul" type="text" name="judul" value="{{ old('judul', $karya->judul ?? '') }}" required autofocus
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('judul')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="subjudul" class="block text-sm font-medium mb-1">Subjudul</label>
        <input id="subjudul" type="text" name="subjudul" value="{{ old('subjudul', $karya->subjudul ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('subjudul')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kategori_id" class="block text-sm font-medium mb-1">Kategori</label>
        <select id="kategori_id" name="kategori_id" required
                class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
            <option value="">— Pilih Kategori —</option>
            @foreach ($daftarKategori as $kategori)
                <option value="{{ $kategori->id }}" @selected((int) old('kategori_id', $karya->kategori_id ?? 0) === $kategori->id)>
                    {{ $kategori->nama }}
                </option>
            @endforeach
        </select>
        @error('kategori_id')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="isbn" class="block text-sm font-medium mb-1">ISBN (opsional)</label>
        <input id="isbn" type="text" name="isbn" value="{{ old('isbn', $karya->isbn ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('isbn')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tahun_terbit" class="block text-sm font-medium mb-1">Tahun Terbit</label>
        <input id="tahun_terbit" type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $karya->tahun_terbit ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('tahun_terbit')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kota_terbit" class="block text-sm font-medium mb-1">Kota Terbit</label>
        <input id="kota_terbit" type="text" name="kota_terbit" value="{{ old('kota_terbit', $karya->kota_terbit ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('kota_terbit')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="edisi" class="block text-sm font-medium mb-1">Edisi</label>
        <input id="edisi" type="text" name="edisi" value="{{ old('edisi', $karya->edisi ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('edisi')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="bahasa" class="block text-sm font-medium mb-1">Bahasa</label>
        <input id="bahasa" type="text" name="bahasa" value="{{ old('bahasa', $karya->bahasa ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('bahasa')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jumlah_halaman" class="block text-sm font-medium mb-1">Jumlah Halaman</label>
        <input id="jumlah_halaman" type="number" name="jumlah_halaman" value="{{ old('jumlah_halaman', $karya->jumlah_halaman ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('jumlah_halaman')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="ukuran" class="block text-sm font-medium mb-1">Ukuran (mis. 14x21 cm)</label>
        <input id="ukuran" type="text" name="ukuran" value="{{ old('ukuran', $karya->ukuran ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('ukuran')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="sinopsis" class="block text-sm font-medium mb-1">Sinopsis</label>
        <textarea id="sinopsis" name="sinopsis" rows="3"
                  class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">{{ old('sinopsis', $karya->sinopsis ?? '') }}</textarea>
        @error('sinopsis')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="kata_kunci" class="block text-sm font-medium mb-1">Kata Kunci</label>
        <input id="kata_kunci" type="text" name="kata_kunci" value="{{ old('kata_kunci', $karya->kata_kunci ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('kata_kunci')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_dibuat" class="block text-sm font-medium mb-1">Tanggal Dibuat</label>
        <input id="tanggal_dibuat" type="date" name="tanggal_dibuat"
               value="{{ old('tanggal_dibuat', optional($karya->tanggal_dibuat ?? null)->format('Y-m-d')) }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('tanggal_dibuat')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_diterbitkan" class="block text-sm font-medium mb-1">Tanggal Diterbitkan</label>
        <input id="tanggal_diterbitkan" type="date" name="tanggal_diterbitkan"
               value="{{ old('tanggal_diterbitkan', optional($karya->tanggal_diterbitkan ?? null)->format('Y-m-d')) }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('tanggal_diterbitkan')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2 flex items-center gap-2">
        <input type="hidden" name="tampil_pra_terbit" value="0">
        <input id="tampil_pra_terbit" type="checkbox" name="tampil_pra_terbit" value="1"
               @checked(old('tampil_pra_terbit', $karya->tampil_pra_terbit ?? true))
               class="rounded border-line">
        <label for="tampil_pra_terbit" class="text-sm">Tampilkan halaman pra-terbit untuk promosi</label>
    </div>

    <div class="sm:col-span-2">
        <label for="cover" class="block text-sm font-medium mb-1">Cover</label>
        @if (($karya->cover_path ?? null))
            <img src="{{ asset('storage-karya/'.$karya->cover_path) }}" alt="Cover saat ini" class="h-32 rounded border border-line mb-2">
        @endif
        <input id="cover" type="file" name="cover" accept="image/jpeg,image/png,image/webp"
               class="w-full text-sm">
        <p class="mt-1 text-xs text-ink/50">JPEG/PNG/WEBP, maksimal 8MB. Dikosongkan berarti cover tidak diganti.</p>
        @error('cover')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    @foreach (\App\Enums\PeranOrang::cases() as $peran)
        <div>
            <label for="peran_{{ $peran->value }}" class="block text-sm font-medium mb-1">{{ $peran->label() }}</label>
            @php
                $pilihan = $daftarPilihanOrang[$peran->value];
                $idTerpilih = array_map('intval', (array) old($peran->value, $peranTerpilih[$peran->value] ?? []));
            @endphp
            <select id="peran_{{ $peran->value }}" name="{{ $peran->value }}[]" multiple size="5"
                    class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                @foreach (['sesuai' => 'Sesuai tugas '.$peran->label(), 'lain' => 'Orang lain'] as $kunciGrup => $labelGrup)
                    @if ($pilihan[$kunciGrup]->isNotEmpty())
                        <optgroup label="{{ $labelGrup }}">
                            @foreach ($pilihan[$kunciGrup] as $orang)
                                <option value="{{ $orang->id }}" @selected(in_array($orang->id, $idTerpilih, true))>
                                    {{ $orang->nama }}{{ $orang->nama_pena ? ' ('.$orang->nama_pena.')' : '' }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
            @error($peran->value)
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</div>

<div class="flex gap-3 mt-4">
    <button type="submit" class="rounded bg-primary text-background text-sm font-medium px-4 py-2 hover:opacity-90">
        Simpan
    </button>
    <a href="{{ route('admin.karya.index') }}" class="rounded border border-line text-sm px-4 py-2 hover:bg-background">
        Batal
    </a>
</div>
