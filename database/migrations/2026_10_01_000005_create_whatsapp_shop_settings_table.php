<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What each shop owner chose to send: one row per (shop, event). Instant events only use
     * `enabled`; scheduled ones (due reminders) also keep their weekdays, time and minimum due.
     * Customers and suppliers get a per-person opt-out and the time of their last reminder.
     */
    public function up(): void
    {
        Schema::create('whatsapp_shop_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->string('event');
            $table->boolean('enabled')->default(false);
            $table->json('schedule_days')->nullable();   // e.g. ["mon","thu"], at most 2
            $table->string('schedule_time', 5)->nullable(); // HH:MM, shop local time
            $table->decimal('min_due_amount', 12, 2)->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'event']);
        });

        foreach (['customers', 'suppliers'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('whatsapp_opt_out')->default(false);
                $t->timestamp('last_whatsapp_reminder_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['customers', 'suppliers'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['whatsapp_opt_out', 'last_whatsapp_reminder_at']);
            });
        }

        Schema::dropIfExists('whatsapp_shop_settings');
    }
};
