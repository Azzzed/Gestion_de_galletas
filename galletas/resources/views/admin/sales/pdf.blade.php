<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta {{ $sale->numero_factura }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

        .header { background: #c27020; color: white; padding: 24px 32px; }
        .header h1 { font-size: 24px; font-weight: bold; margin-bottom: 4px; }
        .header p  { font-size: 11px; opacity: .8; }

        .badge-factura { display: inline-block; background: rgba(255,255,255,.2);
                         padding: 4px 12px; border-radius: 20px; font-size: 13px;
                         font-weight: bold; margin-top: 8px; }

        .body { padding: 24px 32px; }

        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-cell { display: table-cell; width: 50%; vertical-align: top; }
        .info-box  { background: #fdf8f3; border: 1px solid #ecdfc4; border-radius: 8px;
                     padding: 12px 16px; margin-right: 8px; }
        .info-box .label { font-size: 9px; text-transform: uppercase; letter-spacing: .05em;
                           color: #6b7280; margin-bottom: 4px; }
        .info-box .value { font-size: 13px; font-weight: 600; color: #1a1a2e; }

        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: .1em;
                         color: #6b7280; margin-bottom: 10px; padding-bottom: 6px;
                         border-bottom: 1px solid #ecdfc4; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { background: #1a1a2e; color: white; padding: 8px 10px;
                         text-align: left; font-size: 10px; font-weight: 600; }
        table.items th:last-child, table.items td:last-child { text-align: right; }
        table.items td { padding: 8px 10px; font-size: 11px; border-bottom: 1px solid #f8f1e4; }
        table.items tr:nth-child(even) td { background: #fdf8f3; }

        .totals { float: right; width: 260px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 10px; font-size: 11px; }
        .totals td:last-child { text-align: right; font-weight: 600; }
        .total-final { background: #c27020; color: white; font-size: 14px; font-weight: bold; }
        .total-final td { padding: 10px; }

        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #ecdfc4;
                  text-align: center; font-size: 10px; color: #9ca3af; }

        .deuda-badge { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;
                       padding: 8px 16px; border-radius: 8px; margin-bottom: 16px;
                       font-size: 11px; font-weight: 600; }
        .clearfix::after { content:''; display:table; clear:both; }
    </style>
</head>
<body>

<div class="header">
    <h1>🐾 CapyCrunch</h1>
    <p>Sistema de Gestión — Comprobante de Venta</p>
    <div class="badge-factura">{{ $sale->numero_factura }}</div>
</div>

<div class="body">

    {{-- Info general --}}
    <div class="info-grid" style="margin-bottom:20px;">
        <div class="info-cell">
            <div class="info-box">
                <div class="label">Cliente</div>
                <div class="value">{{ $sale->customer->nombre }}</div>
                @if($sale->customer->telefono)
                <div style="font-size:11px;color:#6b7280;margin-top:2px;">{{ $sale->customer->telefono }}</div>
                @endif
            </div>
        </div>
        <div class="info-cell">
            <div class="info-box" style="margin-right:0;margin-left:8px;">
                <div class="label">Fecha y hora</div>
                <div class="value">{{ $sale->created_at->format('d/m/Y H:i:s') }}</div>
                <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                    Pago: {{ ucfirst($sale->metodo_pago) }} ·
                    Estado: {{ ucfirst($sale->estado) }}
                </div>
            </div>
        </div>
    </div>

    @if($sale->tiene_deuda)
    <div class="deuda-badge">⚠️ Esta venta tiene deuda pendiente registrada</div>
    @endif

    {{-- Productos --}}
    <p class="section-title">Detalle de productos</p>
    <table class="items">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Tamaño</th>
                <th style="text-align:center">Cant.</th>
                <th>Precio unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td><strong>{{ $item->cookie->nombre }}</strong></td>
                <td>{{ ucfirst($item->cookie->tamano) }}</td>
                <td style="text-align:center">{{ $item->cantidad }}</td>
                <td style="text-align:right">${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                <td>${{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totales --}}
    <div class="clearfix">
        <div class="totals">
            <table>
                <tr>
                    <td style="color:#6b7280">Subtotal</td>
                    <td>${{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($sale->descuento > 0)
                <tr>
                    <td style="color:#dc2626">Descuento ({{ $sale->descuento_porcentaje }}%)</td>
                    <td style="color:#dc2626">− ${{ number_format($sale->descuento, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-final">
                    <td>TOTAL</td>
                    <td>{{ $sale->total_formateado }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($sale->notas)
    <div style="margin-top:24px;padding:12px 16px;background:#fdf8f3;border-radius:8px;font-size:11px;">
        <strong>Notas:</strong> {{ $sale->notas }}
    </div>
    @endif

    <div class="footer">
        <p>CapyCrunch · Sistema de Gestión de Galletería</p>
        <p style="margin-top:4px;">Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>

</body>
</html>
