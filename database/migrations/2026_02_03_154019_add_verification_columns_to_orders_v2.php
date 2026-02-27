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
                $table->foreign('staff_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'is_payment_verified')) {
                $table->dropColumn('is_payment_verified');
            }
            if (Schema::hasColumn('orders', 'is_content_verified')) {
                $table->dropColumn('is_content_verified');
            }
            if (Schema::hasColumn('orders', 'staff_id')) {
                $table->dropForeign(['staff_id']);
                $table->dropColumn('staff_id');
            }
        });
    }
};
