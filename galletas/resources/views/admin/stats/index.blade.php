@extends('layouts.app')
@section('title','Estadísticas')

@section('content')
<div x-data="statsApp()" x-init="init()">

    {{-- ── HEADER ──────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-display font-bold text-espresso-900 text-2xl flex items-center gap-2">
                <span class="icon icon-lg text-brand-500">analytics</span>
                Panel de Estadísticas
            </h1>
            <p class="page-subtitle">Análisis de desempeño del negocio</p>
        </div>
        <div class="flex gap-2 flex-wrap items-center">
            <input type="date" x-model="desde" @change="reload()" class="field text-sm py-2 w-auto">
            <span class="text-espresso-700/40 text-sm">→</span>
            <input type="date" x-model="hasta" @change="reload()" class="field text-sm py-2 w-auto">
            <button @click="setRange('7')"  class="btn-ghost py-2 text-xs">7d</button>
            <button @click="setRange('30')" class="btn-ghost py-2 text-xs">30d</button>
            <button @click="setRange('90')" class="btn-ghost py-2 text-xs">90d</button>
        </div>
    </div>

    {{-- ── KPIs ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        @php
            $kpiItems = [
                ['Total Ventas', number_format($kpis['total_ventas']), 'receipt_long', 'from-espresso-900 to-darkBrown'],
                ['Ingresos', '$'.number_format($kpis['total_ingresos'],0,',','.'), 'payments', 'from-brand-600 to-brand-500'],
                ['Ticket Promedio', '$'.number_format($kpis['ticket_promedio'],0,',','.'), 'analytics', 'from-purple-700 to-purple-500'],
                ['Más Vendida', $kpis['top_cookie'], 'bakery_dining', 'from-amber-600 to-amber-500'],
                ['Domicilios', $kpis['domicilios'], 'local_shipping', 'from-blue-700 to-blue-500'],
            ];
        @endphp
        @foreach($kpiItems as [$label, $val, $icon, $gradient])
        <div class="rounded-2xl p-5 bg-gradient-to-br {{ $gradient }} shadow-md">
            <span class="icon text-white/60 block mb-2">{{ $icon }}</span>
            <p class="font-display font-bold text-white text-xl leading-tight">{{ $val }}</p>
            <p class="text-white/60 text-xs font-semibold mt-1 uppercase tracking-wide">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── ROW 1: Ingresos + Más vendidas ─────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

        {{-- Línea de ingresos (ocupa 2 cols) --}}
        <div class="card p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2">
                    <span class="icon text-brand-500">trending_up</span>
                    Ingresos en el tiempo
                </h3>
                <div class="flex gap-1">
                    <button @click="revenueMode='ingresos'" :class="revenueMode==='ingresos' ? 'btn-primary py-1 text-xs' : 'btn-ghost py-1 text-xs'">Ingresos</button>
                    <button @click="revenueMode='ventas'"  :class="revenueMode==='ventas'  ? 'btn-primary py-1 text-xs' : 'btn-ghost py-1 text-xs'">N° Ventas</button>
                </div>
            </div>
            <div style="position:relative;height:220px">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        {{-- Galletas más vendidas --}}
        <div class="card p-5">
            <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
                <span class="icon text-brand-500">emoji_events</span>
                Más Vendidas
            </h3>
            <div style="position:relative;height:220px">
                <canvas id="topCookiesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── ROW 2: Por hora + Métodos de pago ──────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

        {{-- Ventas por hora --}}
        <div class="card p-5">
            <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
                <span class="icon text-brand-500">schedule</span>
                Horas pico de ventas
            </h3>
            <div style="position:relative;height:200px">
                <canvas id="hourChart"></canvas>
            </div>
            <p class="text-xs text-espresso-700/40 mt-2 text-center">Número de transacciones por hora del día</p>
        </div>

        {{-- Métodos de pago --}}
        <div class="card p-5">
            <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
                <span class="icon text-brand-500">payments</span>
                Ingresos por Método de Pago
            </h3>
            <div style="position:relative;height:200px">
                <canvas id="paymentChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── ROW 3: Top Clientes + Domicilios hoy ───────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Top Clientes --}}
        <div class="card p-5">
            <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
                <span class="icon text-brand-500">star</span>
                Mejores Clientes del Período
            </h3>
            <div x-show="loadingClients" class="text-center py-8">
                <span class="icon text-brand-400" style="animation:spin 1s linear infinite">progress_activity</span>
            </div>
            <div class="space-y-2">
                <template x-for="(c, i) in topClients" :key="c.nombre">
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-cream-50 border border-cream-200">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                              :class="i===0?'bg-amber-400 text-white':i===1?'bg-slate-300 text-white':'bg-cream-200 text-espresso-700'"
                              x-text="i+1"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-espresso-900 truncate" x-text="c.nombre"></p>
                            <p class="text-xs text-espresso-700/50" x-text="c.compras + ' compras'"></p>
                        </div>
                        <span class="font-display font-bold text-brand-700" x-text="c.formatted"></span>
                    </div>
                </template>
                <div x-show="!loadingClients && topClients.length===0"
                     class="text-center py-8 text-espresso-700/30">
                    <span class="icon icon-xl block mb-2">group</span>
                    <p class="text-sm">Sin datos de clientes</p>
                </div>
            </div>
        </div>

        {{-- Domicilios hoy (live) --}}
        <div class="card p-5">
            <h3 class="font-display font-bold text-espresso-900 flex items-center gap-2 mb-4">
                <span class="icon text-brand-500">local_shipping</span>
                Domicilios de Hoy
            </h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="rounded-xl p-4 text-center" style="background:#FFF3CD;border:1px solid #FBBF24">
                    <p class="font-display font-bold text-2xl text-amber-700" x-text="deliverySummary.scheduled"></p>
                    <p class="text-xs text-amber-600 font-semibold mt-1">⏳ Agendados</p>
                </div>
                <div class="rounded-xl p-4 text-center" style="background:#DBEAFE;border:1px solid #3B82F6">
                    <p class="font-display font-bold text-2xl text-blue-700" x-text="deliverySummary.dispatched"></p>
                    <p class="text-xs text-blue-600 font-semibold mt-1">🛵 En camino</p>
                </div>
                <div class="rounded-xl p-4 text-center" style="background:#DCFCE7;border:1px solid #22C55E">
                    <p class="font-display font-bold text-2xl text-green-700" x-text="deliverySummary.delivered"></p>
                    <p class="text-xs text-green-600 font-semibold mt-1">✅ Entregados</p>
                </div>
                <div class="rounded-xl p-4 text-center" style="background:#FFF8ED;border:1px solid #F97316">
                    <p class="font-display font-bold text-2xl text-brand-700"
                       x-text="'$'+Number(deliverySummary.revenue).toLocaleString('es-CO')"></p>
                    <p class="text-xs text-brand-600 font-semibold mt-1">💰 Ingresos</p>
                </div>
            </div>
            <a href="{{ route('admin.deliveries.index') }}" class="btn-primary w-full justify-center py-2.5">
                <span class="icon icon-sm">open_in_new</span>
                Ver gestión de domicilios
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
<script>
function statsApp() {
    return {
        desde: '{{ $desde }}',
        hasta: '{{ $hasta }}',
        revenueMode: 'ingresos',
        topClients: [],
        loadingClients: true,
        deliverySummary: { scheduled: 0, dispatched: 0, delivered: 0, revenue: 0 },

        charts: {},

        setRange(days) {
            const end = new Date();
            const start = new Date();
            start.setDate(start.getDate() - parseInt(days) + 1);
            this.desde = start.toISOString().slice(0,10);
            this.hasta = end.toISOString().slice(0,10);
            this.reload();
        },

        async init() {
            await Promise.all([
                this.loadRevenue(),
                this.loadTopCookies(),
                this.loadByHour(),
                this.loadByPayment(),
                this.loadTopClients(),
                this.loadDeliverySummary(),
            ]);

            this.$watch('revenueMode', () => this.updateRevenueChart());
        },

        async reload() {
            await Promise.all([
                this.loadRevenue(),
                this.loadTopCookies(),
                this.loadByHour(),
                this.loadByPayment(),
                this.loadTopClients(),
            ]);
        },

        async loadRevenue() {
            const r = await fetch(`/admin/stats/api/revenue?desde=${this.desde}&hasta=${this.hasta}`);
            const d = await r.json();
            this._revenueData = d;

            if (this.charts.revenue) {
                this.charts.revenue.destroy();
            }

            const ctx = document.getElementById('revenueChart').getContext('2d');
            this.charts.revenue = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: 'Ingresos COP',
                        data: d.ingresos,
                        borderColor: '#EA6008',
                        backgroundColor: 'rgba(234,96,8,0.1)',
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointBackgroundColor: '#EA6008',
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 10, color: '#9CA3AF' } },
                        y: { grid: { color: '#F5E5C0' }, ticks: { color: '#9CA3AF', callback: v => '$'+Number(v).toLocaleString('es-CO') } },
                    },
                }
            });
        },

        updateRevenueChart() {
            if (!this.charts.revenue || !this._revenueData) return;
            const d = this._revenueData;
            const isVentas = this.revenueMode === 'ventas';
            this.charts.revenue.data.datasets[0].data = isVentas ? d.ventas : d.ingresos;
            this.charts.revenue.data.datasets[0].label = isVentas ? 'N° Ventas' : 'Ingresos COP';
            this.charts.revenue.options.scales.y.ticks.callback = isVentas
                ? v => v
                : v => '$'+Number(v).toLocaleString('es-CO');
            this.charts.revenue.update();
        },

        async loadTopCookies() {
            const r = await fetch(`/admin/stats/api/top-cookies?desde=${this.desde}&hasta=${this.hasta}`);
            const d = await r.json();
            if (this.charts.topCookies) this.charts.topCookies.destroy();
            const ctx = document.getElementById('topCookiesChart').getContext('2d');
            const colors = ['#EA6008','#F59E0B','#7C3AED','#1D4ED8','#15803D','#0891B2','#DC2626','#854D0E'];
            this.charts.topCookies = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: d.labels,
                    datasets: [{ data: d.vendidas, backgroundColor: colors, borderWidth: 2, borderColor: '#FFFFFF' }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 }, color: '#3B1F0E' } },
                    },
                }
            });
        },

        async loadByHour() {
            const r = await fetch(`/admin/stats/api/by-hour?desde=${this.desde}&hasta=${this.hasta}`);
            const d = await r.json();
            if (this.charts.hour) this.charts.hour.destroy();
            const ctx = document.getElementById('hourChart').getContext('2d');
            const maxVentas = Math.max(...d.ventas);
            const bgColors = d.ventas.map(v => {
                const ratio = maxVentas > 0 ? v / maxVentas : 0;
                const alpha = 0.2 + ratio * 0.8;
                return `rgba(234,96,8,${alpha.toFixed(2)})`;
            });
            this.charts.hour = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: 'Ventas',
                        data: d.ventas,
                        backgroundColor: bgColors,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 12, color: '#9CA3AF', font: { size: 9 } } },
                        y: { grid: { color: '#F5E5C0' }, ticks: { color: '#9CA3AF', precision: 0 } },
                    },
                }
            });
        },

        async loadByPayment() {
            const r = await fetch(`/admin/stats/api/by-payment?desde=${this.desde}&hasta=${this.hasta}`);
            const d = await r.json();
            if (this.charts.payment) this.charts.payment.destroy();
            const ctx = document.getElementById('paymentChart').getContext('2d');
            this.charts.payment = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: 'Ingresos',
                        data: d.totales,
                        backgroundColor: ['#15803D','#7C3AED','#1D4ED8'],
                        borderRadius: 8,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: '#F5E5C0' }, ticks: { callback: v => '$'+Number(v).toLocaleString('es-CO'), color: '#9CA3AF' } },
                        y: { grid: { display: false }, ticks: { color: '#3B1F0E', font: { weight: 'bold' } } },
                    },
                }
            });
        },

        async loadTopClients() {
            this.loadingClients = true;
            const r = await fetch(`/admin/sales/top-clients?desde=${this.desde}&hasta=${this.hasta}`);
            this.topClients = await r.json();
            this.loadingClients = false;
        },

        async loadDeliverySummary() {
            const r = await fetch('/admin/stats/api/delivery-summary');
            this.deliverySummary = await r.json();
        },
    };
}
</script>
@endpush
