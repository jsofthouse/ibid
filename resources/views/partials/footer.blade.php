<footer class="bg-card border-t border-line mt-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-12 grid grid-cols-1 sm:grid-cols-[1.4fr_1fr_1fr_1fr] gap-10 text-sm">
        <div>
            <p class="text-ink/70 max-w-xs">
                Mendukung lahirnya karya-karya bermakna untuk masyarakat yang lebih baik.
            </p>
            <div class="w-8 h-px bg-primary/30 mt-4"></div>
        </div>

        <div>
            <p class="font-medium text-primary mb-3">Tautan</p>
            <ul class="space-y-2 text-ink/70">
                <li><a href="{{ url('/') }}" class="hover:text-primary">Beranda</a></li>
                <li><a href="{{ route('cari') }}" class="hover:text-primary">Cari IBID</a></li>
                <li><a href="{{ route('tentang') }}" class="hover:text-primary">Tentang</a></li>
                <li><a href="{{ route('tentang') }}" class="hover:text-primary">Kontak</a></li>
            </ul>
        </div>

        <div>
            <p class="font-medium text-primary mb-3">Bantuan</p>
            <ul class="space-y-2 text-ink/70">
                <li><a href="#" class="hover:text-primary">Panduan Pengguna</a></li>
                <li><a href="#" class="hover:text-primary">Pertanyaan Umum</a></li>
                <li><a href="#" class="hover:text-primary">Kebijakan Privasi</a></li>
                <li><a href="#" class="hover:text-primary">Syarat &amp; Ketentuan</a></li>
            </ul>
        </div>

        <div>
            <p class="font-medium text-primary mb-3">Ikuti Kami</p>
            <div class="flex items-center gap-3">
                <a href="#" aria-label="Instagram"
                   class="w-9 h-9 rounded-full border border-line flex items-center justify-center text-ink/70 hover:border-primary hover:text-primary">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="5"/>
                        <circle cx="12" cy="12" r="4"/>
                        <circle cx="17" cy="7" r="0.8" fill="currentColor" stroke="none"/>
                    </svg>
                </a>
                <a href="#" aria-label="YouTube"
                   class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center text-background">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor">
                        <path d="M8 6.5v11l9-5.5-9-5.5Z"/>
                    </svg>
                </a>
                <a href="#" aria-label="Email"
                   class="w-9 h-9 rounded-full border border-line flex items-center justify-center text-ink/70 hover:border-primary hover:text-primary">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="m4 7 8 6 8-6"/>
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-ink/60 text-xs">
                &copy; {{ date('Y') }} Penerbit Irfani.<br>
                Semua hak dilindungi.
            </p>
        </div>
    </div>
</footer>
