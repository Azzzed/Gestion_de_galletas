<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\Admin\CookieController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SaleHistoryController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\StatsDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════
// AUTH — Login / Logout
// ══════════════════════════════════════════════════════════════

Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::get('/superadmin/login',  [LoginController::class, 'showSuperAdminLogin'])->name('superadmin.login');
Route::post('/superadmin/login', [LoginController::class, 'superAdminLogin'])->name('superadmin.login.submit');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ══════════════════════════════════════════════════════════════
// SUPER ADMIN — Panel del sistema
// ══════════════════════════════════════════════════════════════
Route::prefix('superadmin')->name('superadmin.')->middleware('role:superadmin')->group(function () {

    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/',            [SuperAdminController::class, 'branches'])->name('index');
        Route::get('/create',      [SuperAdminController::class, 'branchCreate'])->name('create');
        Route::post('/',           [SuperAdminController::class, 'branchStore'])->name('store');
        Route::get('/{branch}',    [SuperAdminController::class, 'branchEdit'])->name('edit');
        Route::put('/{branch}',    [SuperAdminController::class, 'branchUpdate'])->name('update');
        Route::delete('/{branch}', [SuperAdminController::class, 'branchDestroy'])->name('destroy');
    });
    Route::get('/branches', [SuperAdminController::class, 'branches'])->name('branches');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                [SuperAdminController::class, 'users'])->name('index');
        Route::get('/create',          [SuperAdminController::class, 'userCreate'])->name('create');
        Route::post('/',               [SuperAdminController::class, 'userStore'])->name('store');
        Route::get('/{user}',          [SuperAdminController::class, 'userEdit'])->name('edit');
        Route::put('/{user}',          [SuperAdminController::class, 'userUpdate'])->name('update');
        Route::delete('/{user}',       [SuperAdminController::class, 'userDestroy'])->name('destroy');
        Route::patch('/{user}/toggle', [SuperAdminController::class, 'userToggle'])->name('toggle');
    });
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');

    Route::get('/ventas',     [SuperAdminController::class, 'ventas'])->name('ventas');
    Route::get('/domicilios', [SuperAdminController::class, 'domicilios'])->name('domicilios');
    Route::get('/inventario', [SuperAdminController::class, 'inventario'])->name('inventario');
});

// ══════════════════════════════════════════════════════════════
// POS — Acceso: vendedor + admin
// ══════════════════════════════════════════════════════════════
Route::prefix('pos')->name('pos.')->middleware('role:vendedor')->group(function () {
    Route::get('/',                   [PosController::class, 'index'])->name('index');
    Route::post('/venta',             [PosController::class, 'procesarVenta'])->name('venta.procesar');
    Route::get('/comprobante/{sale}', [PosController::class, 'comprobante'])->name('comprobante');
    Route::get('/buscar-galletas',    [PosController::class, 'buscarGalletas'])->name('galletas.buscar');
    Route::get('/buscar-clientes',    [PosController::class, 'buscarClientes'])->name('clientes.buscar');
});

