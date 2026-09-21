@extends('layouts.guest')

@section('content')
    <h1 class="font-serif text-xl font-semibold text-primary mb-4">Lupa Password</h1>

    <p class="text-sm text-ink/70 mb-4">
        Masukkan email superadmin. Kalau email terdaftar, kami kirim link reset password ke email itu.
    </p>

    @if (session('status'))
        <div class="mb-4 rounded border border-primary/30 bg-primary/10 px-3 py-2 text-sm text-primary">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded border border-accent/40 bg-accent/10 px-3 py-2 text-sm text-ink">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.lupa-password.kirim') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        </div>

        <button type="submit"
                class="w-full rounded bg-primary text-background font-medium py-2 text-sm hover:opacity-90">
            Kirim Link Reset
        </button>
    </form>

    <p class="mt-4 text-sm text-center">
        <a href="{{ route('admin.login') }}" class="text-accent hover:underline">Kembali ke login</a>
    </p>
@endsection
