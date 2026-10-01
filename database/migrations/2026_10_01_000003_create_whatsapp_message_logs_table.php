<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per WhatsApp message a shop sends. It is written as "queued" before the API call
     * and then follows Meta's lifecycle (sent → delivered → read, or failed) via the webhook.
     */
    public function up(): void
    {
        Schema::create('whatsapp_message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->string('event');                         // WhatsAppTemplate::EVENTS key
            $table->string('recipient_type');                // customer | supplier
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('phone', 20);
            $table->foreignId('whatsapp_template_id')->nullable()->constrained('whatsapp_templates')->onDelete('set null');
            $table->string('template_name');
            $table->string('language', 5);
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->json('payload');                         // values, document link and button suffix used
            $table->string('status')->default('queued');     // queued, sent, delivered, read, failed
            $table->string('provider_message_id')->nullable()->index();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'created_at']);
            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_logs');
    }
};
