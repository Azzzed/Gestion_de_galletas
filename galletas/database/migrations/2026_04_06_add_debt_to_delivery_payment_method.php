<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega 'debt' (fiado) como método de pago válido en delivery_orders.
 *
 * En PostgreSQL, Laravel usa un CHECK constraint para los ENUMs.
 * Esta migración lo reemplaza para incluir el nuevo valor.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar el constraint actual de payment_method
        DB::statement("
            ALTER TABLE delivery_orders
            DROP CONSTRAINT IF EXISTS delivery_orders_payment_method_check
        ");

        // 2. Agregar el nuevo constraint incluyendo 'debt'
        DB::statement("
            ALTER TABLE delivery_orders
            ADD CONSTRAINT delivery_orders_payment_method_check
            CHECK (payment_method IN ('cash_on_delivery', 'transfer', 'debt'))
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE delivery_orders
            DROP CONSTRAINT IF EXISTS delivery_orders_payment_method_check
        ");

        DB::statement("
            ALTER TABLE delivery_orders
            ADD CONSTRAINT delivery_orders_payment_method_check
            CHECK (payment_method IN ('cash_on_delivery', 'transfer'))
        ");
    }
};
