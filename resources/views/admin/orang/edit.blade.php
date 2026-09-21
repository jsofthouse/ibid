@extends('layouts.admin')

@section('page-title', 'Ubah Orang')

@section('content')
    <div class="bg-card border border-line rounded-lg p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.orang.update', $orang) }}" class="space-y-4">
            @method('PUT')
            @include('admin.orang._form')
        </form>
    </div>
@endsection
