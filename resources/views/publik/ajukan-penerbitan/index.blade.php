@extends('layouts.public')

@section('meta')
    <meta name="robots" content="noindex, follow">
@endsection

@section('content')
    <section class="py-8 max-w-2xl">
        <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-primary">Ajukan Penerbitan</h1>
        <p class="mt-2 text-sm text-ink/70">
            Isi form berikut untuk mengajukan naskah Anda ke Penerbit Irfani. Naskah wajib diunggah,
            surat keaslian karya bersifat opsional.
        </p>

        @if (session('sukses'))
            <div class="mt-6 rounded border border-primary/30 bg-primary/10 px-4 py-3 text-sm text-primary">
                {{ session('sukses') }}
            </div>
        @endif

        <form method="POST" action="{{ route('ajukan-penerbitan.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf

            <fieldset class="space-y-4">
                <legend class="font-medium text-primary mb-1">Identitas Pengaju</legend>

                <div>
                    <label for="nama" class="block text-sm font-medium mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                           class="w-full rounded border border-line px-3 py-2 text-sm">
                    @error('nama')
                        <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_pena" class="block text-sm font-medium mb-1">Nama Pena (opsional)</label>
                    <input type="text" name="nama_pena" id="nama_pena" value="{{ old('nama_pena') }}"
                           class="w-full rounded border border-line px-3 py-2 text-sm">
                    @error('nama_pena')
                        <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="w-full rounded border border-line px-3 py-2 text-sm">
                        @error('email')
                            <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nomor_wa" class="block text-sm font-medium mb-1">Nomor WhatsApp (opsional)</label>
                        <input type="text" name="nomor_wa" id="nomor_wa" value="{{ old('nomor_wa') }}"
                               class="w-full rounded border border-line px-3 py-2 text-sm">
                        @error('nomor_wa')
                            <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="alamat" class="block text-sm font-medium mb-1">Alamat (opsional)</label>
                    <textarea name="alamat" id="alamat" rows="2"
                              class="w-full rounded border border-line px-3 py-2 text-sm">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="kota" class="block text-sm font-medium mb-1">Kota (opsional)</label>
                        <input type="text" name="kota" id="kota" value="{{ old('kota') }}"
                               class="w-full rounded border border-line px-3 py-2 text-sm">
                        @error('kota')
                            <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="provinsi" class="block text-sm font-medium mb-1">Provinsi (opsional)</label>
                        <input type="text" name="provinsi" id="provinsi" value="{{ old('provinsi') }}"
                               class="w-full rounded border border-line px-3 py-2 text-sm">
                        @error('provinsi')
                            <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="font-medium text-primary mb-1">Karya</legend>

                <div>
                    <label for="judul" class="block text-sm font-medium mb-1">Judul Naskah</label>
                    <input type="text" name="judul" id="judul" value="{{ old('judul') }}" required
                           class="w-full rounded border border-line px-3 py-2 text-sm">
                    @error('judul')
                        <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kategori_id" class="block text-sm font-medium mb-1">Kategori</label>
                    <select name="kategori_id" id="kategori_id" required
                            class="w-full rounded border border-line px-3 py-2 text-sm">
                        <option value="">Pilih kategori</option>
                        @foreach ($daftarKategori as $kategori)
                            <option value="{{ $kategori->id }}" @selected((string) old('kategori_id') === (string) $kategori->id)>
                                {{ $kategori->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')
                        <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sinopsis" class="block text-sm font-medium mb-1">Sinopsis (opsional)</label>
                    <textarea name="sinopsis" id="sinopsis" rows="4"
                              class="w-full rounded border border-line px-3 py-2 text-sm">{{ old('sinopsis') }}</textarea>
                    @error('sinopsis')
                        <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="font-medium text-primary mb-1">Berkas</legend>

                <div>
                    <label for="naskah" class="block text-sm font-medium mb-1">Naskah (PDF/DOC/DOCX, maks 10MB)</label>
                    <input type="file" name="naskah" id="naskah" required accept=".pdf,.doc,.docx"
                           class="w-full rounded border border-line px-3 py-2 text-sm">
                    @error('naskah')
                        <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="surat_keaslian" class="block text-sm font-medium mb-1">
                        Surat Keaslian Karya (opsional, PDF/DOC/DOCX, maks 10MB)
                    </label>
                    <input type="file" name="surat_keaslian" id="surat_keaslian" accept=".pdf,.doc,.docx"
                           class="w-full rounded border border-line px-3 py-2 text-sm">
                    @error('surat_keaslian')
                        <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <button type="submit"
                    class="rounded-full bg-primary text-background text-sm font-medium px-6 py-2.5 hover:opacity-90">
                Kirim Pengajuan
            </button>
        </form>
    </section>
@endsection
