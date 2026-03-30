<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debtor_id')->constrained('debtors')->cascadeOnDelete();
            $table->json('items');                  // [{product_id, quantity, unit_price, subtotal}]
            $table->unsignedInteger('total');
            $table->unsignedInteger('paid_amount')->default(0);
            $table->string('status')->default('pending'); // pending | partial | paid
            $table->string('sale_type');                  // individual | bowl
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
