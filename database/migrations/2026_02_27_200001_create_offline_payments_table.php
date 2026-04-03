<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('offline_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offline_client_id')->constrained('offline_clients')->cascadeOnDelete();
            $table->smallInteger('period_month');
            $table->smallInteger('period_year');
            $table->decimal('amount', 12, 2);
            $table->datetime('paid_at');
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['offline_client_id', 'period_month', 'period_year'], 'unique_payment_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_payments');
    }
};
