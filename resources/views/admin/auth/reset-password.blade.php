@extends('layouts.guest')

@section('content')
    <h1 class="font-serif text-xl font-semibold text-primary mb-4">Reset Password</h1>

    @if ($errors->any())
        <div class="mb-4 rounded border border-accent/40 bg-accent/10 px-3 py-2 text-sm text-ink">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.reset-password.simpan') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus
                   class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password Baru</label>
            <input id="password" type="password" name="password" required
                   class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium mb-1">Ulangi Password Baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="w-full rounded border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        </div>

        <button type="submit"
                class="w-full rounded bg-primary text-background font-medium py-2 text-sm hover:opacity-90">
            Simpan Password Baru
        </button>
    </form>
@endsection
