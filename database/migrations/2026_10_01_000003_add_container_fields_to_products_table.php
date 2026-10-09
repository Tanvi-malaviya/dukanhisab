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
            // Returnable container sent with each unit of this product (e.g. 1 Tub per 5L ice cream).
            // Null = no container, which is every existing product.
            $table->foreignId('container_type_id')->nullable()->after('available_for_purchase')
                ->constrained('container_types')->nullOnDelete();
            $table->unsignedInteger('containers_per_unit')->default(1)->after('container_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('container_type_id');
            $table->dropColumn('containers_per_unit');
        });
    }
};
