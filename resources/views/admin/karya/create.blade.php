@extends('layouts.admin')

@section('page-title', 'Tambah Karya')

@section('content')
    <div class="bg-card border border-line rounded-lg p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.karya.store') }}" enctype="multipart/form-data" class="space-y-4">
            @include('admin.karya._form')
        </form>
    </div>
@endsection
