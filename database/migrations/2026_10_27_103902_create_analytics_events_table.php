<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('shop_id')->nullable()->index();
            $table->string('event_name', 100)->index();
            $table->string('category', 50)->default('general')->index(); // lifecycle, business, subscription, whatsapp, system
            $table->json('properties')->nullable();
            $table->string('platform', 30)->nullable()->index(); // android, ios, web, api
            $table->string('app_version', 30)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['event_name', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['shop_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
