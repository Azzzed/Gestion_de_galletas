<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * MIGRACIÓN PRINCIPAL: Sistema de Sucursales + Autenticación
 *
 * 1. Tabla `branches`  — sucursales
 * 2. Tabla `users`     — agrega role + branch_id + activo
 * 3. branch_id en      — customers, cookies, sales, delivery_orders, promo_codes
 * 4. Data inicial      — Sucursal Principal + superadmin
 */
return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════
        // 1. BRANCHES
        // ══════════════════════════════════════════════════════════
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('slug', 80)->unique();         // para URLs / identificación
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('color', 20)->default('#ea6008'); // color de identificación
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════════════════
        // 2. USERS — agregar rol, sucursal y estado
        // ══════════════════════════════════════════════════════════
        Schema::table('users', function (Blueprint $table) {
            // role: superadmin = dueño del sistema, admin = admin de sucursal, vendedor = cajero
            $table->enum('role', ['superadmin', 'admin', 'vendedor'])->default('vendedor')->after('email');
            $table->foreignId('branch_id')->nullable()->after('role')
                  ->constrained('branches')->nullOnDelete();
            $table->boolean('activo')->default(true)->after('branch_id');
        });

        // ══════════════════════════════════════════════════════════
        // 3. BRANCH_ID EN TABLAS OPERATIVAS
        // ══════════════════════════════════════════════════════════

        // customers
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branches')->nullOnDelete();
            $table->index('branch_id');
        });

        // cookies
        Schema::table('cookies', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branches')->nullOnDelete();
            $table->index('branch_id');
        });

        // sales
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branches')->nullOnDelete();
            $table->index('branch_id');
        });

        // delivery_orders
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branches')->nullOnDelete();
            $table->index('branch_id');
        });

        // promo_codes
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branches')->nullOnDelete();
            $table->index('branch_id');
        });

        // ══════════════════════════════════════════════════════════
        // 4. DATA INICIAL
        // ══════════════════════════════════════════════════════════

        // Sucursal principal (los datos existentes son de esta sucursal)
        DB::table('branches')->insert([
            'id'         => 1,
            'nombre'     => 'Sucursal Principal',
            'slug'       => 'principal',
            'activo'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Asignar todos los datos existentes a la sucursal 1
        DB::table('customers')->update(['branch_id' => 1]);
        DB::table('cookies')->update(['branch_id' => 1]);
        DB::table('sales')->update(['branch_id' => 1]);
        DB::table('delivery_orders')->update(['branch_id' => 1]);
        DB::table('promo_codes')->update(['branch_id' => 1]);

        // Superadmin del sistema (sin sucursal)
        DB::table('users')->insert([
            'name'       => 'Super Admin',
            'email'      => 'superadmin@capycrunch.com',
            'password'   => Hash::make('superadmin2025!'),   // ← cambiar en producción
            'role'       => 'superadmin',
            'branch_id'  => null,
            'activo'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Admin de la sucursal principal
        DB::table('users')->insert([
            'name'       => 'Admin Principal',
            'email'      => 'admin@capycrunch.com',
            'password'   => Hash::make('admin2025!'),        // ← cambiar en producción
            'role'       => 'admin',
            'branch_id'  => 1,
            'activo'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Actualizar usuarios existentes que ya estén en la BD
        DB::table('users')
            ->whereNull('branch_id')
            ->where('role', '!=', 'superadmin')
            ->update(['branch_id' => 1, 'role' => 'admin']);
    }

    public function down(): void
    {
        // Revertir en orden inverso de FK
        Schema::table('promo_codes',     fn ($t) => $t->dropColumn('branch_id'));
        Schema::table('delivery_orders', fn ($t) => $t->dropColumn('branch_id'));
        Schema::table('sales',           fn ($t) => $t->dropColumn('branch_id'));
        Schema::table('cookies',         fn ($t) => $t->dropColumn('branch_id'));
        Schema::table('customers',       fn ($t) => $t->dropColumn('branch_id'));
        Schema::table('users',           fn ($t) => $t->dropColumn(['role', 'branch_id', 'activo']));
        Schema::dropIfExists('branches');
    }
};
