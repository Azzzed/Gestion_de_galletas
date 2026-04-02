<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();

            // La palabra clave que el usuario digita en el POS
            $table->string('code', 50)->unique();

            // Descripción interna del código
            $table->string('description', 255)->nullable();

            // Tipo de descuento
            // percentage      = % de descuento sobre el total del pedido
            // fixed_amount    = valor fijo en pesos a descontar
            // free_delivery   = domicilio gratis
            // cookie_discount = descuento en galletas específicas
            $table->enum('type', ['percentage', 'fixed_amount', 'free_delivery', 'cookie_discount']);

            // Valor del descuento (% o monto fijo)
            $table->decimal('discount_value', 10, 2)->default(0);

            // IDs de galletas aplicables (para tipo cookie_discount)
            $table->jsonb('applicable_cookie_ids')->nullable();

            // Orden mínima para aplicar el código
            $table->decimal('min_order_amount', 10, 2)->default(0);

            // Límite de usos (null = ilimitado)
            $table->integer('max_uses')->nullable();

            // Cuántas veces se ha usado
            $table->integer('used_count')->default(0);

            // Validez temporal
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            // Estado
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
