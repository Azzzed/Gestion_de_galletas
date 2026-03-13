@extends('layouts.app')
@section('title', 'Dashboard de Cierre')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-cookie-900">📊 Dashboard de Cierre</h2>
            <p class="text-cookie-500 text-sm">Resumen del día: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
        </div>
        <form method="GET" action="{{ route('dashboard.index') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date }}"
                   class="px-4 py-2 rounded-xl border border-cookie-200 text-sm focus:ring-cookie-500 focus:border-cookie-500"
                   onchange="this.form.submit()">
        </form>
    </div>

    {{-- TARJETAS DE TOTALES --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-gradient-to-br from-cookie-600 to-cookie-700 rounded-2xl p-6 text-white shadow-lg">
            <p class="text-cookie-200 text-sm font-medium">💰 Total Recaudado</p>
            <p class="text-3xl font-extrabold mt-2">${{ number_format($totalGeneral, 0, ',', '.') }}</p>
            <p class="text-cookie-200 text-xs mt-1">{{ $totalVentas }} ventas hoy</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-green-200 shadow-md">
            <p class="text-green-600 text-sm font-medium">💵 Efectivo</p>
            <p class="text-2xl font-extrabold text-green-800 mt-2">${{ number_format($totalEfectivo, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-purple-200 shadow-md">
            <p class="text-purple-600 text-sm font-medium">💜 Nequi</p>
            <p class="text-2xl font-extrabold text-purple-800 mt-2">${{ number_format($totalNequi, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-orange-200 shadow-md">
            <p class="text-orange-600 text-sm font-medium">🧡 Daviplata</p>
            <p class="text-2xl font-extrabold text-orange-800 mt-2">${{ number_format($totalDaviplata, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-red-200 shadow-md">
            <p class="text-red-600 text-sm font-medium">📋 Deudas Pendientes</p>
            <p class="text-2xl font-extrabold text-red-700 mt-2">${{ number_format($totalPendingDebts, 0, ',', '.') }}</p>
            @if($totalDebtPayments > 0)
                <p class="text-green-600 text-xs mt-1">+${{ number_format($totalDebtPayments, 0, ',', '.') }} cobrado hoy</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- RANKING --}}
        <div class="bg-white rounded-2xl shadow-md border border-cookie-200 p-6">
            <h3 class="text-lg font-extrabold text-cookie-900 mb-4">🏆 Ranking de Galletas</h3>
            @if($ranking->isEmpty())
                <div class="text-center py-8">
                    <span class="text-5xl block mb-3">📊</span>
                    <p class="text-cookie-400">No hay ventas este día</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($ranking as $index => $item)
                        @php
                            $maxSold = $ranking->first()->total_vendidas;
                            $percentage = $maxSold > 0 ? ($item->total_vendidas / $maxSold) * 100 : 0;
                            $medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                        @endphp
                        <div class="flex items-center gap-3 p-2 rounded-xl {{ $index === 0 ? 'bg-amber-50 border border-amber-200' : '' }}">
                            <span class="text-2xl w-8 text-center">{{ $medals[$index] ?? '🍪' }}</span>
                            <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 shadow-sm border border-cookie-100">
                                @if($item->product->image_url ?? null)
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-lg" style="background: {{ $item->product->color_hex }}22">🍪</div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-bold text-cookie-800">{{ $item->product->name }}</span>
                                    <span class="text-sm font-extrabold {{ $index === 0 ? 'text-amber-600' : 'text-cookie-600' }}">{{ $item->total_vendidas }} uds</span>
                                </div>
                                <div class="w-full bg-cookie-100 rounded-full h-3 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700" style="width: {{ $percentage }}%; background: {{ $item->product->color_hex ?? '#F97316' }}"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- RESUMEN --}}
        <div class="bg-white rounded-2xl shadow-md border border-cookie-200 p-6">
            <h3 class="text-lg font-extrabold text-cookie-900 mb-4">📋 Resumen de Ventas</h3>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-cookie-50 rounded-xl p-4 text-center">
                    <span class="text-3xl">🍪</span>
                    <p class="text-2xl font-extrabold text-cookie-800 mt-1">{{ $ventasIndividual }}</p>
                    <p class="text-xs text-cookie-500">Individuales</p>
                </div>
                <div class="bg-cookie-50 rounded-xl p-4 text-center">
                    <span class="text-3xl">🥣</span>
                    <p class="text-2xl font-extrabold text-cookie-800 mt-1">{{ $ventasBowl }}</p>
                    <p class="text-xs text-cookie-500">Bowls de 6</p>
                </div>
            </div>

            <h4 class="text-sm font-bold text-cookie-600 mb-2 uppercase tracking-wide">Últimas ventas</h4>
            <div class="space-y-2 max-h-64 overflow-y-auto cart-scroll">
                @forelse($latestSales as $sale)
                    <div class="flex items-center justify-between p-3 bg-cookie-50 rounded-xl text-sm">
                        <div class="flex items-center gap-2">
                            <div class="flex -space-x-2">
                                @foreach($sale->items->take(3) as $saleItem)
                                    <div class="w-7 h-7 rounded-full overflow-hidden border-2 border-white shadow-sm">
                                        @if($saleItem->product->image_url ?? null)
                                            <img src="{{ $saleItem->product->image_url }}" class="w-full h-full object-cover" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-xs" style="background: {{ $saleItem->product->color_hex }}33">🍪</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                <span class="font-semibold text-cookie-800">{{ $sale->sale_type_label }}</span>
                                <span class="text-cookie-400 text-xs ml-1">{{ $sale->created_at->format('h:i A') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs">{!! $sale->payment_label !!}</span>
                            <span class="font-bold text-cookie-700">{{ $sale->formatted_total }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <span class="text-3xl block mb-2">🕐</span>
                        <p class="text-cookie-400 text-sm">Sin ventas este día</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
