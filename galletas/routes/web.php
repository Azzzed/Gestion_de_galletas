<?php

use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtorController;
use Illuminate\Support\Facades\Route;

// ══ POS — Vista principal de ventas ══
Route::get('/', [SaleController::class, 'index'])->name('ventas.index');
Route::post('/ventas', [SaleController::class, 'store'])->name('ventas.store');

// ══ Inventario CRUD ══
Route::resource('inventario', StockController::class)->except(['show']);

// ══ Dashboard de Cierre ══
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// ══ Deudores ══
Route::resource('deudores', DebtorController::class);

// ══ API Deudores (para AJAX) ══
Route::get('/api/deudores', [DebtorController::class, 'apiList'])->name('api.deudores.list');
Route::post('/api/deudores/debt', [DebtorController::class, 'createDebt'])->name('api.deudores.debt');
Route::post('/api/deudas/{id}/payment', [DebtorController::class, 'registerPayment'])->name('api.deudas.payment');
