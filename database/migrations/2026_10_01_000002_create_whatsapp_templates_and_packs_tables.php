<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WhatsApp templates are owned by the platform admin: each row mirrors a template already
     * approved in Meta WhatsApp Manager, one per (event key, language). Shop owners never edit
     * them — they only switch events on/off. Packs are the message-credit bundles shops buy.
     */
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key');                 // sale_invoice, due_reminder, ... (WhatsAppTemplate::EVENTS)
            $table->string('language', 5);         // en, gu, hi — matches the shop's app language
            $table->string('meta_template_name');  // exact template name approved in Meta
            $table->string('meta_language_code', 10); // Meta's code, e.g. en_US, en, gu, hi
            $table->text('body');                  // body text as submitted to Meta, with {{1}}, {{2}} ...
            $table->json('variables');             // ordered variable keys filling {{1}}, {{2}} ...
            $table->boolean('has_document')->default(false); // document (PDF) header
            $table->boolean('has_pay_button')->default(false); // URL button with dynamic {{1}} suffix
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['key', 'language']);
        });

        Schema::create('whatsapp_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('credits');
            $table->decimal('price', 10, 2);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_packs');
        Schema::dropIfExists('whatsapp_templates');
    }
};
