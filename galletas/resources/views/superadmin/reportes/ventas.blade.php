{{-- ═══════════════════════════════════════════════
     resources/views/superadmin/reportes/ventas.blade.php
════════════════════════════════════════════════ --}}
@extends('layouts.superadmin')
@section('title','Reporte de Ventas')
@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#f1eeff;display:flex;align-items:center;gap:10px">
            <span class="icon" style="color:#a78bfa">receipt_long</span> Ventas globales
        </h1>
        <p style="color:rgba(196,181,253,0.45);font-size:13px;margin-top:4px">Todas las sucursales</p>
    </div>
</div>

@include('superadmin.reportes._filtros', ['branches' => $branches, 'route' => 'superadmin.ventas'])

<div style="background:#13111f;border:1px solid rgba(139,92,246,0.15);border-radius:14px;overflow:hidden">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:rgba(124,58,237,0.08);border-bottom:1px solid rgba(255,255,255,0.07)">
                    @foreach(['Factura','Sucursal','Cliente','Fecha','Items','Total','Estado'] as $h)
                    <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.5)">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $v)
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05)" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 16px;font-family:'Space Mono',monospace;font-size:12px;font-weight:700;color:#a78bfa">{{ $v->numero_factura }}</td>
                    <td style="padding:13px 16px">
                        @if($v->branch)
                        <span style="font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;background:rgba(124,58,237,0.12);color:#c4b5fd;border:1px solid rgba(124,58,237,0.2)">
                            {{ $v->branch->nombre }}
                        </span>
                        @endif
                    </td>
                    <td style="padding:13px 16px;font-size:13px;color:rgba(255,255,255,0.7)">{{ $v->customer->nombre ?? '—' }}</td>
                    <td style="padding:13px 16px">
                        <p style="font-size:13px;color:#f1eeff">{{ $v->created_at->format('d/m/Y') }}</p>
                        <p style="font-size:11px;color:rgba(255,255,255,0.3)">{{ $v->created_at->format('H:i') }}</p>
                    </td>
                    <td style="padding:13px 16px;text-align:center">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:8px;background:rgba(124,58,237,0.15);color:#c4b5fd;font-size:12px;font-weight:700">{{ $v->items_count }}</span>
                    </td>
                    <td style="padding:13px 16px;font-weight:700;color:#f1eeff">{{ $v->total_formateado }}</td>
                    <td style="padding:13px 16px">
                        @php $stClr = ['completada'=>'color:#34d399;background:rgba(52,211,153,0.1);border-color:rgba(52,211,153,0.2)','anulada'=>'color:#f87171;background:rgba(239,68,68,0.1);border-color:rgba(239,68,68,0.2)']; @endphp
                        <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;border:1px solid;{{ $stClr[$v->estado] ?? 'color:rgba(255,255,255,0.4);background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.1)' }}">
                            {{ ucfirst($v->estado) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:60px;text-align:center;color:rgba(255,255,255,0.2)">Sin ventas para mostrar</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ventas->hasPages())
    <div style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.06)">{{ $ventas->links() }}</div>
    @endif
</div>

@endsection
