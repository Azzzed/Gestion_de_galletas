@extends('layouts.superadmin')
@section('title','Reporte de Domicilios')
@section('content')

<div style="margin-bottom:24px">
    <h1 style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#f1eeff;display:flex;align-items:center;gap:10px">
        <span class="icon" style="color:#a78bfa">local_shipping</span> Domicilios globales
    </h1>
    <p style="color:rgba(196,181,253,0.45);font-size:13px;margin-top:4px">Todos los domicilios de todas las sucursales</p>
</div>

@include('superadmin.reportes._filtros', ['branches'=>$branches,'route'=>'superadmin.domicilios'])

<div style="background:#13111f;border:1px solid rgba(139,92,246,0.15);border-radius:14px;overflow:hidden">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:rgba(124,58,237,0.08);border-bottom:1px solid rgba(255,255,255,0.07)">
                    @foreach(['Sucursal','Cliente','Dirección','Fecha','Total','Pago','Estado'] as $h)
                    <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:rgba(196,181,253,0.5)">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($domicilios as $d)
                @php
                    $sClr = ['scheduled'=>'color:#fbbf24;border-color:rgba(251,191,36,.2);background:rgba(251,191,36,.08)','dispatched'=>'color:#60a5fa;border-color:rgba(96,165,250,.2);background:rgba(96,165,250,.08)','delivered'=>'color:#34d399;border-color:rgba(52,211,153,.2);background:rgba(52,211,153,.08)','cancelled'=>'color:#f87171;border-color:rgba(248,113,113,.2);background:rgba(248,113,113,.08)'];
                    $sLbl = ['scheduled'=>'⏳ Agendado','dispatched'=>'🛵 En camino','delivered'=>'✅ Entregado','cancelled'=>'❌ Cancelado'];
                @endphp
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05)" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 16px">
                        @if($d->branch)
                        <span style="font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;background:rgba(124,58,237,0.12);color:#c4b5fd;border:1px solid rgba(124,58,237,0.2)">
                            {{ $d->branch->nombre }}
                        </span>
                        @endif
                    </td>
                    <td style="padding:13px 16px;font-size:13px;color:rgba(255,255,255,0.7)">
                        {{ $d->customer?->nombre ?? $d->customer_name ?? '—' }}
                    </td>
                    <td style="padding:13px 16px;font-size:12px;color:rgba(255,255,255,0.5);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $d->delivery_address }}
                    </td>
                    <td style="padding:13px 16px">
                        <p style="font-size:13px;color:#f1eeff">{{ $d->created_at->format('d/m/Y') }}</p>
                        <p style="font-size:11px;color:rgba(255,255,255,0.3)">{{ $d->created_at->format('H:i') }}</p>
                    </td>
                    <td style="padding:13px 16px;font-weight:700;color:#f1eeff">${{ number_format($d->total,0,',','.') }}</td>
                    <td style="padding:13px 16px;font-size:12px;color:rgba(255,255,255,0.5);text-transform:capitalize">{{ $d->payment_method }}</td>
                    <td style="padding:13px 16px">
                        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid;{{ $sClr[$d->status] ?? '' }}">
                            {{ $sLbl[$d->status] ?? $d->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:60px;text-align:center;color:rgba(255,255,255,0.2)">Sin domicilios para mostrar</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($domicilios->hasPages())
    <div style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.06)">{{ $domicilios->links() }}</div>
    @endif
</div>

@endsection
