<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\Admin\CookieController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SaleHistoryController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\StatsDashboardController;
use Illuminate\Support\Facades\Route;

// ── POS ──────────────────────────────────────────────────────────
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('/',                   [PosController::class, 'index'])->name('index');
    Route::post('/venta',             [PosController::class, 'procesarVenta'])->name('venta.procesar');
    Route::get('/comprobante/{sale}', [PosController::class, 'comprobante'])->name('comprobante');
    Route::get('/buscar-galletas',    [PosController::class, 'buscarGalletas'])->name('galletas.buscar');
    Route::get('/buscar-clientes',    [PosController::class, 'buscarClientes'])->name('clientes.buscar');
});

// ── ADMIN ─────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', fn () => redirect()->route('admin.sales.index'))->name('dashboard');

    // ── Domicilios ────────────────────────────────────────────────
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/',                    [DeliveryController::class, 'index'])->name('index');
        Route::post('/',                   [DeliveryController::class, 'store'])->name('store');
        Route::get('/{delivery}',          [DeliveryController::class, 'show'])->name('show');
        Route::patch('/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('status');
        Route::post('/{delivery}/payment', [DeliveryController::class, 'registerPayment'])->name('payment');
        Route::patch('/{delivery}/cancel', [DeliveryController::class, 'cancel'])->name('cancel');
    });

    // ── Códigos promocionales ─────────────────────────────────────
    Route::resource('promo-codes', PromoCodeController::class);
    Route::patch('promo-codes/{promoCode}/toggle', [PromoCodeController::class, 'toggle'])->name('promo-codes.toggle');

    // ── API: validar código promo desde POS ───────────────────────
    Route::get('/api/promo-codes/validate', [PromoCodeController::class, 'validate'])->name('api.promo-codes.validate');

    // ── Estadísticas ──────────────────────────────────────────────
    Route::prefix('stats')->name('stats.')->group(function () {
        Route::get('/',                     [StatsDashboardController::class, 'index'])->name('index');
        Route::get('/api/revenue',          [StatsDashboardController::class, 'revenueTimeline'])->name('api.revenue');
        Route::get('/api/top-cookies',      [StatsDashboardController::class, 'topCookies'])->name('api.top-cookies');
        Route::get('/api/by-hour',          [StatsDashboardController::class, 'salesByHour'])->name('api.by-hour');
        Route::get('/api/by-payment',       [StatsDashboardController::class, 'byPaymentMethod'])->name('api.by-payment');
        Route::get('/api/weekly-trend',     [StatsDashboardController::class, 'weeklyTrend'])->name('api.weekly-trend');
        Route::get('/api/delivery-summary', [StatsDashboardController::class, 'deliverySummary'])->name('api.delivery-summary');
    });

    // ── Galletas (inventario) ─────────────────────────────────────
    Route::resource('cookies', CookieController::class);
    Route::patch('cookies/{cookie}/toggle-pausado', [CookieController::class, 'togglePausado'])->name('cookies.toggle-pausado');
    Route::patch('cookies/{cookie}/toggle-activo',  [CookieController::class, 'toggleActivo'])->name('cookies.toggle-activo');

    // ── Clientes ──────────────────────────────────────────────────
    Route::resource('customers', CustomerController::class);
    Route::post('customers/debts/{debt}/abono', [CustomerController::class, 'registrarAbono'])->name('customers.abono');

    // ── Historial de ventas ───────────────────────────────────────
    Route::get('sales/top-clients',       [SaleHistoryController::class, 'topClientes'])->name('sales.top-clients');
    Route::get('sales',                   [SaleHistoryController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}/detalle',    [SaleHistoryController::class, 'detalle'])->name('sales.detalle');
    Route::get('sales/{sale}/pdf',        [SaleHistoryController::class, 'exportarPdf'])->name('sales.pdf');
    Route::patch('sales/{sale}/anular',   [SaleHistoryController::class, 'anular'])->name('sales.anular');
    Route::get('sales/{sale}',            [SaleHistoryController::class, 'show'])->name('sales.show');

});

Route::get('/', fn () => redirect()->route('pos.index'));