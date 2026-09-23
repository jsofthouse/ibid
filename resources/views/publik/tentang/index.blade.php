@extends('layouts.public')

@section('meta')
    <meta name="robots" content="index, follow">
@endsection

@section('content')
    <section class="py-8 max-w-2xl">
        <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-primary">Tentang IBID</h1>
        <p class="mt-2 text-sm text-ink/70">
            IBID (Irfani Book Identity) adalah sistem identitas internal Penerbit Irfani untuk karya
            yang tidak menggunakan ISBN.
        </p>

        <div class="mt-8 divide-y divide-line border-t border-b border-line">
            <details class="py-4">
                <summary class="cursor-pointer font-medium text-primary">Apa itu IBID?</summary>
                <p class="mt-2 text-sm text-ink/70">
                    IBID (Irfani Book Identity) adalah sistem identitas internal Penerbit Irfani untuk
                    memberi setiap karya terbitan Irfani nomor identitas unik, agar setiap buku
                    memiliki rekam jejak yang jelas dan dapat diverifikasi publik.
                </p>
            </details>

            <details class="py-4">
                <summary class="cursor-pointer font-medium text-primary">Apa bedanya IBID dengan ISBN?</summary>
                <p class="mt-2 text-sm text-ink/70">
                    IBID <strong>bukan pengganti ISBN</strong>. ISBN adalah standar identifikasi buku
                    internasional untuk perdagangan buku secara luas. IBID adalah sistem identitas
                    internal Penerbit Irfani, dipakai untuk karya yang tidak menggunakan ISBN agar
                    tetap punya jejak identitas resmi yang bisa diverifikasi lewat website ini.
                </p>
            </details>

            <details class="py-4">
                <summary class="cursor-pointer font-medium text-primary">Bagaimana cara kerja IBID?</summary>
                <p class="mt-2 text-sm text-ink/70">
                    Setiap karya yang disetujui Penerbit Irfani mendapat nomor IBID unik dengan format
                    <code class="text-primary">IRF-YYYY-NNNNNN</code>. Nomor ini dicetak sebagai QR
                    code statis di buku - memindai atau mencari nomor tersebut di website ini akan
                    menampilkan halaman identitas resmi karya tersebut.
                </p>
            </details>

            <details class="py-4">
                <summary class="cursor-pointer font-medium text-primary">Bagaimana cara memverifikasi nomor IBID?</summary>
                <p class="mt-2 text-sm text-ink/70">
                    Masukkan nomor IBID di halaman
                    <a href="{{ route('verifikasi') }}" class="text-primary hover:underline">Verifikasi IBID</a>,
                    atau cari judul/nama penulisnya lewat halaman
                    <a href="{{ route('cari') }}" class="text-primary hover:underline">Cari IBID</a>.
                </p>
            </details>

            <details class="py-4">
                <summary class="cursor-pointer font-medium text-primary">Bagaimana cara mengajukan penerbitan naskah?</summary>
                <p class="mt-2 text-sm text-ink/70">
                    Isi form di halaman
                    <a href="{{ route('ajukan-penerbitan') }}" class="text-primary hover:underline">Daftarkan Karya</a>
                    dengan identitas Anda, judul naskah, dan unggah berkas naskah. Tim Penerbit Irfani
                    akan meninjau pengajuan Anda.
                </p>
            </details>
        </div>

        <div class="mt-10">
            <h2 class="font-serif text-lg font-semibold text-primary">Kontak Resmi Penerbit Irfani</h2>
            <dl class="mt-3 text-sm text-ink/70 space-y-1">
                <div class="flex gap-2">
                    <dt class="text-ink/50 w-20 shrink-0">Email</dt>
                    <dd>bukuirfani@gmail.com</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="text-ink/50 w-20 shrink-0">Website</dt>
                    <dd>www.penerbitirfani.com</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="text-ink/50 w-20 shrink-0">Instagram</dt>
                    <dd>@penerbitirfani</dd>
                </div>
            </dl>
        </div>
    </section>
@endsection
