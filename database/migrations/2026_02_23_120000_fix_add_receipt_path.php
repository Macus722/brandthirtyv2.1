<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Use raw SQL to avoid schema detection issues
        try {
            DB::statement('ALTER TABLE orders ADD COLUMN receipt_path VARCHAR(255) NULL');
        } catch (\Exception $e) {
            // Column might already exist, ignore
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
        });
    }
};
