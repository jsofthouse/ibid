@csrf

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="nama" class="block text-sm font-medium mb-1">Nama</label>
        <input id="nama" type="text" name="nama" value="{{ old('nama', $orang->nama ?? '') }}" required autofocus
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('nama')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nama_pena" class="block text-sm font-medium mb-1">Nama Pena</label>
        <input id="nama_pena" type="text" name="nama_pena" value="{{ old('nama_pena', $orang->nama_pena ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('nama_pena')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium mb-1">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $orang->email ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('email')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nomor_wa" class="block text-sm font-medium mb-1">Nomor WA</label>
        <input id="nomor_wa" type="text" name="nomor_wa" value="{{ old('nomor_wa', $orang->nomor_wa ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('nomor_wa')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kota" class="block text-sm font-medium mb-1">Kota</label>
        <input id="kota" type="text" name="kota" value="{{ old('kota', $orang->kota ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('kota')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="provinsi" class="block text-sm font-medium mb-1">Provinsi</label>
        <input id="provinsi" type="text" name="provinsi" value="{{ old('provinsi', $orang->provinsi ?? '') }}"
               class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        @error('provinsi')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="alamat" class="block text-sm font-medium mb-1">Alamat</label>
        <textarea id="alamat" name="alamat" rows="2"
                  class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">{{ old('alamat', $orang->alamat ?? '') }}</textarea>
        @error('alamat')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="rounded bg-primary text-background text-sm font-medium px-4 py-2 hover:opacity-90">
        Simpan
    </button>
    <a href="{{ route('admin.orang.index') }}" class="rounded border border-line text-sm px-4 py-2 hover:bg-background">
        Batal
    </a>
</div>
