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
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_sale_number_unique');
            $table->unique(['shop_id', 'sale_number'], 'sales_shop_id_sale_number_unique');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique('purchases_purchase_number_unique');
            $table->unique(['shop_id', 'purchase_number'], 'purchases_shop_id_purchase_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_shop_id_sale_number_unique');
            $table->unique('sale_number', 'sales_sale_number_unique');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique('purchases_shop_id_purchase_number_unique');
            $table->unique('purchase_number', 'purchases_purchase_number_unique');
        });
    }
};
