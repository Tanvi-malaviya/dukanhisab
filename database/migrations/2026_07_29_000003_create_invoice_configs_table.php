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
        Schema::create('invoice_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained('shops')->onDelete('cascade');

            // General
            $table->integer('starting_invoice_number')->default(1);
            $table->boolean('auto_increment')->default(true);
            $table->string('date_format', 20)->default('DD/MM/YYYY');

            // Layout
            $table->string('paper_size', 10)->default('A4'); // A4, 58mm, 80mm
            $table->string('theme_color', 10)->default('#0F766E');

            // Customer
            $table->boolean('show_customer_address')->default(true);
            $table->boolean('show_customer_gst')->default(true);

            // Products
            $table->boolean('show_hsn_code')->default(false);
            $table->boolean('show_discount')->default(true);
            $table->boolean('show_tax')->default(true);
            $table->boolean('show_sku')->default(false);

            // Tax
            $table->boolean('gst_enabled')->default(true);
            $table->boolean('round_off')->default(true);
            $table->boolean('tax_summary')->default(true);

            // Payment
            $table->boolean('show_upi_qr')->default(true);
            $table->boolean('show_bank_details')->default(false);

            // Print & Share
            $table->boolean('auto_print')->default(false);
            $table->boolean('whatsapp_share')->default(true);
            $table->boolean('pdf_download')->default(true);
            $table->boolean('email_invoice')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_configs');
    }
};
