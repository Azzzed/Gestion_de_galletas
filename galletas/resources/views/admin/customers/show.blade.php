@extends('layouts.app')
@section('title', $customer->nombre)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.customers.index') }}" class="btn-ghost py-2 px-3">
            <span class="icon icon-sm">arrow_back</span>
        </a>
        <div class="flex-1">
            <h1 class="font-display font-bold text-espresso-900 text-2xl">Perfil del Cliente</h1>
            <p class="page-subtitle">Historial y gestión de cuenta</p>
        </div>
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn-ghost">
            <span class="icon icon-sm">edit</span>Editar
        </a>
    </div>

    {{-- Card principal --}}
    <div class="card overflow-hidden">
        {{-- Banner superior --}}
        <div class="h-3 w-full" style="background:linear-gradient(90deg,#1a0a00,#ea6008,#f97316)"></div>

        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-start gap-5">

                {{-- Avatar grande --}}
                <div class="w-20 h-20 rounded-2xl flex-shrink-0 flex items-center justify-center
                            font-display font-bold text-3xl text-white shadow-lg"
                     style="background:linear-gradient(135deg,#c24808,#ea6008)">
                    {{ strtoupper(substr($customer->nombre, 0, 1)) }}
                </div>

                {{-- Datos --}}
                <div class="flex-1">
                    <h2 class="font-display font-bold text-espresso-900 text-xl">{{ $customer->nombre }}</h2>
                    @if($customer->email)
                    <p class="text-sm text-espresso-700/60 flex items-center gap-1.5 mt-1">
                        <span class="icon icon-sm text-brand-400">mail</span>{{ $customer->email }}
                    </p>
                    @endif
                    @if($customer->telefono)
                    <p class="text-sm text-espresso-700/60 flex items-center gap-1.5 mt-1">
                        <span class="icon icon-sm text-brand-400">phone</span>{{ $customer->telefono }}
                    </p>
                    @endif
                    @if($customer->direccion)
                    <p class="text-sm text-espresso-700/60 flex items-center gap-1.5 mt-1">
                        <span class="icon icon-sm text-brand-400">location_on</span>{{ $customer->direccion }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- KPIs --}}
            <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-cream-200">
                <div class="text-center rounded-2xl py-4 px-3" style="background:#fdfaf4;border:1px solid #f5e5c0">
                    <p class="font-display font-bold text-2xl text-brand-600">{{ $ventas->total() }}</p>
                    <p class="text-xs text-espresso-700/50 font-semibold mt-1 flex items-center justify-center gap-1">
                        <span class="icon icon-sm">receipt_long</span>Compras
                    </p>
                </div>
                <div class="text-center rounded-2xl py-4 px-3" style="background:#fdfaf4;border:1px solid #f5e5c0">
                    <p class="font-display font-bold text-2xl text-brand-600">${{ number_format($totalGastado, 0, ',', '.') }}</p>
                    <p class="text-xs text-espresso-700/50 font-semibold mt-1 flex items-center justify-center gap-1">
                        <span class="icon icon-sm">payments</span>Total gastado
                    </p>
                </div>
                <div class="text-center rounded-2xl py-4 px-3 {{ $saldoTotal > 0 ? 'border-red-200' : 'border-green-200' }}"
                     style="border-width:1px;border-style:solid;background:{{ $saldoTotal > 0 ? '#fef2f2' : '#f0fdf4' }}">
                    <p class="font-display font-bold text-2xl {{ $saldoTotal > 0 ? 'text-red-600' : 'text-green-600' }}">
                        @if($saldoTotal > 0)
                            ${{ number_format($saldoTotal, 0, ',', '.') }}
                        @else
                            <span class="icon icon-xl icon-fill">check_circle</span>
                        @endif
                    </p>
                    <p class="text-xs font-semibold mt-1 flex items-center justify-center gap-1 {{ $saldoTotal > 0 ? 'text-red-500' : 'text-green-600' }}">
                        <span class="icon icon-sm">{{ $saldoTotal > 0 ? 'credit_card_off' : 'verified' }}</span>
                        {{ $saldoTotal > 0 ? 'Deuda pendiente' : 'Sin deudas' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Galletas favoritas --}}
    @if($frecuentes->isNotEmpty())
    <div class="card p-6">
        <h2 class="font-display font-bold text-espresso-900 text-lg flex items-center gap-2 mb-4">
            <span class="icon text-brand-500">favorite</span>
            Galletas favoritas
        </h2>
        <div class="flex flex-wrap gap-3">
            @foreach($frecuentes as $item)
            <div class="flex items-center gap-3 rounded-2xl px-4 py-3 border border-cream-200 transition-all hover:border-brand-300 hover:shadow-sm" style="background:#fdfaf4">
                @if($item->imagen_path)
                <img src="{{ Storage::url($item->imagen_path) }}" alt="{{ $item->nombre }}"
                     class="w-10 h-10 rounded-xl object-cover border border-cream-200">
                @else
                <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                    <span class="icon text-brand-400">bakery_dining</span>
                </div>
                @endif
                <div>
                    <p class="text-sm font-bold text-espresso-900">{{ $item->nombre }}</p>
                    <p class="text-xs text-brand-600 font-semibold flex items-center gap-1">
                        <span class="icon icon-sm">shopping_bag</span>
                        {{ $item->total_comprado }} unidades
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Deudas pendientes --}}
    @if($deudas->isNotEmpty())
    <div class="card p-6 border-red-200 overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-espresso-900 text-lg flex items-center gap-2">
                <span class="icon text-red-500">credit_card_off</span>
                Deudas Pendientes
            </h2>
            <span class="badge badge-red text-sm px-4 py-1.5">
                ${{ number_format($saldoTotal, 0, ',', '.') }} total
            </span>
        </div>
        <div class="space-y-3">
            @foreach($deudas as $deuda)
            <div class="rounded-2xl p-4 border border-red-100 overflow-hidden"
                 style="background:#fef2f2"
                 x-data="{ abonoAbierto: false }">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        @if($deuda->sale)
                        <p class="text-sm font-bold text-espresso-900 flex items-center gap-1.5">
                            <span class="icon icon-sm text-red-400">receipt</span>
                            Venta {{ $deuda->sale->numero_factura }}
                        </p>
                        <p class="text-xs text-espresso-700/50 mt-0.5">{{ $deuda->sale->created_at->format('d/m/Y') }}</p>
                        @endif
                        <p class="text-xs text-espresso-700/50 mt-1.5">
                            Original: <strong>${{ number_format($deuda->monto_original, 0, ',', '.') }}</strong> ·
                            Pagado: <strong class="text-green-600">${{ number_format($deuda->monto_pagado, 0, ',', '.') }}</strong>
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-display font-bold text-xl text-red-600">
                            ${{ number_format($deuda->monto_pendiente, 0, ',', '.') }}
                        </p>
                        @php $deudaMap = ['pendiente'=>'badge-red','pagada_parcial'=>'badge-amber','pagada'=>'badge-green']; @endphp
                        <span class="badge {{ $deudaMap[$deuda->estado] ?? 'badge-gray' }} text-[10px] mt-1">
                            {{ ucfirst(str_replace('_', ' ', $deuda->estado)) }}
                        </span>
                    </div>

                    {{-- Botón abonar --}}
                    <button @click="abonoAbierto = !abonoAbierto"
                            :class="abonoAbierto ? 'bg-green-600 text-white' : 'btn-ghost'"
                            class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold transition-all border border-green-300">
                        <span class="icon icon-sm">payments</span>
                        Abonar
                    </button>
                </div>

                {{-- Form abono --}}
                <div x-show="abonoAbierto" x-cloak x-transition class="mt-3 pt-3 border-t border-red-200">
                    <form method="POST" action="{{ route('admin.customers.abono', $deuda) }}"
                          class="flex gap-2 items-center">
                        @csrf
                        <div class="relative flex-1">
                            <span class="icon icon-sm absolute left-3 top-1/2 -translate-y-1/2 text-green-500">attach_money</span>
                            <input type="number" name="monto" min="1"
                                   max="{{ $deuda->monto_pendiente }}"
                                   placeholder="Monto a abonar"
                                   class="field pl-9 text-sm py-2">
                        </div>
                        <button type="submit" class="btn-primary py-2 flex-shrink-0">
                            <span class="icon icon-sm">check</span>
                            Registrar
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Historial de ventas --}}
    <div>
        <h2 class="font-display font-bold text-espresso-900 text-lg flex items-center gap-2 mb-4">
            <span class="icon text-brand-500">history</span>
            Historial de Compras
        </h2>
        <div class="space-y-3">
            @forelse($ventas as $venta)
            <div class="card p-4 hover:border-brand-200 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm font-bold text-brand-700">{{ $venta->numero_factura }}</span>
                        @php $stMap = ['completada'=>'badge-green','anulada'=>'badge-red','pendiente'=>'badge-amber']; @endphp
                        <span class="badge {{ $stMap[$venta->estado] ?? 'badge-gray' }}">{{ ucfirst($venta->estado) }}</span>
                        @if($venta->tiene_deuda)
                        <span class="badge badge-red">
                            <span class="icon icon-sm">credit_card_off</span>Deuda
                        </span>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="font-display font-bold text-espresso-900">{{ $venta->total_formateado }}</p>
                        <p class="text-xs text-espresso-700/40 flex items-center gap-1 justify-end mt-0.5">
                            <span class="icon icon-sm">schedule</span>
                            {{ $venta->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($venta->items as $item)
                    <span class="inline-flex items-center gap-1.5 bg-cream-100 text-espresso-700 text-xs px-2.5 py-1 rounded-full border border-cream-200 font-medium">
                        <span class="icon icon-sm text-brand-400" style="font-size:12px">bakery_dining</span>
                        {{ $item->cantidad }}× {{ $item->cookie->nombre }}
                    </span>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="card p-14 text-center">
                <span class="icon icon-2xl text-espresso-700/20 block mb-3">receipt_long</span>
                <p class="text-sm font-medium text-espresso-700/40">Sin compras registradas</p>
            </div>
            @endforelse
        </div>
        <div class="mt-4">{{ $ventas->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
@endpush