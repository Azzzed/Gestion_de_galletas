@extends('layouts.superadmin')
@section('title', 'Panel del Sistema')

@section('content')
<div class="mb-6">
    <h1 class="font-display font-bold text-2xl text-white flex items-center gap-3">
        <span class="icon text-purple-400">dashboard</span>
        Panel del Sistema
    </h1>
    <p class="text-white/50 text-sm mt-1">Vista global de todas las sucursales</p>
</div>

{{-- KPIs globales --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="rounded-2xl p-5" style="background:rgba(124,58,237,0.15);border:1px solid rgba(124,58,237,0.3)">
        <span class="icon text-purple-400 block mb-2">store</span>
        <p class="font-bold text-white text-2xl">{{ $kpis['sucursales'] }}</p>
        <p class="text-white/50 text-xs font-bold uppercase tracking-wide mt-1">Sucursales activas</p>
    </div>
    <div class="rounded-2xl p-5" style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25)">
        <span class="icon text-emerald-400 block mb-2">group</span>
        <p class="font-bold text-white text-2xl">{{ $kpis['usuarios'] }}</p>
        <p class="text-white/50 text-xs font-bold uppercase tracking-wide mt-1">Usuarios totales</p>
    </div>
    <div class="rounded-2xl p-5" style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.25)">
        <span class="icon text-amber-400 block mb-2">receipt_long</span>
        <p class="font-bold text-white text-2xl">{{ $kpis['ventas_hoy'] }}</p>
        <p class="text-white/50 text-xs font-bold uppercase tracking-wide mt-1">Ventas hoy</p>
    </div>
    <div class="rounded-2xl p-5" style="background:rgba(234,96,8,0.15);border:1px solid rgba(234,96,8,0.3)">
        <span class="icon text-orange-400 block mb-2">payments</span>
        <p class="font-bold text-white text-xl">${{ number_format($kpis['ingresos_hoy'], 0, ',', '.') }}</p>
        <p class="text-white/50 text-xs font-bold uppercase tracking-wide mt-1">Ingresos hoy</p>
    </div>
</div>

{{-- Sucursales --}}
<div class="mb-4 flex items-center justify-between">
    <h2 class="font-display font-bold text-white text-lg flex items-center gap-2">
        <span class="icon text-purple-400">store</span> Sucursales
    </h2>
    <a href="{{ route('superadmin.branches.create') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all"
       style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">
        <span class="icon" style="font-size:16px">add</span>
        Nueva Sucursal
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($branches as $branch)
    <div class="rounded-2xl p-5 relative overflow-hidden"
         style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1)">

        {{-- Color de la sucursal --}}
        <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl"
             style="background:{{ $branch->color }}"></div>

        <div class="flex items-start justify-between gap-3 mb-4 mt-1">
            <div>
                <h3 class="font-bold text-white">{{ $branch->nombre }}</h3>
                @if($branch->ciudad)
                <p class="text-xs text-white/40 mt-0.5 flex items-center gap-1">
                    <span class="icon" style="font-size:12px">location_on</span>
                    {{ $branch->ciudad }}
                </p>
                @endif
            </div>
            <span class="text-[10px] font-bold px-2 py-1 rounded-full {{ $branch->activo ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                {{ $branch->activo ? 'Activa' : 'Inactiva' }}
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2 mb-4">
            <div class="text-center rounded-xl py-2" style="background:rgba(255,255,255,0.05)">
                <p class="font-bold text-white text-lg">{{ $branch->users_count }}</p>
                <p class="text-[10px] text-white/40">Usuarios</p>
            </div>
            <div class="text-center rounded-xl py-2" style="background:rgba(255,255,255,0.05)">
                <p class="font-bold text-white text-lg">{{ $branch->sales_count }}</p>
                <p class="text-[10px] text-white/40">Ventas</p>
            </div>
            <div class="text-center rounded-xl py-2" style="background:rgba(255,255,255,0.05)">
                <p class="font-bold text-white text-lg">{{ $branch->delivery_orders_count }}</p>
                <p class="text-[10px] text-white/40">Domicilios</p>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('superadmin.branches.edit', $branch) }}"
               class="flex-1 text-center py-2 rounded-xl text-xs font-bold text-white/60 transition-all hover:text-white"
               style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1)">
                <span class="icon" style="font-size:14px">edit</span> Editar
            </a>
            <a href="{{ route('superadmin.ventas', ['branch_id' => $branch->id]) }}"
               class="flex-1 text-center py-2 rounded-xl text-xs font-bold text-orange-300 transition-all hover:text-orange-200"
               style="background:rgba(234,96,8,0.1);border:1px solid rgba(234,96,8,0.2)">
                <span class="icon" style="font-size:14px">receipt_long</span> Ventas
            </a>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-white/30">
        <span class="icon block text-4xl mb-3">store_mall_directory</span>
        <p>No hay sucursales creadas</p>
    </div>
    @endforelse
</div>
@endsection
