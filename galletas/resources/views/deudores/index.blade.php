@extends('layouts.app')
@section('title', 'Gestión de Deudores')

@section('content')
<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-cookie-900">📋 Gestión de Deudores</h2>
            <p class="text-cookie-500 text-sm">
                Total pendiente: 
                <span class="font-bold {{ $totalPending > 0 ? 'text-red-600' : 'text-green-600' }}">
                    ${{ number_format($totalPending, 0, ',', '.') }}
                </span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Buscador --}}
            <form method="GET" action="{{ route('deudores.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar deudor..."
                       class="px-4 py-2 rounded-xl border border-cookie-200 text-sm focus:ring-cookie-500 focus:border-cookie-500 w-48">
                @if($search)
                    <a href="{{ route('deudores.index') }}" class="text-cookie-400 hover:text-cookie-600">✕</a>
                @endif
            </form>

            <a href="{{ route('deudores.create') }}"
               class="px-4 py-2 bg-cookie-500 text-white rounded-xl text-sm font-bold hover:bg-cookie-600 transition shadow-md">
                ➕ Nuevo Deudor
            </a>
        </div>
    </div>

    {{-- Tarjeta resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 border border-cookie-200 shadow-sm">
            <p class="text-cookie-500 text-sm">👥 Total Deudores</p>
            <p class="text-3xl font-extrabold text-cookie-800 mt-1">{{ $debtors->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-red-200 shadow-sm">
            <p class="text-red-500 text-sm">💰 Deuda Total Pendiente</p>
            <p class="text-3xl font-extrabold text-red-600 mt-1">${{ number_format($totalPending, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-amber-200 shadow-sm">
            <p class="text-amber-600 text-sm">⚠️ Con Alertas</p>
            <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $debtors->where('has_alert', true)->count() }}</p>
        </div>
    </div>

    {{-- Lista de deudores --}}
    @if($debtors->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center border border-cookie-200">
            <span class="text-5xl block mb-4">📋</span>
            <p class="text-cookie-500 text-lg">No hay deudores registrados</p>
            <a href="{{ route('deudores.create') }}"
               class="inline-block mt-4 px-6 py-3 bg-cookie-500 text-white rounded-xl font-bold hover:bg-cookie-600 transition">
                Registrar primer deudor
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($debtors as $debtor)
                <a href="{{ route('deudores.show', $debtor->id) }}"
                   class="block bg-white rounded-2xl border-2 p-5 transition-all hover:shadow-lg hover:-translate-y-1
                          {{ $debtor->has_alert ? 'border-red-300 bg-red-50/50' : 'border-cookie-200' }}">

                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            {{-- Avatar --}}
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold
                                        {{ $debtor->has_alert ? 'bg-red-100 text-red-600' : 'bg-cookie-100 text-cookie-600' }}">
                                {{ strtoupper(substr($debtor->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-cookie-900">{{ $debtor->name }}</h3>
                                @if($debtor->phone)
                                    <p class="text-xs text-cookie-400">📞 {{ $debtor->phone }}</p>
                                @endif
                            </div>
                        </div>

                        @if($debtor->has_alert)
                            <span class="px-2 py-1 bg-red-100 text-red-600 rounded-full text-xs font-bold animate-pulse-alert">
                                ⚠️
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="bg-cookie-50 rounded-xl p-3">
                            <p class="text-xs text-cookie-500">Deuda Pendiente</p>
                            <p class="text-lg font-extrabold {{ $debtor->total_pending > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $debtor->formatted_pending }}
                            </p>
                        </div>
                        <div class="bg-cookie-50 rounded-xl p-3">
                            <p class="text-xs text-cookie-500">Compras Fiadas</p>
                            <p class="text-lg font-extrabold text-cookie-700">
                                {{ $debtor->pending_purchases }}
                            </p>
                        </div>
                    </div>

                    @if($debtor->last_purchase_date)
                        <p class="text-xs text-cookie-400 mt-3 text-center">
                            Última compra: {{ $debtor->last_purchase_date->diffForHumans() }}
                            @if($debtor->days_since_purchase > 7 && $debtor->total_pending > 0)
                                <span class="text-red-500">(hace {{ $debtor->days_since_purchase }} días)</span>
                            @endif
                        </p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
