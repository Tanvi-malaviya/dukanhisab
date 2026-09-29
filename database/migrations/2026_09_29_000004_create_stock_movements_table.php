<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A real, server-side, append-only stock audit log — every sale, purchase, return, cancel, and
 * manual adjustment writes one row here. Both the web and mobile apps previously kept their own
 * client-only version of this (browser localStorage / a local-only Isar table); this is what they
 * both now read and write through instead, so the history is the same on every device.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type'); // sale, sale_return, sale_cancel, purchase, purchase_return, purchase_cancel, adjustment
            $table->integer('quantity_change'); // positive = stock increased, negative = decreased
            $table->integer('resulting_stock');
            $table->string('reference_type')->nullable(); // sale, purchase
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['shop_id', 'product_id']);
            $table->index(['shop_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