// ══════════════════════════════════════════════════════════════
// ADMIN — Panel de sucursal
// ══════════════════════════════════════════════════════════════
Route::prefix('admin')->name('admin.')->middleware('role:vendedor')->group(function () {

    Route::get('/', fn () => redirect()->route('admin.sales.index'))->name('dashboard');

    // ── Domicilios ─────────────────────────────────────── vendedor + admin
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/',                    [DeliveryController::class, 'index'])->name('index');
        Route::post('/',                   [DeliveryController::class, 'store'])->name('store');
        Route::get('/{delivery}',          [DeliveryController::class, 'show'])->name('show');
        Route::patch('/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('status');
        Route::post('/{delivery}/payment', [DeliveryController::class, 'registerPayment'])->name('payment');
        Route::patch('/{delivery}/cancel', [DeliveryController::class, 'cancel'])->name('cancel');
    });

    // ── Clientes ───────────────────────────────────────── vendedor + admin
    Route::resource('customers', CustomerController::class);
    Route::post('customers/debts/{debt}/abono', [CustomerController::class, 'registrarAbono'])->name('customers.abono');

    // ── Historial de ventas ────────────────────────────── vendedor (solo ver + PDF)
    // ✅ Las rutas estáticas SIEMPRE antes de las dinámicas {sale}
    Route::get('sales/top-clients',    [SaleHistoryController::class, 'topClientes'])->name('sales.top-clients');
    Route::get('sales',                [SaleHistoryController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}/detalle', [SaleHistoryController::class, 'detalle'])->name('sales.detalle');
    Route::get('sales/{sale}/pdf',     [SaleHistoryController::class, 'exportarPdf'])->name('sales.pdf');
    Route::get('sales/{sale}',         [SaleHistoryController::class, 'show'])->name('sales.show');

    // Anular y eliminar: SOLO admin
    Route::patch('sales/{sale}/anular', [SaleHistoryController::class, 'anular'])
        ->name('sales.anular')
        ->middleware('role:admin');
    Route::delete('sales/{sale}', [SaleHistoryController::class, 'destroy'])
        ->name('sales.destroy')
        ->middleware('role:admin');

    // ── Galletas ───────────────────────────────────────── vendedor solo ver
    // ✅ FIX: rutas estáticas (index, create, store) ANTES de las dinámicas ({cookie})
    Route::get('cookies',  [CookieController::class, 'index'])->name('cookies.index');

    Route::middleware('role:admin')->group(function () {
        // ✅ create y store VAN ANTES que cookies/{cookie}
        Route::get('cookies/create',  [CookieController::class, 'create'])->name('cookies.create');
        Route::post('cookies',        [CookieController::class, 'store'])->name('cookies.store');
    });

    // Rutas con parámetro {cookie} — después de las estáticas
    Route::get('cookies/{cookie}', [CookieController::class, 'show'])->name('cookies.show');

    Route::middleware('role:admin')->group(function () {
        Route::get('cookies/{cookie}/edit',    [CookieController::class, 'edit'])->name('cookies.edit');
        Route::put('cookies/{cookie}',         [CookieController::class, 'update'])->name('cookies.update');
        Route::delete('cookies/{cookie}',      [CookieController::class, 'destroy'])->name('cookies.destroy');
        Route::patch('cookies/{cookie}/toggle-pausado', [CookieController::class, 'togglePausado'])->name('cookies.toggle-pausado');
        Route::patch('cookies/{cookie}/toggle-activo',  [CookieController::class, 'toggleActivo'])->name('cookies.toggle-activo');
    });

    // ── Estadísticas ───────────────────────────────────── SOLO admin
    Route::middleware('role:admin')->prefix('stats')->name('stats.')->group(function () {
        Route::get('/',                     [StatsDashboardController::class, 'index'])->name('index');
        Route::get('/api/revenue',          [StatsDashboardController::class, 'revenueTimeline'])->name('api.revenue');
        Route::get('/api/top-cookies',      [StatsDashboardController::class, 'topCookies'])->name('api.top-cookies');
        Route::get('/api/by-hour',          [StatsDashboardController::class, 'salesByHour'])->name('api.by-hour');
        Route::get('/api/by-payment',       [StatsDashboardController::class, 'byPaymentMethod'])->name('api.by-payment');
        Route::get('/api/weekly-trend',     [StatsDashboardController::class, 'weeklyTrend'])->name('api.weekly-trend');
        Route::get('/api/delivery-summary', [StatsDashboardController::class, 'deliverySummary'])->name('api.delivery-summary');
    });

    // ── Códigos promocionales ─────────────────────────── SOLO admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('promo-codes', PromoCodeController::class);
        Route::patch('promo-codes/{promoCode}/toggle', [PromoCodeController::class, 'toggle'])->name('promo-codes.toggle');
        Route::get('/api/promo-codes/validate', [PromoCodeController::class, 'validate'])->name('api.promo-codes.validate');
    });
});

// ── Raíz ─────────────────────────────────────────────────────
Route::get('/', fn () => auth()->check()
    ? redirect()->route(auth()->user()->isSuperAdmin() ? 'superadmin.dashboard' : 'pos.index')
    : redirect()->route('login')
);