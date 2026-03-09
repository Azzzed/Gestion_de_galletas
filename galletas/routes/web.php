<?php

use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ── POS — Vista principal de ventas ──
Route::get('/', [SaleController::class, 'index'])->name('ventas.index');
Route::post('/ventas', [SaleController::class, 'store'])->name('ventas.store');

// ── Inventario CRUD ──
Route::resource('inventario', StockController::class)->except(['show']);

// ── Dashboard de Cierre ──
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');