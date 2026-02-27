<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run each statement separately so one failure doesn't abort the rest (e.g. PostgreSQL).
     */
    public $withinTransaction = false;

    /**
     * Add indexes for admin panel list/count queries (status, staff_id, created_at).
     * Uses IF NOT EXISTS so migration is safe to re-run.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $prefix = Schema::getConnection()->getTablePrefix();
        $table = $prefix . 'orders';

        if ($driver === 'pgsql') {
            DB::statement("CREATE INDEX IF NOT EXISTS orders_status_index ON {$table} (status)");
            DB::statement("CREATE INDEX IF NOT EXISTS orders_staff_id_index ON {$table} (staff_id)");
            DB::statement("CREATE INDEX IF NOT EXISTS orders_created_at_index ON {$table} (created_at)");
            return;
        }

        if ($driver === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name IN ('orders_status_index', 'orders_staff_id_index', 'orders_created_at_index')"))
                ->pluck('Key_name')->unique();
            if (!$indexes->contains('orders_status_index')) {
                DB::statement("CREATE INDEX orders_status_index ON {$table} (status)");
            }
            if (!$indexes->contains('orders_staff_id_index')) {
                DB::statement("CREATE INDEX orders_staff_id_index ON {$table} (staff_id)");
            }
            if (!$indexes->contains('orders_created_at_index')) {
                DB::statement("CREATE INDEX orders_created_at_index ON {$table} (created_at)");
            }
            return;
        }

        // SQLite / fallback: use Schema
        Schema::table('orders', function ($t) {
            $t->index('status');
            $t->index('staff_id');
            $t->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $prefix = Schema::getConnection()->getTablePrefix();
        $tableName = $prefix . 'orders';

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS orders_status_index');
            DB::statement('DROP INDEX IF EXISTS orders_staff_id_index');
            DB::statement('DROP INDEX IF EXISTS orders_created_at_index');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE {$tableName} DROP INDEX orders_status_index");
            DB::statement("ALTER TABLE {$tableName} DROP INDEX orders_staff_id_index");
            DB::statement("ALTER TABLE {$tableName} DROP INDEX orders_created_at_index");
            return;
        }

        Schema::table('orders', function ($t) {
            $t->dropIndex(['status']);
            $t->dropIndex(['staff_id']);
            $t->dropIndex(['created_at']);
        });
    }
};
