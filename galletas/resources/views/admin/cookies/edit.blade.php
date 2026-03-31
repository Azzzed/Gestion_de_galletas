@extends('layouts.app')
@section('title','Editar Galleta')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.cookies.index') }}"
           class="p-2 rounded-xl hover:bg-warm-100 text-gray-500">←</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Editar: {{ $cookie->nombre }}</h1>
            <p class="text-sm text-gray-400">{{ $cookie->tamano_label }}</p>
        </div>
    </div>
    <div class="bg-white rounded-3xl shadow-sm border border-warm-200 p-6">
        <form method="POST" action="{{ route('admin.cookies.update', $cookie) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.cookies._form')
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush
