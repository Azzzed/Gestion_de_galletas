<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'deleted_at')) {
                $table->softDeletes();
            }

            // También agregar columnas que faltan según el modelo
            if (!Schema::hasColumn('sales', 'numero_factura')) {
                $table->string('numero_factura', 30)->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('sales', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('sales', 'descuento')) {
                $table->decimal('descuento', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('sales', 'descuento_porcentaje')) {
                $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('sales', 'estado')) {
                $table->string('estado', 20)->default('completada');
            }
            if (!Schema::hasColumn('sales', 'cajero_id')) {
                $table->unsignedBigInteger('cajero_id')->nullable();
            }
            if (!Schema::hasColumn('sales', 'notas')) {
                $table->string('notas', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};