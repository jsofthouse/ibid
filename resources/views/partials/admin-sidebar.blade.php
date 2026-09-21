<aside class="w-56 shrink-0 bg-primary text-background min-h-screen">
    <div class="px-4 py-5 border-b border-white/10">
        <p class="font-serif text-lg font-semibold">IBID Admin</p>
    </div>
    <nav class="px-2 py-4 space-y-1 text-sm">
        <a href="{{ route('admin.dashboard') }}"
           class="block rounded px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 font-medium' : 'hover:bg-white/5' }}">
            Dashboard
        </a>

        <a href="{{ route('admin.karya.index') }}"
           class="block rounded px-3 py-2 {{ request()->routeIs('admin.karya.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5' }}">
            Karya
        </a>

        <a href="{{ route('admin.kategori.index') }}"
           class="block rounded px-3 py-2 {{ request()->routeIs('admin.kategori.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5' }}">
            Kategori
        </a>

        <a href="{{ route('admin.orang.index') }}"
           class="block rounded px-3 py-2 {{ request()->routeIs('admin.orang.*') ? 'bg-white/10 font-medium' : 'hover:bg-white/5' }}">
            Orang
        </a>

        <a href="{{ route('admin.log') }}"
           class="block rounded px-3 py-2 {{ request()->routeIs('admin.log') ? 'bg-white/10 font-medium' : 'hover:bg-white/5' }}">
            Log Audit
        </a>

        {{-- Menu Pengajuan, Laporan, Pengaturan menyusul di Fase 2. --}}
    </nav>
</aside>
