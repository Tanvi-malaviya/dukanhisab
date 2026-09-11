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
        Schema::create('cash_register_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('closing_date');
            $table->decimal('opening_balance', 12, 2)->default(0.00);
            $table->decimal('cash_in', 12, 2)->default(0.00);
            $table->decimal('cash_out', 12, 2)->default(0.00);
            $table->decimal('expected_cash', 12, 2)->default(0.00);
            $table->decimal('actual_cash', 12, 2)->default(0.00);
            $table->decimal('difference', 12, 2)->default(0.00);
            $table->json('denominations')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'closing_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_closures');
    }
};
