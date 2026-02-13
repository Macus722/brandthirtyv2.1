<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'is_payment_verified')) {
                $table->boolean('is_payment_verified')->default(false)->after('current_step');
            }
            if (!Schema::hasColumn('orders', 'is_content_verified')) {
                $table->boolean('is_content_verified')->default(false)->after('is_payment_verified');
            }
            if (!Schema::hasColumn('orders', 'staff_id')) {
                // Using unsignedBigInteger directly if foreignId causes issues, but foreignId is standard.
                // Assuming users table uses id (bigIncrements).
                $table->after('user_id', function ($table) {
                    $table->unsignedBigInteger('staff_id')->nullable();
                    $table->foreign('staff_id')->references('id')->on('users');
                });
            }
        });
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
