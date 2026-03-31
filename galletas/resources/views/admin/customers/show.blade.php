@extends('layouts.app')
@section('title', $customer->nombre)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.customers.index') }}"
           class="p-2 rounded-xl hover:bg-warm-100 text-gray-500">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Perfil del cliente</h1>
        <a href="{{ route('admin.customers.edit', $customer) }}"
           class="ml-auto px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-600
                  hover:bg-warm-50 font-medium transition-colors">✏️ Editar</a>
    </div>

    {{-- Card principal --}}
    <div class="bg-white rounded-3xl border border-warm-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-start gap-5">

            {{-- Avatar --}}
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-capy-400 to-capy-700
                        flex items-center justify-center text-white font-bold text-3xl
                        shrink-0 shadow-md">
                {{ strtoupper(substr($customer->nombre, 0, 1)) }}
            </div>

            {{-- Datos --}}
            <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-xl font-bold text-gray-800">{{ $customer->nombre }}</p>
                    @if($customer->email)
                    <p class="text-sm text-gray-400">{{ $customer->email }}</p>
                    @endif
                </div>
                @if($customer->telefono)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Teléfono</p>
                    <p class="text-sm font-medium">{{ $customer->telefono }}</p>
                </div>
                @endif
                @if($customer->direccion)
                <div class="col-span-2">
                    <p class="text-xs text-gray-400 mb-0.5">Dirección</p>
                    <p class="text-sm font-medium">{{ $customer->direccion }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-warm-100">
            <div class="bg-warm-50 rounded-2xl p-4 text-center">
                <p class="text-2xl font-bold text-capy-600">{{ $ventas->total() }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Compras totales</p>
            </div>
            <div class="bg-warm-50 rounded-2xl p-4 text-center">
                <p class="text-2xl font-bold text-capy-600">
                    ${{ number_format($totalGastado, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">Total gastado</p>
            </div>
            <div class="rounded-2xl p-4 text-center {{ $saldoTotal > 0 ? 'bg-red-50' : 'bg-green-50' }}">
                <p class="text-2xl font-bold {{ $saldoTotal > 0 ? 'text-red-600' : 'text-green-600' }}">
                    @if($saldoTotal > 0)
                        ${{ number_format($saldoTotal, 0, ',', '.') }}
                    @else
                        ✅
                    @endif
                </p>
                <p class="text-xs mt-0.5 {{ $saldoTotal > 0 ? 'text-red-400' : 'text-green-400' }}">
                    {{ $saldoTotal > 0 ? 'Deuda pendiente' : 'Sin deudas' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Galletas más compradas --}}
    @if($frecuentes->isNotEmpty())
    <div class="bg-white rounded-3xl border border-warm-200 shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-4">🍪 Galletas favoritas</h2>
        <div class="flex flex-wrap gap-3">
            @foreach($frecuentes as $item)
            <div class="flex items-center gap-3 bg-warm-50 rounded-2xl px-4 py-3 border border-warm-200">
                @if($item->imagen_path)
                <img src="{{ Storage::url($item->imagen_path) }}" alt="{{ $item->nombre }}"
                     class="w-10 h-10 rounded-xl object-cover">
                @else
                <span class="text-2xl">🍪</span>
                @endif
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $item->nombre }}</p>
                    <p class="text-xs text-capy-600 font-medium">
                        {{ $item->total_comprado }} unidades compradas
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Deudas pendientes --}}
    @if($deudas->isNotEmpty())
    <div class="bg-white rounded-3xl border border-red-200 shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            ⚠️ Deudas pendientes
            <span class="text-sm font-normal text-red-500">
                Total: ${{ number_format($saldoTotal, 0, ',', '.') }}
            </span>
        </h2>
        <div class="space-y-3">
            @foreach($deudas as $deuda)
            <div class="flex items-center gap-4 bg-red-50 rounded-2xl p-4 border border-red-100"
                 x-data="{ abonoAbierto: false }">
                <div class="flex-1">
                    @if($deuda->sale)
                    <p class="text-sm font-semibold text-gray-800">
                        Venta {{ $deuda->sale->numero_factura }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ $deuda->sale->created_at->format('d/m/Y') }}
                    </p>
                    @endif
                    <p class="text-xs text-gray-500 mt-1">
                        Original: ${{ number_format($deuda->monto_original, 0, ',', '.') }} ·
                        Pagado: ${{ number_format($deuda->monto_pagado, 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-lg font-bold text-red-600">
                        ${{ number_format($deuda->monto_pendiente, 0, ',', '.') }}
                    </p>
                    <span class="text-xs px-2 py-0.5 rounded-full
                                 {{ $deuda->estado === 'pendiente' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600' }}">
                        {{ ucfirst(str_replace('_', ' ', $deuda->estado)) }}
                    </span>
                </div>

                {{-- Registrar abono --}}
                <div>
                    <button @click="abonoAbierto = !abonoAbierto"
                            class="px-3 py-2 bg-white border border-red-200 text-red-600
                                   rounded-xl text-xs font-medium hover:bg-red-50 transition-colors">
                        💵 Abonar
                    </button>
                    <div x-show="abonoAbierto" x-cloak class="mt-2 fade-in">
                        <form method="POST"
                              action="{{ route('admin.customers.abono', $deuda) }}"
                              class="flex gap-2">
                            @csrf
                            <input type="number" name="monto" min="1"
                                   max="{{ $deuda->monto_pendiente }}"
                                   placeholder="Monto"
                                   class="w-28 px-2 py-1.5 text-sm rounded-lg border border-gray-200
                                          focus:outline-none focus:ring-2 focus:ring-capy-400">
                            <button type="submit"
                                    class="px-3 py-1.5 bg-green-500 text-white text-xs
                                           font-bold rounded-lg hover:bg-green-600">
                                ✓
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Historial de ventas --}}
    <div>
        <h2 class="font-bold text-gray-800 mb-4">📋 Historial de compras</h2>
        <div class="space-y-3">
            @forelse($ventas as $venta)
            <div class="bg-white rounded-2xl border border-warm-200 shadow-sm p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm font-bold text-capy-600">
                            {{ $venta->numero_factura }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $venta->estado === 'completada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ ucfirst($venta->estado) }}
                        </span>
                        @if($venta->tiene_deuda)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-500 font-medium">
                            ⚠️ Deuda
                        </span>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-800">{{ $venta->total_formateado }}</p>
                        <p class="text-xs text-gray-400">{{ $venta->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($venta->items as $item)
                    <span class="inline-flex items-center gap-1 bg-warm-50 text-gray-600
                                 text-xs px-2.5 py-1 rounded-full border border-warm-200">
                        🍪 {{ $item->cantidad }}× {{ $item->cookie->nombre }}
                    </span>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-warm-200 p-12 text-center text-gray-300">
                <span class="text-5xl block mb-3">🛒</span>
                <p class="text-gray-400">Sin compras registradas</p>
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
