@extends('layouts.app')
@section('title','Editar Código: '.$promoCode->code)
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.promo-codes.index') }}" class="btn-ghost py-2 px-3"><span class="icon icon-sm">arrow_back</span></a>
        <div>
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">edit</span>Editar: <span class="font-mono">{{ $promoCode->code }}</span>
            </h1>
            <p class="page-subtitle">{{ $promoCode->type_label }} · {{ $promoCode->discount_label }}</p>
        </div>
    </div>
    <div class="card p-6">
        <form method="POST" action="{{ route('admin.promo-codes.update', $promoCode) }}">
            @csrf @method('PUT')
            @include('admin.promo_codes._form')
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush
