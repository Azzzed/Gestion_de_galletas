<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\Admin\CookieController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SaleHistoryController;
use App\Models\Debt;
use Illuminate\Support\Facades\Route;

// ── POS ──────────────────────────────────────────────────────────
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('/',                    [PosController::class, 'index'])->name('index');
    Route::post('/venta',              [PosController::class, 'procesarVenta'])->name('venta.procesar');
    Route::get('/comprobante/{sale}',  [PosController::class, 'comprobante'])->name('comprobante');
    Route::get('/buscar-galletas',     [PosController::class, 'buscarGalletas'])->name('galletas.buscar');
    Route::get('/buscar-clientes',     [PosController::class, 'buscarClientes'])->name('clientes.buscar');
});

// ── ADMIN ────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', fn () => redirect()->route('admin.sales.index'))->name('dashboard');

    // Galletas (inventario)
    Route::resource('cookies', CookieController::class);
    Route::patch('cookies/{cookie}/toggle-pausado', [CookieController::class, 'togglePausado'])->name('cookies.toggle-pausado');
    Route::patch('cookies/{cookie}/toggle-activo',  [CookieController::class, 'toggleActivo'])->name('cookies.toggle-activo');

    // Clientes
    Route::resource('customers', CustomerController::class);
    Route::post('customers/debts/{debt}/abono', [CustomerController::class, 'registrarAbono'])->name('customers.abono');

    // Historial de ventas
    Route::get('sales',                          [SaleHistoryController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}',                   [SaleHistoryController::class, 'show'])->name('sales.show');
    Route::get('sales/{sale}/detalle',           [SaleHistoryController::class, 'detalle'])->name('sales.detalle');
    Route::get('sales/{sale}/pdf',               [SaleHistoryController::class, 'exportarPdf'])->name('sales.pdf');
    Route::patch('sales/{sale}/anular',          [SaleHistoryController::class, 'anular'])->name('sales.anular');
});

Route::get('/', fn () => redirect()->route('pos.index'));
