@extends('layouts.app')
@section('title','Nuevo Cliente')
@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.customers.index') }}"
           class="w-10 h-10 rounded-xl border border-cream-300 flex items-center justify-center text-espresso-700/50 hover:text-espresso-900 hover:bg-cream-100 hover:border-brand-300 transition-all">
            <span class="icon icon-sm">arrow_back</span>
        </a>
        <div>
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">person_add</span>
                Nuevo Cliente
            </h1>
            <p class="page-subtitle">Registra los datos de contacto</p>
        </div>
    </div>

    <div class="card p-8">
        <form method="POST" action="{{ route('admin.customers.store') }}">
            @csrf
            @include('admin.customers._form')
        </form>
    </div>

</div>
@endsection