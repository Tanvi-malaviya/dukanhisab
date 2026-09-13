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
        if (!Schema::hasColumn('purchase_items', 'discount')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->decimal('discount', 12, 2)->default(0.00)->after('purchase_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchase_items', 'discount')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropColumn('discount');
            });
        }
    }
};
