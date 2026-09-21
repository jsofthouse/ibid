@csrf

<div>
    <label for="nama" class="block text-sm font-medium mb-1">Nama Kategori</label>
    <input id="nama" type="text" name="nama" value="{{ old('nama', $kategori->nama ?? '') }}" required autofocus
           class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
    @error('nama')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>

<div class="flex gap-3">
    <button type="submit" class="rounded bg-primary text-background text-sm font-medium px-4 py-2 hover:opacity-90">
        Simpan
    </button>
    <a href="{{ route('admin.kategori.index') }}" class="rounded border border-line text-sm px-4 py-2 hover:bg-background">
        Batal
    </a>
</div>
