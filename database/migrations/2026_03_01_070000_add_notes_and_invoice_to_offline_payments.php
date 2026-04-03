<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('offline_payments', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('amount');
            $table->string('invoice_number', 30)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('offline_payments', function (Blueprint $table) {
            $table->dropColumn(['notes', 'invoice_number']);
        });
    }
};
