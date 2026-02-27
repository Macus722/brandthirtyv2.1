<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'is_payment_verified')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_payment_verified')->default(false);
            });
        }
        if (!Schema::hasColumn('orders', 'is_content_verified')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_content_verified')->default(false);
            });
        }
        if (!Schema::hasColumn('orders', 'staff_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('staff_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_payment_verified', 'is_content_verified', 'staff_id']);
        });
    }
};
