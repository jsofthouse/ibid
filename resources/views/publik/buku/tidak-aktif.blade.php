@extends('layouts.public')

@section('meta')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    <div class="text-center py-16">
        @if ($judul)
            <h1 class="font-serif text-2xl font-semibold text-primary">{{ $judul }}</h1>
        @endif

        <p class="mt-4 text-ink/70">IBID ini dibatalkan / tidak berlaku.</p>
        <p class="mt-1 text-sm text-ink/50">Nomor IBID: {{ $ibidNumber }}</p>
    </div>
@endsection
