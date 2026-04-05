<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Venta {{ $sale->numero_factura }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #2a1206;
            background: #fff;
        }

        /* ── HEADER ─────────────────────────────────────────── */
        .header {
            background: #1a0a00;
            padding: 0;
        }
        .header-inner {
            padding: 28px 36px 22px;
        }
        .header-accent {
            height: 5px;
            background: linear-gradient(to right, #ea6008, #FF750F, #c24808);
        }

        .brand-row {
            display: table;
            width: 100%;
        }
        .brand-logo-cell {
            display: table-cell;
            width: 68px;
            vertical-align: middle;
            padding-right: 16px;
        }
        .brand-logo {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            border: 2px solid rgba(255,255,255,0.15);
            object-fit: cover;
        }
        .brand-logo-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: #ea6008;
            border: 2px solid rgba(255,255,255,0.15);
            text-align: center;
            padding-top: 12px;
            font-size: 26px;
            color: white;
        }
        .brand-text-cell {
            display: table-cell;
            vertical-align: middle;
        }
        .brand-name {
            font-size: 22px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 0.5px;
        }
        .brand-tagline {
            font-size: 10px;
            color: rgba(255,255,255,0.45);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .factura-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .factura-label {
            font-size: 9px;
            color: rgba(255,255,255,0.40);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .factura-num {
            font-size: 18px;
            font-weight: bold;
            color: #FF750F;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        /* ── ESTADO BADGE ────────────────────────────────────── */
        .status-bar {
            background: #2a1206;
            padding: 8px 36px;
            display: table;
            width: 100%;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .status-completada { background: #14532d; color: #86efac; }
        .status-anulada    { background: #7f1d1d; color: #fca5a5; }
        .status-pendiente  { background: #78350f; color: #fcd34d; }

        .date-right {
            font-size: 10px;
            color: rgba(255,255,255,0.40);
            float: right;
            margin-top: 3px;
        }

        /* ── BODY ────────────────────────────────────────────── */
        .body { padding: 24px 36px 0; }

        /* ── INFO GRID ───────────────────────────────────────── */
        .info-grid { display: table; width: 100%; margin-bottom: 22px; }
        .info-cell { display: table-cell; width: 50%; vertical-align: top; }

        .info-box {
            background: #fdf8f3;
            border: 1px solid #ecdfc4;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .info-box-left  { margin-right: 8px; }
        .info-box-right { margin-left: 8px; }

        .info-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9a7a5a;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .info-value {
            font-size: 13px;
            font-weight: bold;
            color: #1a0a00;
        }
        .info-sub {
            font-size: 10px;
            color: #9a7a5a;
            margin-top: 3px;
        }

        /* ── DEUDA ALERT ─────────────────────────────────────── */
        .deuda-alert {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-left: 4px solid #ea6008;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 10px;
            color: #9a3412;
            font-weight: bold;
        }

        /* ── SECTION TITLE ───────────────────────────────────── */
        .section-title {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #9a7a5a;
            font-weight: bold;
            padding-bottom: 8px;
            border-bottom: 2px solid #ea6008;
            margin-bottom: 12px;
        }

        /* ── ITEMS TABLE ─────────────────────────────────────── */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items thead tr {
            background: #2a1206;
        }
        table.items thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.70);
            font-weight: bold;
        }
        table.items thead th:last-child,
        table.items thead th:nth-child(3),
        table.items thead th:nth-child(4) {
            text-align: right;
        }
        table.items tbody tr {
            border-bottom: 1px solid #f5e5c0;
        }
        table.items tbody tr:last-child { border-bottom: none; }
        table.items tbody tr:nth-child(even) {
            background: #fdf8f3;
        }
        table.items td {
            padding: 9px 10px;
            vertical-align: middle;
        }
        table.items td.name { font-weight: bold; color: #1a0a00; font-size: 11.5px; }
        table.items td.size { color: #9a7a5a; font-size: 10px; }
        table.items td.qty  { text-align: center; font-weight: bold; }
        table.items td.price,
        table.items td.sub  { text-align: right; }
        table.items td.sub  { font-weight: bold; color: #c24808; }

        /* ── TOTALES ─────────────────────────────────────────── */
        .totals-wrap { text-align: right; margin-bottom: 28px; }
        .totals-table { display: inline-table; min-width: 220px; }
        .totals-table td { padding: 4px 0; font-size: 11px; }
        .totals-table td.lbl { color: #9a7a5a; padding-right: 20px; }
        .totals-table td.val { font-weight: bold; text-align: right; }
        .totals-total-row td {
            padding-top: 10px;
            border-top: 2px solid #ea6008;
            font-size: 15px;
        }
        .totals-total-row td.lbl { color: #1a0a00; font-weight: bold; }
        .totals-total-row td.val { color: #ea6008; font-weight: bold; font-size: 17px; }

        .discount-row td { color: #dc2626 !important; }

        /* ── PAYMENT METHOD ──────────────────────────────────── */
        .payment-row {
            display: table;
            width: 100%;
            margin-bottom: 28px;
        }
        .payment-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .pay-efectivo  { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .pay-nequi     { background: #ede9fe; color: #4c1d95; border: 1px solid #c4b5fd; }
        .pay-daviplata { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
        .pay-default   { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        /* ── FOOTER ──────────────────────────────────────────── */
        .footer {
            background: #1a0a00;
            padding: 14px 36px;
            margin-top: 10px;
        }
        .footer-inner { display: table; width: 100%; }
        .footer-left  { display: table-cell; vertical-align: middle; }
        .footer-right { display: table-cell; vertical-align: middle; text-align: right; }
        .footer p { font-size: 9px; color: rgba(255,255,255,0.40); }
        .footer .footer-brand { font-size: 11px; color: #FF750F; font-weight: bold; }
        .footer-accent {
            height: 3px;
            background: linear-gradient(to right, #ea6008, #FF750F, #c24808);
        }

        /* Nota de venta */
        .nota-box {
            background: #fdf8f3;
            border: 1px solid #ecdfc4;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 10px;
            color: #6b4c2a;
        }
        .nota-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9a7a5a;
            font-weight: bold;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

{{-- ══ HEADER ══════════════════════════════════════════════════ --}}
<div class="header-accent"></div>
<div class="header">
    <div class="header-inner">
        <div class="brand-row">

            {{-- Logo --}}
            <div class="brand-logo-cell">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Capy Crunch" class="brand-logo">
                @else
                    <div class="brand-logo-placeholder">🍪</div>
                @endif
            </div>

            {{-- Nombre y tagline --}}
            <div class="brand-text-cell">
                <div class="brand-name">Capy Crunch</div>
                <div class="brand-tagline">Galletas artesanales</div>
            </div>

            {{-- Número de factura --}}
            <div class="factura-cell">
                <div class="factura-label">Comprobante de venta</div>
                <div class="factura-num">{{ $sale->numero_factura }}</div>
            </div>
        </div>
    </div>
</div>
<div class="header-accent"></div>

{{-- ══ STATUS BAR ═══════════════════════════════════════════════ --}}
<div class="status-bar">
    @php
        $statusClass = match($sale->estado) {
            'completada' => 'status-completada',
            'anulada'    => 'status-anulada',
            default      => 'status-pendiente',
        };
        $statusLabel = ucfirst($sale->estado);
    @endphp
    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
    <span class="date-right">{{ $sale->created_at->format('d/m/Y H:i:s') }}</span>
</div>

{{-- ══ BODY ══════════════════════════════════════════════════════ --}}
<div class="body">

    {{-- ── Info cliente / fecha ──────────────────────────────── --}}
    <div class="info-grid" style="margin-top:20px">
        <div class="info-cell">
            <div class="info-box info-box-left">
                <div class="info-label">👤 Cliente</div>
                <div class="info-value">{{ $sale->customer->nombre }}</div>
                @if($sale->customer->telefono)
                <div class="info-sub">📞 {{ $sale->customer->telefono }}</div>
                @endif
                @if($sale->customer->email)
                <div class="info-sub">✉ {{ $sale->customer->email }}</div>
                @endif
            </div>
        </div>
        <div class="info-cell">
            <div class="info-box info-box-right">
                <div class="info-label">📅 Fecha y pago</div>
                <div class="info-value">{{ $sale->created_at->format('d M Y') }}</div>
                <div class="info-sub">{{ $sale->created_at->format('H:i:s') }}</div>
                <div class="info-sub" style="margin-top:4px">
                    Método: <strong>{{ ucfirst($sale->metodo_pago) }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Alerta deuda ──────────────────────────────────────── --}}
    @if($sale->tiene_deuda)
    <div class="deuda-alert">
        ⚠️ Esta venta tiene deuda pendiente registrada. Se acordó pago posterior.
    </div>
    @endif

    {{-- ── Nota ─────────────────────────────────────────────── --}}
    @if($sale->notas)
    <div class="nota-box">
        <div class="nota-label">📝 Nota</div>
        {{ $sale->notas }}
    </div>
    @endif

    {{-- ── Productos ─────────────────────────────────────────── --}}
    <p class="section-title">Detalle de productos</p>

    <table class="items">
        <thead>
            <tr>
                <th style="width:36%">Producto</th>
                <th style="width:15%">Tamaño</th>
                <th style="width:10%; text-align:center">Cant.</th>
                <th style="width:18%; text-align:right">Precio unit.</th>
                <th style="width:18%; text-align:right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td class="name">{{ $item->cookie->nombre }}</td>
                <td class="size">{{ ucfirst($item->cookie->tamano) }}</td>
                <td class="qty">{{ $item->cantidad }}</td>
                <td class="price">${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                <td class="sub">${{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Totales ───────────────────────────────────────────── --}}
    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td class="lbl">Subtotal</td>
                <td class="val">${{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($sale->descuento > 0)
            <tr class="discount-row">
                <td class="lbl" style="color:#dc2626">Descuento ({{ $sale->descuento_porcentaje }}%)</td>
                <td class="val" style="color:#dc2626">− ${{ number_format($sale->descuento, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="totals-total-row">
                <td class="lbl">Total</td>
                <td class="val">${{ number_format($sale->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- ── Método de pago badge ──────────────────────────────── --}}
    <div class="payment-row">
        <p class="section-title" style="border:none;padding-bottom:6px">Método de pago</p>
        @php
            $payClass = match($sale->metodo_pago) {
                'efectivo'  => 'pay-efectivo',
                'nequi'     => 'pay-nequi',
                'daviplata' => 'pay-daviplata',
                default     => 'pay-default',
            };
            $payIcon = match($sale->metodo_pago) {
                'efectivo'  => '💵',
                'nequi'     => '📱',
                'daviplata' => '💳',
                default     => '💰',
            };
        @endphp
        <span class="payment-badge {{ $payClass }}">
            {{ $payIcon }} {{ ucfirst($sale->metodo_pago) }}
        </span>
        @if($sale->tiene_deuda)
        <span class="payment-badge" style="margin-left:8px;background:#fff7ed;color:#9a3412;border:1px solid #fed7aa">
            ⚠ Con deuda pendiente
        </span>
        @endif
    </div>

</div>{{-- /body --}}

{{-- ══ FOOTER ═══════════════════════════════════════════════════ --}}
<div class="footer-accent"></div>
<div class="footer">
    <div class="footer-inner">
        <div class="footer-left">
            <p class="footer-brand">Capy Crunch</p>
            <p>Galletas artesanales · Gracias por tu compra 🍪</p>
        </div>
        <div class="footer-right">
            <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
            <p>Ref: <strong style="color:#FF750F">{{ $sale->numero_factura }}</strong></p>
        </div>
    </div>
</div>

</body>
</html>