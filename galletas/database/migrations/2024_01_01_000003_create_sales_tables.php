<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // ── sales ────────────────────────────────────────────────
    if (! Schema::hasTable('sales')) {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                  ->default(1)
                  ->constrained('customers')
                  ->restrictOnDelete();
            $table->string('numero_factura', 30)->unique()->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('metodo_pago', 30)->default('efectivo');
            $table->jsonb('metodos_pago')->nullable();
            $table->string('estado', 20)->default('completada');
            $table->boolean('tiene_deuda')->default(false);
            $table->string('notas', 500)->nullable();
            $table->unsignedBigInteger('cajero_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('estado');
            $table->index('created_at');
        });
    }

    // ── sale_items ───────────────────────────────────────────
    if (! Schema::hasTable('sale_items')) {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();
            $table->foreignId('cookie_id')
                  ->constrained('cookies')
                  ->restrictOnDelete();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento_item', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->string('notas_item', 255)->nullable();
            $table->timestamps();

            $table->index('sale_id');
            $table->index('cookie_id');
        });
    }

    // ── debts ────────────────────────────────────────────────
    if (! Schema::hasTable('debts')) {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->restrictOnDelete();
            $table->foreignId('sale_id')
                  ->nullable()
                  ->constrained('sales')
                  ->nullOnDelete();
            $table->decimal('monto_original', 12, 2);
            $table->decimal('monto_pendiente', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->string('estado', 20)->default('pendiente');
            $table->date('fecha_vencimiento')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('estado');
        });
    }
}

    public function down(): void
    {
        Schema::dropIfExists('debts');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
