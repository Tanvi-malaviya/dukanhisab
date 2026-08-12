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
        if (!Schema::hasColumn('sales', 'paid_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('paid_amount', 12, 2)->default(0.00)->after('grand_total');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales', 'paid_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('paid_amount');
            });
        }
    }
};
