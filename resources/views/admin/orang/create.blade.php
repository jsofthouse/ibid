@extends('layouts.admin')

@section('page-title', 'Tambah Orang')

@section('content')
    <div class="bg-card border border-line rounded-lg p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.orang.store') }}" class="space-y-4">
            @include('admin.orang._form', ['orang' => null])
        </form>
    </div>
@endsection
