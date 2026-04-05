@extends('layouts.app')
@section('title','Editar Cliente')
@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.customers.index') }}"
           class="w-10 h-10 rounded-xl border border-cream-300 flex items-center justify-center text-espresso-700/50 hover:text-espresso-900 hover:bg-cream-100 hover:border-brand-300 transition-all">
            <span class="icon icon-sm">arrow_back</span>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">edit</span>
                Editar Cliente
            </h1>
            <p class="page-subtitle truncate">{{ $customer->nombre }}</p>
        </div>
        {{-- Avatar grande --}}
        <div class="w-12 h-12 rounded-2xl bg-brand-100 flex items-center justify-center text-brand-700 font-display font-bold text-xl flex-shrink-0 shadow-sm">
            {{ strtoupper(substr($customer->nombre, 0, 1)) }}
        </div>
    </div>

    <div class="card p-8">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @csrf @method('PUT')
            @include('admin.customers._form')
        </form>
    </div>

</div>
@endsection