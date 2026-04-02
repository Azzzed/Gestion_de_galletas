<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();

            // Cliente (puede no estar registrado)
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_phone', 20)->nullable();

            // Dirección
            $table->text('delivery_address');
            $table->string('delivery_neighborhood', 100)->nullable(); // barrio

            // Costo de envío
            // free = el negocio asume el envío
            // additional = se cobra al cliente encima del pedido
            // business = el negocio asume, sin cobro
            $table->enum('delivery_cost_type', ['free', 'additional', 'business'])->default('additional');
            $table->decimal('delivery_cost', 10, 2)->default(0);

            // Items del pedido (JSON)
            $table->jsonb('items'); // [{cookie_id, nombre, cantidad, precio_unitario, subtotal}]

            // Totales
            $table->decimal('subtotal', 12, 2)->default(0);    // subtotal galletas
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);        // total con envío y descuentos

            // Pago
            // cash_on_delivery = paga al llegar (efectivo o transferencia)
            // transfer = ya pagó por transferencia antes del envío
            $table->enum('payment_method', ['cash_on_delivery', 'transfer'])->default('cash_on_delivery');

            // Estado del pago
            // paid = pagado completo
            // pending = pendiente de pago
            // partial = abono parcial
            $table->enum('payment_status', ['paid', 'pending', 'partial'])->default('pending');
            $table->decimal('paid_amount', 10, 2)->default(0);

            // Estado del domicilio
            // scheduled = agendado (recibido, no despachado)
            // dispatched = en camino
            // delivered = entregado
            // cancelled = cancelado
            $table->enum('status', ['scheduled', 'dispatched', 'delivered', 'cancelled'])->default('scheduled');

            // Código promocional aplicado
            $table->string('promo_code', 50)->nullable();

            // Notas
            $table->text('notes')->nullable();

            // Fecha programada (si es para más tarde)
            $table->timestamp('scheduled_at')->nullable();

            // Quién registró el pedido
            $table->unsignedBigInteger('cajero_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
