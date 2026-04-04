@extends('layouts.app')
@section('title', 'Estadísticas')

@section('content')
{{-- Chart.js debe cargarse ANTES del contenido, sin defer --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- ── HEADER ────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
            <span class="icon icon-lg text-brand-500">analytics</span>
            Panel de Estadísticas
        </h1>
        <p class="page-subtitle">Análisis de desempeño del negocio</p>
    </div>
    <div class="flex gap-2 flex-wrap items-center" id="dateControls">
        <input type="date" id="inputDesde" value="{{ $desde }}" class="field text-sm py-2 w-auto">
        <span class="text-espresso-700/40 text-sm">→</span>
        <input type="date" id="inputHasta" value="{{ $hasta }}" class="field text-sm py-2 w-auto">
        <button onclick="setRange(7)"  class="btn-ghost py-2 px-3 text-xs font-bold">7d</button>
        <button onclick="setRange(30)" class="btn-ghost py-2 px-3 text-xs font-bold">30d</button>
        <button onclick="setRange(90)" class="btn-ghost py-2 px-3 text-xs font-bold">90d</button>
        <button onclick="statsReload()" class="btn-primary py-2 px-4 text-sm">
            <span class="icon icon-sm">refresh</span>
        </button>
    </div>
</div>

{{-- ── KPIs (server-side, sin JS) ──────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3 mb-6">
    <div class="rounded-2xl p-4 bg-gradient-to-br from-espresso-900 to-darkBrown shadow-md">
        <span class="icon text-white/60 block mb-1">receipt_long</span>
        <p class="font-display font-bold text-white text-xl">{{ number_format($kpis['total_ventas']) }}</p>
        <p class="text-white/60 text-[10px] font-bold uppercase tracking-wide mt-0.5">Total Ventas</p>
    </div>
    <div class="rounded-2xl p-4 bg-gradient-to-br from-brand-600 to-brand-500 shadow-md xl:col-span-2">
        <span class="icon text-white/60 block mb-1">payments</span>
        <p class="font-display font-bold text-white text-xl">${{ number_format($kpis['total_ingresos'],0,',','.') }}</p>
        <p class="text-white/60 text-[10px] font-bold uppercase tracking-wide mt-0.5">Ingresos Totales · incl. domicilios</p>
    </div>
    <div class="rounded-2xl p-4 bg-gradient-to-br from-green-700 to-green-600 shadow-md">
        <span class="icon text-white/60 block mb-1">storefront</span>
        <p class="font-display font-bold text-white text-xl">${{ number_format($kpis['ingresos_ventas'],0,',','.') }}</p>
        <p class="text-white/60 text-[10px] font-bold uppercase tracking-wide mt-0.5">Ventas POS</p>
    </div>
    <div class="rounded-2xl p-4 bg-gradient-to-br from-blue-700 to-blue-500 shadow-md">
        <span class="icon text-white/60 block mb-1">local_shipping</span>
        <p class="font-display font-bold text-white text-xl">${{ number_format($kpis['ingresos_deliv'],0,',','.') }}</p>
        <p class="text-white/60 text-[10px] font-bold uppercase tracking-wide mt-0.5">Domicilios</p>
    </div>
    <div class="rounded-2xl p-4 bg-gradient-to-br from-teal-700 to-teal-500 shadow-md">
        <span class="icon text-white/60 block mb-1">directions_bike</span>
        <p class="font-display font-bold text-white text-xl">${{ number_format($kpis['costo_envios'],0,',','.') }}</p>
        <p class="text-white/60 text-[10px] font-bold uppercase tracking-wide mt-0.5">Costo Envíos</p>
    </div>
    <div class="rounded-2xl p-4 bg-gradient-to-br from-amber-600 to-amber-500 shadow-md">
        <span class="icon text-white/60 block mb-1">bakery_dining</span>
        <p class="font-display font-bold text-white text-base leading-tight truncate">{{ $kpis['top_cookie'] }}</p>
        <p class="text-white/60 text-[10px] font-bold uppercase tracking-wide mt-0.5">Más Vendida</p>
    </div>
</div>

{{-- ── GRÁFICAS ─────────────────────────────────────────────── --}}

{{-- ROW 1: Ingresos + Top galletas --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    <div class="card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2">
                <span class="icon text-brand-500">trending_up</span>
                Ingresos en el tiempo
            </h3>
            <div class="flex gap-1">
                <button id="btnIngresos" onclick="toggleRevenue('ingresos')"
                        class="btn-primary py-1 px-3 text-xs">Ingresos</button>
                <button id="btnVentas" onclick="toggleRevenue('ventas')"
                        class="btn-ghost py-1 px-3 text-xs">N° Ventas</button>
            </div>
        </div>
        <div class="relative" style="height:230px">
            <canvas id="chartRevenue"></canvas>
            <div id="loadRevenue" class="absolute inset-0 flex items-center justify-center">
                <span class="icon text-brand-400 text-3xl" style="animation:spin 1s linear infinite">progress_activity</span>
            </div>
            <div id="errRevenue" class="absolute inset-0 hidden flex items-center justify-center text-red-400 text-xs text-center p-4"></div>
        </div>
    </div>

    <div class="card p-5">
        <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
            <span class="icon text-brand-500">emoji_events</span>
            Galletas Más Vendidas
        </h3>
        <div class="relative" style="height:230px">
            <canvas id="chartCookies"></canvas>
            <div id="loadCookies" class="absolute inset-0 flex items-center justify-center">
                <span class="icon text-brand-400 text-3xl" style="animation:spin 1s linear infinite">progress_activity</span>
            </div>
            <div id="emptyCookies" class="hidden absolute inset-0 flex flex-col items-center justify-center text-espresso-700/30">
                <span class="icon text-3xl block mb-2">bakery_dining</span>
                <p class="text-sm">Sin datos</p>
            </div>
            <div id="errCookies" class="absolute inset-0 hidden flex items-center justify-center text-red-400 text-xs text-center p-4"></div>
        </div>
    </div>
</div>

{{-- ROW 2: Horas pico + Métodos de pago --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    <div class="card p-5">
        <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
            <span class="icon text-brand-500">schedule</span>
            Horas pico de ventas
        </h3>
        <div class="relative" style="height:210px">
            <canvas id="chartHour"></canvas>
            <div id="loadHour" class="absolute inset-0 flex items-center justify-center">
                <span class="icon text-brand-400 text-3xl" style="animation:spin 1s linear infinite">progress_activity</span>
            </div>
            <div id="errHour" class="absolute inset-0 hidden flex items-center justify-center text-red-400 text-xs text-center p-4"></div>
        </div>
        <p class="text-xs text-espresso-700/40 mt-2 text-center">Ventas POS + domicilios por hora</p>
    </div>

    <div class="card p-5">
        <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
            <span class="icon text-brand-500">payments</span>
            Ingresos por Método de Pago
        </h3>
        <div class="relative" style="height:210px">
            <canvas id="chartPayment"></canvas>
            <div id="loadPayment" class="absolute inset-0 flex items-center justify-center">
                <span class="icon text-brand-400 text-3xl" style="animation:spin 1s linear infinite">progress_activity</span>
            </div>
            <div id="errPayment" class="absolute inset-0 hidden flex items-center justify-center text-red-400 text-xs text-center p-4"></div>
        </div>
    </div>
</div>

{{-- ROW 3: Top clientes + Domicilios hoy --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    <div class="card p-5">
        <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
            <span class="icon text-brand-500">star</span>
            Mejores Clientes del Período
        </h3>
        <div id="loadClients" class="flex justify-center py-8">
            <span class="icon text-brand-400 text-3xl" style="animation:spin 1s linear infinite">progress_activity</span>
        </div>
        <div id="listClients" class="space-y-2 hidden"></div>
        <div id="emptyClients" class="hidden text-center py-8 text-espresso-700/30 text-sm">Sin datos</div>
        <div id="errClients" class="hidden text-center py-4 text-red-400 text-xs"></div>
    </div>

    <div class="card p-5">
        <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
            <span class="icon text-brand-500">local_shipping</span>
            Domicilios de Hoy
        </h3>
        <div class="grid grid-cols-2 gap-3 mb-4" id="deliveryGrid">
            <div class="rounded-xl p-4 text-center bg-amber-50 border border-amber-200">
                <p class="font-display font-bold text-2xl text-amber-700" id="dScheduled">—</p>
                <p class="text-xs text-amber-600 font-semibold mt-1">⏳ Agendados</p>
            </div>
            <div class="rounded-xl p-4 text-center bg-blue-50 border border-blue-200">
                <p class="font-display font-bold text-2xl text-blue-700" id="dDispatched">—</p>
                <p class="text-xs text-blue-600 font-semibold mt-1">🛵 En camino</p>
            </div>
            <div class="rounded-xl p-4 text-center bg-green-50 border border-green-200">
                <p class="font-display font-bold text-2xl text-green-700" id="dDelivered">—</p>
                <p class="text-xs text-green-600 font-semibold mt-1">✅ Entregados</p>
            </div>
            <div class="rounded-xl p-4 text-center bg-brand-50 border border-brand-200">
                <p class="font-display font-bold text-xl text-brand-700" id="dRevenue">—</p>
                <p class="text-xs text-brand-600 font-semibold mt-1">💰 Cobrado</p>
            </div>
        </div>
        <div id="envioBox" class="hidden flex items-center justify-between px-4 py-3 rounded-xl bg-teal-50 border border-teal-200 mb-3">
            <div class="flex items-center gap-2">
                <span class="icon text-teal-500 text-sm">directions_bike</span>
                <p class="text-sm font-bold text-teal-700">Ingresos envíos hoy</p>
            </div>
            <span class="font-bold text-teal-700" id="dEnvio"></span>
        </div>
        <a href="{{ route('admin.deliveries.index') }}" class="btn-primary w-full justify-center py-2.5">
            <span class="icon icon-sm">open_in_new</span> Ver domicilios
        </a>
    </div>
</div>

{{-- ── JS ─────────────────────────────────────────────────────── --}}
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

<script>
(function () {
    'use strict';

    // ── Estado ──────────────────────────────────────────────────
    const CHARTS  = {};
    let REV_DATA  = null;
    let REV_MODE  = 'ingresos';
    const PALETTE = ['#EA6008','#F59E0B','#7C3AED','#1D4ED8','#15803D','#0891B2','#DC2626','#854D0E'];
    const CSRF    = document.querySelector('meta[name=csrf-token]')?.content ?? '';

    function getRange() {
        return {
            desde: document.getElementById('inputDesde').value,
            hasta: document.getElementById('inputHasta').value,
        };
    }

    // ── DOM helpers ─────────────────────────────────────────────
    function show(id) { const el = document.getElementById(id); if (el) el.classList.remove('hidden'); }
    function hide(id) { const el = document.getElementById(id); if (el) el.classList.add('hidden');    }
    function text(id, t) { const el = document.getElementById(id); if (el) el.textContent = t;        }
    function html(id, h) { const el = document.getElementById(id); if (el) el.innerHTML = h;          }
    function fmt(v) { return '$' + Math.round(Number(v) || 0).toLocaleString('es-CO'); }

    // ── Fetch JSON seguro ────────────────────────────────────────
    async function api(url) {
        const r = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    }

    // ── Gráfica: Ingresos timeline ───────────────────────────────
    async function loadRevenue() {
        const { desde, hasta } = getRange();
        hide('errRevenue');
        show('loadRevenue');
        try {
            const d = await api('/admin/stats/api/revenue?desde=' + desde + '&hasta=' + hasta);
            REV_DATA = d;
            hide('loadRevenue');
            if (CHARTS.revenue) CHARTS.revenue.destroy();

            CHARTS.revenue = new Chart(document.getElementById('chartRevenue'), {
                type: 'line',
                data: {
                    labels: d.labels,
                    datasets: [
                        {
                            label: 'Ingresos totales',
                            data: d.ingresos,
                            borderColor: '#EA6008',
                            backgroundColor: 'rgba(234,96,8,0.07)',
                            borderWidth: 2.5, pointRadius: 3,
                            pointBackgroundColor: '#EA6008',
                            fill: true, tension: 0.35, yAxisID: 'y',
                        },
                        {
                            label: '# Domicilios',
                            data: d.domicilios,
                            borderColor: '#3B82F6',
                            borderWidth: 1.5, pointRadius: 2,
                            borderDash: [5, 3], tension: 0.35,
                            fill: false, yAxisID: 'y2',
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, labels: { font: { size: 11 }, color: '#3B1F0E', boxWidth: 12 } }
                    },
                    scales: {
                        x:  { grid: { display: false }, ticks: { maxTicksLimit: 10, color: '#9CA3AF', font: { size: 10 } } },
                        y:  { grid: { color: '#F5E5C020' }, ticks: { color: '#9CA3AF', callback: v => '$' + Number(v).toLocaleString('es-CO') } },
                        y2: { position: 'right', grid: { display: false }, ticks: { color: '#3B82F6', precision: 0, font: { size: 10 } } },
                    }
                }
            });
        } catch (e) {
            hide('loadRevenue');
            show('errRevenue');
            text('errRevenue', '⚠ ' + e.message + ' — ' + (e.stack?.split('\n')[0] ?? ''));
            console.error('[Revenue]', e);
        }
    }

    window.toggleRevenue = function (mode) {
        REV_MODE = mode;
        document.getElementById('btnIngresos').className = mode === 'ingresos' ? 'btn-primary py-1 px-3 text-xs' : 'btn-ghost py-1 px-3 text-xs';
        document.getElementById('btnVentas').className   = mode === 'ventas'   ? 'btn-primary py-1 px-3 text-xs' : 'btn-ghost py-1 px-3 text-xs';
        if (!CHARTS.revenue || !REV_DATA) return;
        const ds = CHARTS.revenue.data.datasets[0];
        ds.data  = mode === 'ventas' ? REV_DATA.ventas : REV_DATA.ingresos;
        ds.label = mode === 'ventas' ? 'N° Ventas' : 'Ingresos totales';
        CHARTS.revenue.options.scales.y.ticks.callback = mode === 'ventas'
            ? v => v
            : v => '$' + Number(v).toLocaleString('es-CO');
        CHARTS.revenue.update();
    };

    // ── Gráfica: Galletas más vendidas ───────────────────────────
    async function loadCookies() {
        const { desde, hasta } = getRange();
        hide('errCookies'); hide('emptyCookies');
        show('loadCookies');
        try {
            const d = await api('/admin/stats/api/top-cookies?desde=' + desde + '&hasta=' + hasta);
            hide('loadCookies');
            if (!d.labels || !d.labels.length) { show('emptyCookies'); return; }
            if (CHARTS.cookies) CHARTS.cookies.destroy();

            CHARTS.cookies = new Chart(document.getElementById('chartCookies'), {
                type: 'doughnut',
                data: {
                    labels: d.labels,
                    datasets: [{ data: d.vendidas, backgroundColor: PALETTE, borderWidth: 2, borderColor: '#FFFFFF' }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 8, font: { size: 10 }, color: '#3B1F0E', boxWidth: 10 }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' und.'
                            }
                        }
                    }
                }
            });
        } catch (e) {
            hide('loadCookies');
            show('errCookies');
            text('errCookies', '⚠ ' + e.message);
            console.error('[Cookies]', e);
        }
    }

    // ── Gráfica: Horas pico ──────────────────────────────────────
    async function loadHour() {
        const { desde, hasta } = getRange();
        hide('errHour');
        show('loadHour');
        try {
            const d = await api('/admin/stats/api/by-hour?desde=' + desde + '&hasta=' + hasta);
            hide('loadHour');
            if (CHARTS.hour) CHARTS.hour.destroy();
            const max = Math.max(...d.ventas, 1);

            CHARTS.hour = new Chart(document.getElementById('chartHour'), {
                type: 'bar',
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: 'Transacciones',
                        data: d.ventas,
                        backgroundColor: d.ventas.map(v => {
                            const a = (0.15 + (v / max) * 0.85).toFixed(2);
                            return 'rgba(234,96,8,' + a + ')';
                        }),
                        borderRadius: 4, borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 12, color: '#9CA3AF', font: { size: 9 } } },
                        y: { grid: { color: '#F5E5C020' }, ticks: { color: '#9CA3AF', precision: 0 } }
                    }
                }
            });
        } catch (e) {
            hide('loadHour');
            show('errHour');
            text('errHour', '⚠ ' + e.message);
            console.error('[Hour]', e);
        }
    }

    // ── Gráfica: Métodos de pago ─────────────────────────────────
    async function loadPayment() {
        const { desde, hasta } = getRange();
        hide('errPayment');
        show('loadPayment');
        try {
            const d = await api('/admin/stats/api/by-payment?desde=' + desde + '&hasta=' + hasta);
            hide('loadPayment');
            if (!d.labels || !d.labels.length) return;
            if (CHARTS.payment) CHARTS.payment.destroy();

            CHARTS.payment = new Chart(document.getElementById('chartPayment'), {
                type: 'bar',
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: 'Ingresos',
                        data: d.totales,
                        backgroundColor: PALETTE.slice(0, d.labels.length),
                        borderRadius: 6, borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            grid: { color: '#F5E5C020' },
                            ticks: { callback: v => '$' + Number(v).toLocaleString('es-CO'), color: '#9CA3AF' }
                        },
                        y: { grid: { display: false }, ticks: { color: '#3B1F0E', font: { weight: '600' } } }
                    }
                }
            });
        } catch (e) {
            hide('loadPayment');
            show('errPayment');
            text('errPayment', '⚠ ' + e.message);
            console.error('[Payment]', e);
        }
    }

    // ── Top clientes ─────────────────────────────────────────────
    async function loadClients() {
        const { desde, hasta } = getRange();
        show('loadClients'); hide('listClients'); hide('emptyClients'); hide('errClients');
        try {
            const data = await api('/admin/sales/top-clients?desde=' + desde + '&hasta=' + hasta);
            hide('loadClients');
            if (!data || !data.length) { show('emptyClients'); return; }
            const badges = ['bg-amber-400 text-white', 'bg-slate-300 text-white', 'bg-cream-200 text-espresso-700'];
            html('listClients', data.map((c, i) =>
                `<div class="flex items-center gap-3 p-3 rounded-xl bg-cream-50 border border-cream-200">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 ${badges[i] || badges[2]}">${i+1}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-espresso-900 truncate">${c.nombre}</p>
                        <p class="text-xs text-espresso-700/50">${c.compras} compras</p>
                    </div>
                    <span class="font-display font-bold text-brand-700">${c.formatted}</span>
                </div>`
            ).join(''));
            show('listClients');
        } catch (e) {
            hide('loadClients');
            show('errClients');
            text('errClients', '⚠ ' + e.message);
            console.error('[Clients]', e);
        }
    }

    // ── Domicilios de hoy ────────────────────────────────────────
    async function loadDelivery() {
        try {
            const d = await api('/admin/stats/api/delivery-summary');
            text('dScheduled',  d.scheduled);
            text('dDispatched', d.dispatched);
            text('dDelivered',  d.delivered);
            text('dRevenue',    fmt(d.revenue));
            if (d.costo_envio > 0) {
                text('dEnvio', fmt(d.costo_envio));
                show('envioBox');
            }
        } catch (e) {
            console.error('[Delivery]', e);
        }
    }

    // ── Rango rápido ─────────────────────────────────────────────
    window.setRange = function (days) {
        const end   = new Date();
        const start = new Date();
        start.setDate(start.getDate() - days + 1);
        document.getElementById('inputDesde').value = start.toISOString().slice(0, 10);
        document.getElementById('inputHasta').value = end.toISOString().slice(0, 10);
        statsReload();
    };

    // ── Reload global ────────────────────────────────────────────
    window.statsReload = function () {
        loadRevenue();
        loadCookies();
        loadHour();
        loadPayment();
        loadClients();
    };

    // ── Init ─────────────────────────────────────────────────────
    document.getElementById('inputDesde').addEventListener('change', statsReload);
    document.getElementById('inputHasta').addEventListener('change', statsReload);

    // Esperar a que Chart.js esté listo
    function waitForChartJs(cb, attempts) {
        if (typeof Chart !== 'undefined') { cb(); return; }
        if (attempts <= 0) { console.error('Chart.js no cargó'); return; }
        setTimeout(() => waitForChartJs(cb, attempts - 1), 200);
    }

    // Aumentado a 30 intentos x 200ms = 6 segundos de espera máxima
    waitForChartJs(function () {
        statsReload();
        loadDelivery();
    }, 30);

}());
</script>

@endsection