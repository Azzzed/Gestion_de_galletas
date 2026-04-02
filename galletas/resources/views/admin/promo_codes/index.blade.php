@extends('layouts.app')
@section('title','Códigos Promocionales')

@section('content')
<div x-data="{ confirmDel: null }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">local_offer</span>
                Códigos Promocionales
            </h1>
            <p class="page-subtitle">Gestiona descuentos y promociones para el POS</p>
        </div>
        <a href="{{ route('admin.promo-codes.create') }}" class="btn-primary">
            <span class="icon icon-sm">add</span> Nuevo Código
        </a>
    </div>

    {{-- Grid de códigos --}}
    @if($codes->isEmpty())
    <div class="card p-16 text-center">
        <span class="icon icon-2xl text-espresso-700/20 block mb-3">local_offer</span>
        <p class="text-espresso-700/40 font-medium mb-4">Sin códigos creados aún</p>
        <a href="{{ route('admin.promo-codes.create') }}" class="btn-primary">
            <span class="icon icon-sm">add</span> Crear primer código
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($codes as $c)
        @php
            $typeBg = ['percentage'=>'bg-purple-100 text-purple-700','fixed_amount'=>'bg-green-100 text-green-700','free_delivery'=>'bg-blue-100 text-blue-700','cookie_discount'=>'bg-amber-100 text-amber-700'];
        @endphp
        <div class="card p-5 {{ !$c->is_active ? 'opacity-60' : '' }} hover:shadow-md transition-all"
             x-data="{ active: {{ $c->is_active ? 'true' : 'false' }} }">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-mono font-bold text-xl text-espresso-900 tracking-wider">{{ $c->code }}</span>
                        <span class="badge {{ $c->is_valid_now ? 'badge-green' : 'badge-red' }} text-[10px]">
                            {{ $c->is_valid_now ? '✓ Activo' : '✗ Inactivo' }}
                        </span>
                    </div>
                    @if($c->description)
                    <p class="text-xs text-espresso-700/50">{{ $c->description }}</p>
                    @endif
                </div>
                {{-- Toggle activo --}}
                <button @click="
                    fetch('{{ route('admin.promo-codes.toggle', $c->id) }}', {
                        method: 'PATCH',
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}
                    }).then(r=>r.json()).then(d=>{ active = d.is_active });
                "
                :class="active ? 'bg-green-500' : 'bg-gray-300'"
                class="relative w-10 h-5 rounded-full transition-colors flex-shrink-0">
                    <span :class="active ? 'translate-x-5' : 'translate-x-0.5'"
                          class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                </button>
            </div>

            {{-- Tipo + descuento --}}
            <div class="flex items-center gap-2 mb-3">
                <span class="icon icon-sm {{ ($typeBg[$c->type] ?? '') }}">{{ $c->type_icon }}</span>
                <span class="text-xs font-semibold {{ $typeBg[$c->type] ?? '' }} px-2 py-0.5 rounded-full">{{ $c->type_label }}</span>
                <span class="ml-auto font-display font-bold text-2xl text-brand-600">{{ $c->discount_label }}</span>
            </div>

            {{-- Detalles --}}
            <div class="space-y-1 text-xs text-espresso-700/60 mb-4">
                @if($c->min_order_amount > 0)
                <p class="flex items-center gap-1">
                    <span class="icon icon-sm">shopping_cart</span>
                    Mínimo: <strong>${{ number_format($c->min_order_amount, 0, ',', '.') }}</strong>
                </p>
                @endif
                @if($c->max_uses)
                <p class="flex items-center gap-1">
                    <span class="icon icon-sm">confirmation_number</span>
                    Usos: <strong>{{ $c->used_count }} / {{ $c->max_uses }}</strong>
                </p>
                @else
                <p class="flex items-center gap-1">
                    <span class="icon icon-sm">all_inclusive</span>
                    Usos ilimitados · <strong>{{ $c->used_count }}</strong> usados
                </p>
                @endif
                @if($c->valid_from || $c->valid_until)
                <p class="flex items-center gap-1">
                    <span class="icon icon-sm">date_range</span>
                    {{ $c->valid_from ?? '—' }} → {{ $c->valid_until ?? '—' }}
                </p>
                @endif
            </div>

            {{-- Acciones --}}
            <div class="flex gap-2 pt-3 border-t border-cream-200">
                <a href="{{ route('admin.promo-codes.edit', $c->id) }}" class="flex-1 btn-ghost text-center justify-center py-1.5 text-xs rounded-lg">
                    <span class="icon icon-sm">edit</span> Editar
                </a>
                <button @click="confirmDel = {{ $c->id }}"
                        class="btn-danger py-1.5 px-3 text-xs rounded-lg">
                    <span class="icon icon-sm">delete</span>
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Modal eliminar --}}
    <div x-show="confirmDel !== null" x-cloak x-transition
         @click.self="confirmDel=null"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-espresso-900/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-6 text-center">
            <span class="icon icon-2xl text-red-500 block mb-3">delete</span>
            <h3 class="font-display font-bold text-xl mb-2">¿Eliminar código?</h3>
            <p class="text-sm text-espresso-700/60 mb-6">Esta acción no se puede deshacer.</p>
            <div class="flex gap-3">
                <button @click="confirmDel=null" class="flex-1 btn-ghost justify-center py-3">Cancelar</button>
                <form :action="`/admin/promo-codes/${confirmDel}`" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full btn-danger justify-center py-3 rounded-xl font-bold">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush
