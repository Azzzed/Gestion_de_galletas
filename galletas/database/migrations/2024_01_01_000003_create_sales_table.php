<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->enum('sale_type', ['individual', 'bowl']); // Tipo de venta
            $table->unsignedInteger('total');                    // Total en COP
            $table->enum('payment_method', [
                'efectivo',
                'nequi',
                'daviplata'
            ]);
            $table->text('notes')->nullable();
            $table->timestamps();                               // created_at = hora de venta
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};