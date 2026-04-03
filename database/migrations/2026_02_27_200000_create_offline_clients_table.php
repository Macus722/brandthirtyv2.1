<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('offline_clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('pic_name');
            $table->string('pic_phone');
            $table->string('pic_email')->nullable();
            $table->decimal('total_package', 12, 2);
            $table->decimal('monthly_payment', 12, 2);
            $table->date('contract_start');
            $table->smallInteger('due_day')->default(1);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_clients');
    }
};
