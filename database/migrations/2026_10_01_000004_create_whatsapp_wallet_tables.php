<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-shop WhatsApp message credits. The wallet holds the balance; every change is a ledger
     * row (purchase, debit per message, refund on failure, admin adjustment). Pack purchases are
     * recorded as pending when the Razorpay order is created so the price and credits come from
     * our side, never from the client.
     */
    public function up(): void
    {
        Schema::create('whatsapp_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained('shops')->onDelete('cascade');
            $table->integer('balance')->default(0);
            $table->timestamps();
        });

        Schema::create('whatsapp_pack_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('whatsapp_pack_id')->nullable()->constrained('whatsapp_packs')->onDelete('set null');
            $table->string('pack_name');
            $table->unsignedInteger('credits');
            $table->decimal('amount', 10, 2);
            $table->string('razorpay_order_id')->unique();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('status')->default('pending'); // pending, paid
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_credit_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->string('type');               // purchase, debit, refund, adjustment
            $table->integer('credits');           // signed: + adds, - uses
            $table->integer('balance_after');
            $table->foreignId('whatsapp_pack_purchase_id')->nullable()->constrained('whatsapp_pack_purchases')->onDelete('set null');
            $table->foreignId('whatsapp_message_log_id')->nullable()->constrained('whatsapp_message_logs')->onDelete('set null');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_credit_ledger');
        Schema::dropIfExists('whatsapp_pack_purchases');
        Schema::dropIfExists('whatsapp_wallets');
    }
};
