<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FIX: La secuencia de customers comienza en 1, pero el ID 1
 * ya está reservado para "Cliente de Mostrador".
 * Al crear el primer cliente real, PostgreSQL intenta usar ID=1
 * y falla con un error de llave primaria duplicada.
 *
 * Esta migración resetea la secuencia al valor correcto.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Obtener el MAX(id) actual y asignar la secuencia al siguiente valor
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('customers', 'id'),
                GREATEST((SELECT MAX(id) FROM customers), 1) + 1
            )
        ");
    }

    public function down(): void
    {
        // No hay rollback significativo para esto
    }
};
