@extends('layouts.app')
@section('title','Nuevo Cliente')
@section('content')
<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.customers.index') }}"
           class="p-2 rounded-xl hover:bg-warm-100 text-gray-500">←</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Nuevo Cliente</h1>
            <p class="text-sm text-gray-400">Registra los datos de contacto</p>
        </div>
    </div>
    <div class="bg-white rounded-3xl shadow-sm border border-warm-200 p-6">
        <form method="POST" action="{{ route('admin.customers.store') }}">
            @csrf
            @include('admin.customers._form')
        </form>
    </div>
</div>
@endsection
