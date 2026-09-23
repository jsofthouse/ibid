<header class="bg-background">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-5 flex items-center justify-between gap-6 flex-wrap">
        <nav class="flex items-center gap-6 text-sm">
            <a href="{{ url('/') }}"
               class="pb-1 border-b-2 {{ request()->is('/') ? 'border-primary font-medium text-primary' : 'border-transparent text-ink/80 hover:text-primary' }}">
                Beranda
            </a>
            <a href="{{ route('cari') }}"
               class="pb-1 border-b-2 {{ request()->routeIs('cari') ? 'border-primary font-medium text-primary' : 'border-transparent text-ink/80 hover:text-primary' }}">
                Cari IBID
            </a>
            <a href="/tentang"
               class="pb-1 border-b-2 {{ request()->is('tentang') ? 'border-primary font-medium text-primary' : 'border-transparent text-ink/80 hover:text-primary' }}">
                Tentang
            </a>
            <a href="/tentang" class="pb-1 border-b-2 border-transparent text-ink/80 hover:text-primary">
                Kontak
            </a>
        </nav>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.login') }}"
               class="rounded-full bg-primary text-background text-sm font-medium px-5 py-2 hover:opacity-90">
                Masuk
            </a>
            <a href="{{ route('ajukan-penerbitan') }}"
               class="rounded-full border border-primary text-primary text-sm font-medium px-5 py-2 hover:bg-primary/5">
                Daftarkan Karya
            </a>
        </div>
    </div>
</header>
