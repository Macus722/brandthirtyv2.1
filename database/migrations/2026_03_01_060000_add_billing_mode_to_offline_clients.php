<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('offline_clients', function (Blueprint $table) {
            $table->string('billing_mode', 20)->default('fixed')->after('status');
        });

        // Make total_package nullable for recurring retainer clients
        Schema::table('offline_clients', function (Blueprint $table) {
            $table->decimal('total_package', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('offline_clients', function (Blueprint $table) {
            $table->dropColumn('billing_mode');
        });

        Schema::table('offline_clients', function (Blueprint $table) {
            $table->decimal('total_package', 12, 2)->nullable(false)->change();
        });
    }
};
