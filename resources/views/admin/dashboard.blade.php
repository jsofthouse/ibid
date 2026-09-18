@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
    <div class="bg-card border border-line rounded-lg p-6">
        <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>.</p>
        <p class="text-sm text-ink/70 mt-2">Statistik ringkas menyusul di Fase 2.</p>
    </div>
@endsection
