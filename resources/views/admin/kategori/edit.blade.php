@extends('layouts.admin')

@section('page-title', 'Ubah Kategori')

@section('content')
    <div class="bg-card border border-line rounded-lg p-6 max-w-md">
        <form method="POST" action="{{ route('admin.kategori.update', $kategori) }}" class="space-y-4">
            @method('PUT')
            @include('admin.kategori._form')
        </form>
    </div>
@endsection
