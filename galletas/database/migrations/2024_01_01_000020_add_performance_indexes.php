<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Índices de rendimiento para el dashboard de estadísticas.
 *
 * SIN estos índices, PostgreSQL hace full table scan en cada query,
 * lo que genera ~700ms por consulta cuando la BD está en un servidor remoto.
 * CON estos índices, las mismas queries bajan a ~10-50ms.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── sales ─────────────────────────────────────────────────
        Schema::table('sales', function (Blueprint $table) {
            // Índice compuesto más importante: filtrar por estado + fecha
            // Cubre: WHERE estado = 'completada' AND created_at >= ... AND created_at <= ...
            if (! $this->indexExists('sales', 'sales_estado_created_at_idx')) {
                $table->index(['estado', 'created_at'], 'sales_estado_created_at_idx');
            }
            // Para el GROUP BY created_at en timelines
            if (! $this->indexExists('sales', 'sales_created_at_idx')) {
                $table->index(['created_at'], 'sales_created_at_idx');
            }
            // Para filtros por customer_id + estado (historial de cliente)
            if (! $this->indexExists('sales', 'sales_customer_id_estado_idx')) {
                $table->index(['customer_id', 'estado'], 'sales_customer_id_estado_idx');
            }
        });

        // ── sale_items ────────────────────────────────────────────
        Schema::table('sale_items', function (Blueprint $table) {
            // Para los JOINs con sales en top_cookies y ventasFrecuentes
            if (! $this->indexExists('sale_items', 'sale_items_cookie_id_idx')) {
                $table->index(['cookie_id'], 'sale_items_cookie_id_idx');
            }
            if (! $this->indexExists('sale_items', 'sale_items_sale_id_idx')) {
                $table->index(['sale_id'], 'sale_items_sale_id_idx');
            }
        });

        // ── delivery_orders ───────────────────────────────────────
        Schema::table('delivery_orders', function (Blueprint $table) {
            // Para filtros de estadísticas por fecha
            if (! $this->indexExists('delivery_orders', 'deliveries_created_at_idx')) {
                $table->index(['created_at'], 'deliveries_created_at_idx');
            }
            // Para filtros por estado del pago
            if (! $this->indexExists('delivery_orders', 'deliveries_payment_status_idx')) {
                $table->index(['payment_status', 'created_at'], 'deliveries_payment_status_idx');
            }
            // Para el kanban (filtrar por status)
            if (! $this->indexExists('delivery_orders', 'deliveries_status_idx')) {
                $table->index(['status', 'created_at'], 'deliveries_status_idx');
            }
            // Para el perfil de cliente
            if (! $this->indexExists('delivery_orders', 'deliveries_customer_id_idx')) {
                $table->index(['customer_id'], 'deliveries_customer_id_idx');
            }
        });

        // ── customers ─────────────────────────────────────────────
        Schema::table('customers', function (Blueprint $table) {
            // Para la búsqueda por nombre y teléfono en el POS
            if (! $this->indexExists('customers', 'customers_nombre_idx')) {
                $table->index(['nombre'], 'customers_nombre_idx');
            }
        });

        // ── cookies ───────────────────────────────────────────────
        Schema::table('cookies', function (Blueprint $table) {
            // Para filtros disponiblePos()
            if (! $this->indexExists('cookies', 'cookies_activo_pausado_idx')) {
                $table->index(['activo', 'pausado'], 'cookies_activo_pausado_idx');
            }
        });

        // ── debts ─────────────────────────────────────────────────
        Schema::table('debts', function (Blueprint $table) {
            // Para cargar deudas de un cliente
            if (! $this->indexExists('debts', 'debts_customer_id_estado_idx')) {
                $table->index(['customer_id', 'estado'], 'debts_customer_id_estado_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            foreach (['sales_estado_created_at_idx','sales_created_at_idx','sales_customer_id_estado_idx'] as $idx) {
                try { $table->dropIndex($idx); } catch (\Throwable $e) {}
            }
        });
        Schema::table('sale_items', function (Blueprint $table) {
            foreach (['sale_items_cookie_id_idx','sale_items_sale_id_idx'] as $idx) {
                try { $table->dropIndex($idx); } catch (\Throwable $e) {}
            }
        });
        Schema::table('delivery_orders', function (Blueprint $table) {
            foreach (['deliveries_created_at_idx','deliveries_payment_status_idx','deliveries_status_idx','deliveries_customer_id_idx'] as $idx) {
                try { $table->dropIndex($idx); } catch (\Throwable $e) {}
            }
        });
        Schema::table('customers',  function (Blueprint $table) { try { $table->dropIndex('customers_nombre_idx'); } catch (\Throwable $e) {} });
        Schema::table('cookies',    function (Blueprint $table) { try { $table->dropIndex('cookies_activo_pausado_idx'); } catch (\Throwable $e) {} });
        Schema::table('debts',      function (Blueprint $table) { try { $table->dropIndex('debts_customer_id_estado_idx'); } catch (\Throwable $e) {} });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("
                SELECT indexname FROM pg_indexes
                WHERE tablename = ? AND indexname = ?
            ", [$table, $indexName]);
            return count($indexes) > 0;
        } catch (\Throwable $e) {
            // SQLite fallback
            return false;
        }
    }
};
