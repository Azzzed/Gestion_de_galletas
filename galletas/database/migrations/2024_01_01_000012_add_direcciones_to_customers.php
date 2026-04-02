<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Solo agregar si no existe
            if (! Schema::hasColumn('customers', 'direcciones')) {
                $table->json('direcciones')->nullable()->after('telefono');
                // Cada entrada: { "direccion": "Cra 15 #45", "barrio": "El Prado", "principal": true }
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'direcciones')) {
                $table->dropColumn('direcciones');
            }
        });
    }
};
