@extends('layouts.superadmin')
@section('title','Inventario Global')
@section('content')

<div style="margin-bottom:24px">
    <h1 style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#f1eeff;display:flex;align-items:center;gap:10px">
        <span class="icon" style="color:#a78bfa">inventory_2</span> Inventario global
    </h1>
    <p style="color:rgba(196,181,253,0.45);font-size:13px;margin-top:4px">Stock de galletas por sucursal</p>
</div>

@include('superadmin.reportes._filtros', ['branches' => $branches, 'route' => 'superadmin.inventario'])

<div style="background:#13111f;border:1px solid rgba(139,92,246,0.15);border-radius:14px;overflow:hidden">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:rgba(124,58,237,0.08);border-bottom:1px solid rgba(255,255,255,0.07)">
                    {{-- ✅ FIX: paréntesis mal colocado — era ]) as $h) --}}
                    @foreach(['Sucursal','Galleta','Tamaño','Precio','Stock','Estado'] as $h)
                    <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.5)">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($cookies as $c)
                @php
                    $stockOk  = $c->stock > 5;
                    $stockLow = $c->stock > 0 && $c->stock <= 5;
                @endphp
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05)"
                    onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                    onmouseout="this.style.background='transparent'">

                    {{-- Sucursal --}}
                    <td style="padding:13px 16px">
                        @if($c->branch)
                        <span style="font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;background:rgba(124,58,237,0.12);color:#c4b5fd;border:1px solid rgba(124,58,237,0.2)">
                            {{ $c->branch->nombre }}
                        </span>
                        @else
                        <span style="font-size:11px;color:rgba(255,255,255,0.2)">—</span>
                        @endif
                    </td>

                    {{-- Galleta --}}
                    <td style="padding:13px 16px">
                        <p style="font-size:13px;font-weight:600;color:#f1eeff">{{ $c->nombre }}</p>
                        @if($c->descripcion)
                        <p style="font-size:11px;color:rgba(255,255,255,0.3);margin-top:2px">{{ Str::limit($c->descripcion, 40) }}</p>
                        @endif
                    </td>

                    {{-- Tamaño --}}
                    <td style="padding:13px 16px">
                        <span style="font-size:12px;color:rgba(196,181,253,0.7);text-transform:capitalize">{{ $c->tamano ?? '—' }}</span>
                    </td>

                    {{-- Precio --}}
                    <td style="padding:13px 16px;font-weight:700;color:#f1eeff">
                        ${{ number_format($c->precio, 0, ',', '.') }}
                    </td>

                    {{-- Stock --}}
                    <td style="padding:13px 16px">
                        <span style="font-size:15px;font-weight:800;
                            {{ $stockOk  ? 'color:#34d399' : ($stockLow ? 'color:#fbbf24' : 'color:#f87171') }}">
                            {{ $c->stock }}
                        </span>
                        <span style="font-size:10px;color:rgba(255,255,255,0.3);margin-left:4px">uds</span>
                    </td>

                    {{-- Estado --}}
                    <td style="padding:13px 16px">
                        @if($c->pausado)
                            <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;color:#fbbf24;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.25)">
                                ⏸ Pausada
                            </span>
                        @elseif(!$c->activo)
                            <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;color:#f87171;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2)">
                                Inactiva
                            </span>
                        @elseif($c->stock === 0)
                            <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;color:#f87171;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2)">
                                Sin stock
                            </span>
                        @elseif($stockLow)
                            <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;color:#fbbf24;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.2)">
                                ⚠ Stock bajo
                            </span>
                        @else
                            <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;color:#34d399;background:rgba(52,211,153,0.1);border:1px solid rgba(52,211,153,0.2)">
                                Disponible
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:60px;text-align:center;color:rgba(255,255,255,0.2)">
                        <span class="icon" style="font-size:40px;display:block;margin-bottom:10px">inventory_2</span>
                        Sin galletas para mostrar
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cookies->hasPages())
    <div style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.06)">
        {{ $cookies->links() }}
    </div>
    @endif
</div>

@endsection