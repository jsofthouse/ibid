@extends('layouts.admin')

@section('page-title', 'Tambah Kategori')

@section('content')
    <div class="bg-card border border-line rounded-lg p-6 max-w-md">
        <form method="POST" action="{{ route('admin.kategori.store') }}" class="space-y-4">
            @include('admin.kategori._form', ['kategori' => null])
        </form>
    </div>
@endsection
