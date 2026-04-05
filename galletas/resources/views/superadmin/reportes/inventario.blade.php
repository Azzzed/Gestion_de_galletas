@extends('layouts.superadmin')
@section('title','Inventario Global')
@section('content')

<div style="margin-bottom:24px">
    <h1 style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#f1eeff;display:flex;align-items:center;gap:10px">
        <span class="icon" style="color:#a78bfa">inventory_2</span> Inventario global
    </h1>
    <p style="color:rgba(196,181,253,0.45);font-size:13px;margin-top:4px">Stock de galletas por sucursal</p>
</div>

@include('superadmin.reportes._filtros', ['branches'=>$branches,'route'=>'superadmin.inventario'])

<div style="background:#13111f;border:1px solid rgba(139,92,246,0.15);border-radius:14px;overflow:hidden">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:rgba(124,58,237,0.08);border-bottom:1px solid rgba(255,255,255,0.07)">
                    @foreach(['Sucursal','Galleta','Tamaño','Precio','Stock','Estado']) as $h)
                    <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.5)">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($cookies as $c)
                @php $stockOk = $c->stock > 5; $stockLow = $c->stock > 0 && $c->stock <= 5; @endphp
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05)" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 16px">
                        @if($c->branch)
                        <span style="font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;background:rgba(124,58,237,0.12);color:#c4b5fd;border:1px solid rgba(124,58,237,0.2)">
                            {{ $c->branch->nombre }}
                        </span>
                        @endif
                    </td>
                    <td style="padding:13px 16px">
                        <div style="display:flex;align-items:center;gap:10px">
                            @if($c->imagen_path)
                            <img src="{{ Storage::url($c->imagen_path) }}" alt="" style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0">
                            @else
                            <div style="width:32px;height:32px;border-radius:8px;background:rgba(124,58,237,0.15);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">🍪</div>
                            @endif
                            <p style="font-weight:600;color:#f1eeff;font-size:14px">{{ $c->nombre }}</p>
                        </div>
                    </td>
                    <td style="padding:13px 16px;font-size:12px;color:rgba(255,255,255,0.5);text-transform:capitalize">{{ $c->tamano }}</td>
                    <td style="padding:13px 16px;font-weight:600;color:#f1eeff">${{ number_format($c->precio,0,',','.') }}</td>
                    <td style="padding:13px 16px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-family:'Space Mono',monospace;font-weight:700;font-size:16px;
                                {{ $stockOk ? 'color:#34d399' : ($stockLow ? 'color:#fbbf24' : 'color:#f87171') }}">
                                {{ $c->stock }}
                            </span>
                            @if($stockLow)
                            <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;background:rgba(251,191,36,0.1);color:#fbbf24;border:1px solid rgba(251,191,36,0.2)">bajo</span>
                            @elseif(!$stockOk)
                            <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.2)">agotado</span>
                            @endif
                        </div>
                    </td>
                    <td style="padding:13px 16px">
                        @if($c->pausado)
                        <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(251,191,36,0.08);color:#fbbf24;border:1px solid rgba(251,191,36,0.15)">⏸ Pausada</span>
                        @elseif(!$c->activo)
                        <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(239,68,68,0.08);color:#f87171;border:1px solid rgba(239,68,68,0.15)">Inactiva</span>
                        @else
                        <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(52,211,153,0.08);color:#34d399;border:1px solid rgba(52,211,153,0.15)">● Activa</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:60px;text-align:center;color:rgba(255,255,255,0.2)">Sin productos para mostrar</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cookies->hasPages())
    <div style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.06)">{{ $cookies->links() }}</div>
    @endif
</div>

@endsection
