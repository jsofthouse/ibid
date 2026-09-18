<aside class="w-56 shrink-0 bg-primary text-background min-h-screen">
    <div class="px-4 py-5 border-b border-white/10">
        <p class="font-serif text-lg font-semibold">IBID Admin</p>
    </div>
    <nav class="px-2 py-4 space-y-1 text-sm">
        <a href="{{ route('admin.dashboard') }}"
           class="block rounded px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 font-medium' : 'hover:bg-white/5' }}">
            Dashboard
        </a>

        {{-- Menu Data Karya, Pengajuan, Orang, Kategori, Laporan, Pengaturan menyusul di Fase 2. --}}
    </nav>
</aside>
