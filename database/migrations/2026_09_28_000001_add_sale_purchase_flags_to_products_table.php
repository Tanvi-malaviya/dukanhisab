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
        Schema::table('products', function (Blueprint $table) {
            // Default true so every existing product keeps showing up in
            // both Sales and Purchase billing exactly as it did before —
            // only newly toggled-off products get excluded.
            $table->boolean('available_for_sale')->default(true)->after('purchase_price');
            $table->boolean('available_for_purchase')->default(true)->after('available_for_sale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['available_for_sale', 'available_for_purchase']);
        });
    }
};
