<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Network Activity Log
     *
     * Tracks every significant HQ action — module toggles, sync-all broadcasts,
     * God-Mode logins — for the Super Admin Live Feed on the Command Center.
     */
    public function up(): void
    {
        Schema::create('network_activities', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50);
            $table->string('site_key', 30)->default('all');
            $table->string('site_name', 100)->default('All Sites');
            $table->string('actor', 100)->default('System');
            $table->string('status', 20)->default('success');
            $table->text('meta')->nullable();         // stored as JSON string, cast in model
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('site_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_activities');
    }
};
