<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Pay Now" links sent with WhatsApp due reminders. The public /pay/{token} page opens the
     * customer's UPI app with the shop's UPI ID; the customer can then report the payment with its
     * UTR, and the shop owner confirms (records the payment) or rejects it.
     */
    public function up(): void
    {
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->decimal('amount', 12, 2);                 // due when the link was sent
            $table->timestamp('expires_at');
            $table->string('status')->default('open');        // open, claimed, confirmed, rejected
            $table->string('claimed_utr', 40)->nullable();
            $table->decimal('claimed_amount', 12, 2)->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();      // confirmed or rejected
            $table->timestamps();

            $table->index(['shop_id', 'status']);
        });

        Schema::table('whatsapp_shop_settings', function (Blueprint $table) {
            $table->json('last_run_result')->nullable()->after('last_run_at'); // {sent, skipped_no_credits}
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_shop_settings', function (Blueprint $table) {
            $table->dropColumn('last_run_result');
        });

        Schema::dropIfExists('payment_links');
    }
};
