@extends('layouts.public')

@section('meta')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    <div class="text-center py-16">
        <span class="inline-block rounded-full bg-accent/10 text-accent text-xs font-medium px-3 py-1 mb-3">
            Terdaftar dalam Sistem IBID &mdash; Dalam Proses Penerbitan
        </span>

        <p class="mt-4 text-sm text-ink/50">Nomor IBID: {{ $ibidNumber }}</p>
    </div>
@endsection
