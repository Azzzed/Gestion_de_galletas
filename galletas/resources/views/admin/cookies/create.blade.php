@extends('layouts.app')
@section('title','Nueva Galleta')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.cookies.index') }}" class="btn-ghost py-2 px-3">
            <span class="icon icon-sm">arrow_back</span>
        </a>
        <div>
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">add_circle</span>
                Nueva Galleta
            </h1>
            <p class="page-subtitle">Agrega un producto al catálogo</p>
        </div>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.cookies.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.cookies._form')
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush