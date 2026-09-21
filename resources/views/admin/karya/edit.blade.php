@extends('layouts.admin')

@section('page-title', 'Ubah Karya')

@section('content')
    <div class="bg-card border border-line rounded-lg p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.karya.update', $karya) }}" enctype="multipart/form-data" class="space-y-4">
            @method('PUT')
            @include('admin.karya._form')
        </form>
    </div>
@endsection
