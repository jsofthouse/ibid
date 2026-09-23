@extends('layouts.public')

@section('content')
    {{-- Hero --}}
    <section class="relative pt-8 pb-16 sm:pt-12 sm:pb-20">
        <div class="hidden sm:block absolute top-0 right-0 text-right text-xs tracking-[0.2em] text-ink/50 uppercase leading-relaxed">
            <p>Karya</p>
            <p>Menemukan</p>
            <p>Jalannya</p>
            <div class="w-8 h-px bg-primary/30 mt-2 ml-auto"></div>
        </div>

        <p class="text-xs font-medium tracking-[0.2em] text-ink/60 uppercase">Irfani Book Identity</p>

        <h1 class="mt-4 max-w-lg font-serif text-4xl sm:text-5xl font-semibold text-primary leading-tight">
            Setiap Karya Memiliki Jejak
        </h1>

        <div class="w-10 h-px bg-primary/40 my-5"></div>

        <p class="max-w-xl text-ink/70">
            IBID memberikan identitas unik bagi setiap karya terbitan Penerbit Irfani, agar setiap
            buku memiliki rekam yang jelas dan dapat diverifikasi.
        </p>

        <form method="GET" action="/cari"
              class="mt-8 flex items-center gap-3 bg-card border border-line rounded-full p-2 pl-5 max-w-2xl">
            <svg viewBox="0 0 24 24" class="w-5 h-5 text-ink/40 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="7"/>
                <path d="m20 20-3.5-3.5"/>
            </svg>
            <input type="search" name="q" placeholder="Cari nomor IBID, judul buku, atau nama penulis..."
                   class="flex-1 bg-transparent text-sm placeholder:text-ink/40 focus:outline-none">
            <button type="submit"
                    class="rounded-full bg-primary text-background text-sm font-medium px-6 py-2.5 hover:opacity-90 shrink-0">
                Cari
            </button>
        </form>

        <a href="/cari" class="inline-flex items-center gap-1 mt-4 text-sm text-primary hover:underline">
            Lihat semua karya
            <span aria-hidden="true">&rarr;</span>
        </a>
    </section>

    {{-- 3 Fitur --}}
    <section class="py-12 border-t border-line grid grid-cols-1 sm:grid-cols-3 divide-y divide-line sm:divide-y-0 sm:divide-x sm:divide-line">
        <div class="text-center px-6 py-6 sm:py-0">
            <span class="mx-auto flex items-center justify-center w-14 h-14 rounded-full border border-line">
                <svg viewBox="0 0 24 24" class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 5.5c2-1 5-1 8 0v13c-3-1-6-1-8 0v-13Z"/>
                    <path d="M20 5.5c-2-1-5-1-8 0v13c3-1 6-1 8 0v-13Z"/>
                </svg>
            </span>
            <h3 class="mt-4 font-serif text-lg font-semibold text-primary">Identitas Unik</h3>
            <p class="mt-1 text-sm text-ink/70">Setiap karya memiliki nomor IBID yang unik.</p>
        </div>

        <div class="text-center px-6 py-6 sm:py-0">
            <span class="mx-auto flex items-center justify-center w-14 h-14 rounded-full border border-line">
                <svg viewBox="0 0 24 24" class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 4l7 2.5v5c0 4.5-3 7.5-7 8.5-4-1-7-4-7-8.5v-5L12 4Z"/>
                    <path d="m9.5 12 2 2 3.5-3.5"/>
                </svg>
            </span>
            <h3 class="mt-4 font-serif text-lg font-semibold text-primary">Mudah Diverifikasi</h3>
            <p class="mt-1 text-sm text-ink/70">Cukup pindai QR Code atau cari di website.</p>
        </div>

        <div class="text-center px-6 py-6 sm:py-0">
            <span class="mx-auto flex items-center justify-center w-14 h-14 rounded-full border border-line">
                <svg viewBox="0 0 24 24" class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="9" r="2.5"/>
                    <circle cx="16" cy="10" r="2"/>
                    <path d="M4 19c0-2.8 2.2-5 5-5s5 2.2 5 5"/>
                    <path d="M14.5 14.2c2.2.3 3.5 2 3.5 4.3"/>
                </svg>
            </span>
            <h3 class="mt-4 font-serif text-lg font-semibold text-primary">Untuk Masa Depan</h3>
            <p class="mt-1 text-sm text-ink/70">Mencatat jejak karya untuk generasi berikutnya.</p>
        </div>
    </section>

    {{-- Statistik --}}
    <div class="relative left-1/2 right-1/2 -mx-[50vw] w-screen bg-line/30">
        <div class="mx-auto max-w-5xl px-4 py-10 grid grid-cols-2 sm:grid-cols-4 gap-y-6 divide-x divide-line">
            <div class="text-center px-2">
                <p class="font-serif text-3xl font-semibold text-primary">{{ number_format($jumlahKaryaTerdaftar, 0, ',', '.') }}</p>
                <p class="text-sm text-ink/70 mt-1">Karya Terdaftar</p>
            </div>
            <div class="text-center px-2">
                <p class="font-serif text-3xl font-semibold text-primary">{{ number_format($jumlahPenulis, 0, ',', '.') }}</p>
                <p class="text-sm text-ink/70 mt-1">Penulis</p>
            </div>
            <div class="text-center px-2">
                <p class="font-serif text-3xl font-semibold text-primary">{{ number_format($jumlahKategori, 0, ',', '.') }}</p>
                <p class="text-sm text-ink/70 mt-1">Kategori</p>
            </div>
            <div class="text-center px-2">
                <p class="font-serif text-3xl font-semibold text-primary">Sejak 2024</p>
                <p class="text-sm text-ink/70 mt-1">Bersama Literasi</p>
            </div>
        </div>
    </div>

    {{-- Quote --}}
    <section class="py-16 flex items-start justify-between gap-8 flex-wrap">
        <div class="max-w-xl">
            <p class="font-serif text-2xl italic text-primary leading-snug">
                &ldquo;Setiap karya adalah jejak pemikiran, pengalaman, dan harapan untuk masa depan.&rdquo;
            </p>
            <div class="w-8 h-px bg-primary/30 mt-5 mb-3"></div>
            <p class="text-xs font-medium tracking-[0.2em] text-ink/60 uppercase">Penerbit Irfani</p>
        </div>

        <div class="text-right text-xs tracking-[0.2em] text-ink/50 uppercase leading-relaxed">
            <p>Buku</p>
            <p>Lebih Dari</p>
            <p>Sekadar Kertas</p>
            <div class="w-8 h-px bg-primary/30 mt-2 ml-auto"></div>
        </div>
    </section>
@endsection
