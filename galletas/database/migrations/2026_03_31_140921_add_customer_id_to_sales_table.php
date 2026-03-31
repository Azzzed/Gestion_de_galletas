<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Agregar customer_id si no existe
            if (!Schema::hasColumn('sales', 'customer_id')) {
                $table->foreignId('customer_id')
                      ->default(1)
                      ->after('id')
                      ->constrained('customers')
                      ->restrictOnDelete();
            }

            // Agregar tiene_deuda si no existe
            if (!Schema::hasColumn('sales', 'tiene_deuda')) {
                $table->boolean('tiene_deuda')->default(false)->after('estado');
            }

            // Agregar metodo_pago si no existe
            if (!Schema::hasColumn('sales', 'metodo_pago')) {
                $table->string('metodo_pago', 30)->default('efectivo')->after('total');
            }

            // Agregar metodos_pago JSONB si no existe
            if (!Schema::hasColumn('sales', 'metodos_pago')) {
                $table->jsonb('metodos_pago')->nullable()->after('metodo_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'tiene_deuda', 'metodo_pago', 'metodos_pago']);
        });
    }
};