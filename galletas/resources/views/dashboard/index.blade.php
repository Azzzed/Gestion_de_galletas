@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">dashboard</span>
                Dashboard de Cierre
            </h1>
            <p class="page-subtitle mt-1">Resumen del día: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
        </div>
        <form method="GET" action="{{ route('dashboard.index') }}" class="flex items-center gap-2">
            <span class="icon icon-sm text-brand-400">calendar_today</span>
            <input type="date" name="date" value="{{ $date }}"
                   class="field text-sm py-2 w-auto"
                   onchange="this.form.submit()">
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">

        {{-- Total --}}
        <div class="rounded-2xl p-6 text-white shadow-xl" style="background: linear-gradient(135deg,#1a0a00,#ea6008)">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="icon text-white">payments</span>
                </div>
                <span class="badge" style="background:rgba(255,255,255,0.15);color:#fff">{{ $totalVentas }} ventas</span>
            </div>
            <p class="text-white/60 text-xs font-semibold uppercase tracking-wider mb-1">Total Recaudado</p>
            <p class="font-display font-bold text-3xl">${{ number_format($totalGeneral, 0, ',', '.') }}</p>
        </div>

        {{-- Efectivo --}}
        <div class="card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center">
                    <span class="icon text-green-600">payments</span>
                </div>
                <p class="text-xs font-bold text-espresso-700/60 uppercase tracking-wide">Efectivo</p>
            </div>
            <p class="font-display font-bold text-2xl text-green-700">${{ number_format($totalEfectivo, 0, ',', '.') }}</p>
        </div>

        {{-- Nequi --}}
        <div class="card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center">
                    <span class="icon text-purple-600">phone_iphone</span>
                </div>
                <p class="text-xs font-bold text-espresso-700/60 uppercase tracking-wide">Nequi</p>
            </div>
            <p class="font-display font-bold text-2xl text-purple-700">${{ number_format($totalNequi, 0, ',', '.') }}</p>
        </div>

        {{-- Daviplata --}}
        <div class="card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center">
                    <span class="icon text-orange-600">account_balance_wallet</span>
                </div>
                <p class="text-xs font-bold text-espresso-700/60 uppercase tracking-wide">Daviplata</p>
            </div>
            <p class="font-display font-bold text-2xl text-orange-700">${{ number_format($totalDaviplata, 0, ',', '.') }}</p>
        </div>

        {{-- Deudas --}}
        <div class="card p-5 border-red-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                    <span class="icon text-red-500">credit_card_off</span>
                </div>
                <p class="text-xs font-bold text-espresso-700/60 uppercase tracking-wide">Deudas</p>
            </div>
            <p class="font-display font-bold text-2xl text-red-600">${{ number_format($totalPendingDebts, 0, ',', '.') }}</p>
            @if($totalDebtPayments > 0)
            <p class="text-green-600 text-xs font-semibold mt-1 flex items-center gap-1">
                <span class="icon icon-sm">trending_up</span>
                +${{ number_format($totalDebtPayments, 0, ',', '.') }} cobrado hoy
            </p>
            @endif
        </div>
    </div>

    {{-- Body Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Ranking --}}
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-5">
                <span class="icon text-brand-500">emoji_events</span>
                <h3 class="font-display font-bold text-espresso-900 text-lg">Ranking de Galletas</h3>
            </div>

            @if($ranking->isEmpty())
            <div class="text-center py-12">
                <span class="icon icon-2xl text-espresso-700/20 block mb-2">bar_chart</span>
                <p class="text-sm text-espresso-700/40 font-medium">No hay ventas este día</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($ranking as $index => $item)
                @php
                    $maxSold = $ranking->first()->total_vendidas;
                    $percentage = $maxSold > 0 ? ($item->total_vendidas / $maxSold) * 100 : 0;
                    $medals = ['gold','silver','#cd7f32','',''];
                    $medalIcons = ['workspace_premium','military_tech','military_tech','looks_4','looks_5'];
                    $medalColors = ['text-amber-500','text-slate-400','text-amber-700','text-espresso-700/30','text-espresso-700/30'];
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $index === 0 ? 'bg-amber-50 border border-amber-100' : 'bg-cream-50' }}">
                    <span class="icon {{ $medalColors[$index] ?? 'text-espresso-700/30' }} w-8 text-center">{{ $medalIcons[$index] ?? 'circle' }}</span>

                    <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0 border border-cream-200 bg-cream-100 flex items-center justify-center">
                        @if($item->product->image_url ?? null)
                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="icon text-brand-300">bakery_dining</span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-sm font-bold text-espresso-800 truncate">{{ $item->product->name }}</span>
                            <span class="text-sm font-bold {{ $index === 0 ? 'text-amber-600' : 'text-brand-600' }} flex-shrink-0 ml-2">{{ $item->total_vendidas }} uds</span>
                        </div>
                        <div class="w-full bg-cream-200 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                 style="width: {{ $percentage }}%; background: {{ $item->product->color_hex ?? '#f97316' }}"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Resumen --}}
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-5">
                <span class="icon text-brand-500">summarize</span>
                <h3 class="font-display font-bold text-espresso-900 text-lg">Resumen de Ventas</h3>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="rounded-2xl p-4 text-center" style="background:#fbf3e2">
                    <span class="icon icon-xl text-brand-500 block mb-1">bakery_dining</span>
                    <p class="font-display font-bold text-2xl text-espresso-900">{{ $ventasIndividual }}</p>
                    <p class="text-xs text-espresso-700/50 font-semibold mt-0.5">Individuales</p>
                </div>
                <div class="rounded-2xl p-4 text-center" style="background:#fbf3e2">
                    <span class="icon icon-xl text-brand-500 block mb-1">inventory_2</span>
                    <p class="font-display font-bold text-2xl text-espresso-900">{{ $ventasBowl }}</p>
                    <p class="text-xs text-espresso-700/50 font-semibold mt-0.5">Bowls de 6</p>
                </div>
            </div>

            <h4 class="text-xs font-bold text-espresso-700/50 mb-3 uppercase tracking-wider flex items-center gap-1.5">
                <span class="icon icon-sm">history</span>Últimas ventas
            </h4>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($latestSales as $sale)
                <div class="flex items-center justify-between p-3 bg-cream-50 rounded-xl border border-cream-200 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-2">
                            @foreach($sale->items->take(3) as $saleItem)
                            <div class="w-7 h-7 rounded-full overflow-hidden border-2 border-white shadow-sm bg-cream-100 flex items-center justify-center">
                                @if($saleItem->product->image_url ?? null)
                                    <img src="{{ $saleItem->product->image_url }}" class="w-full h-full object-cover">
                                @else
                                    <span class="icon icon-sm text-brand-300" style="font-size:13px">bakery_dining</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <div>
                            <span class="font-semibold text-espresso-800">{{ $sale->sale_type_label }}</span>
                            <span class="text-espresso-700/40 text-xs ml-1.5">{{ $sale->created_at->format('h:i A') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-espresso-700/60">{!! $sale->payment_label !!}</span>
                        <span class="font-bold text-brand-700">{{ $sale->formatted_total }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <span class="icon icon-xl text-espresso-700/20 block mb-2">schedule</span>
                    <p class="text-sm text-espresso-700/40 font-medium">Sin ventas este día</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection