@extends('layouts.admin')

@section('page-title', 'Ganti Password')

@section('content')
    <div class="bg-card border border-line rounded-lg p-6 max-w-md">
        <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="password_saat_ini" class="block text-sm font-medium mb-1">Password Saat Ini</label>
                <input id="password_saat_ini" type="password" name="password_saat_ini" required autofocus
                       class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                @error('password_saat_ini')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_baru" class="block text-sm font-medium mb-1">Password Baru</label>
                <input id="password_baru" type="password" name="password_baru" required
                       class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                <p class="mt-1 text-xs text-ink/50">Minimal 8 karakter, kombinasi huruf dan angka.</p>
                @error('password_baru')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_baru_confirmation" class="block text-sm font-medium mb-1">Ulangi Password Baru</label>
                <input id="password_baru_confirmation" type="password" name="password_baru_confirmation" required
                       class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
            </div>

            <button type="submit" class="rounded bg-primary text-background text-sm font-medium px-4 py-2 hover:opacity-90">
                Simpan Password Baru
            </button>
        </form>
    </div>
@endsection
