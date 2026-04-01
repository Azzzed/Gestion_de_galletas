@extends('layouts.app')
@section('title','Editar Galleta')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.cookies.index') }}" class="btn-ghost py-2 px-3">
            <span class="icon icon-sm">arrow_back</span>
        </a>
        <div class="flex-1">
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">edit</span>
                Editar: {{ $cookie->nombre }}
            </h1>
            <p class="page-subtitle">{{ $cookie->tamano_label }}</p>
        </div>

        {{-- Preview imagen actual si existe --}}
        @if($cookie->imagen_path)
        <div class="w-12 h-12 rounded-xl overflow-hidden border border-cream-200 flex-shrink-0">
            <img src="{{ Storage::url($cookie->imagen_path) }}" alt="{{ $cookie->nombre }}"
                 class="w-full h-full object-cover">
        </div>
        @endif
    </div>

    <div class="card p-6">
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