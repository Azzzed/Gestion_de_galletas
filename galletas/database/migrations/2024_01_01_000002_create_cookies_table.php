<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cookies', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            // Tags de relleno como array JSON: ["chocolate","vainilla","maní"]
            $table->jsonb('rellenos')->default('[]');
            $table->string('tamano', 30)->default('mediana')
                  ->comment('pequeña | mediana | grande');
            $table->string('imagen_path', 255)->nullable();
            $table->integer('stock')->default(0);
            // Pausar venta: si true, NO aparece en el POS
            $table->boolean('pausado')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('pausado');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookies');
    }
};
