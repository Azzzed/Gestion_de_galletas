<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FIX: La secuencia de branches comienza en 1, pero el ID 1
 * ya fue insertado manualmente por la migración add_branches_auth_system.
 * Al crear la primera sucursal nueva, PostgreSQL intenta usar ID=1
 * y falla con "duplicate key value violates unique constraint".
 *
 * Esta migración resetea la secuencia al valor correcto.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('branches', 'id'),
                GREATEST((SELECT MAX(id) FROM branches), 1) + 1
            )
        ");
    }

    public function down(): void
    {
        // No hay rollback significativo
    }
};
