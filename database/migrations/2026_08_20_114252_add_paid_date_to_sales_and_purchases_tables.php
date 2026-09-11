<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'paid_date')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->timestamp('paid_date')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('purchases', 'paid_date')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->timestamp('paid_date')->nullable()->after('status');
            });
        }

        // Backfill Sales
        DB::table('sales')->where('status', 'Completed')->where('payment_type', '!=', 'Credit')->update([
            'paid_date' => DB::raw('sale_date')
        ]);
        DB::table('sales')->where('status', 'Completed')->where('payment_type', 'Credit')->update([
            'paid_date' => DB::raw('updated_at')
        ]);

        // Backfill Purchases
        DB::table('purchases')->where('status', 'Completed')->where('payment_type', '!=', 'Credit')->update([
            'paid_date' => DB::raw('purchase_date')
        ]);
        DB::table('purchases')->where('status', 'Completed')->where('payment_type', 'Credit')->update([
            'paid_date' => DB::raw('updated_at')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales', 'paid_date')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('paid_date');
            });
        }

        if (Schema::hasColumn('purchases', 'paid_date')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('paid_date');
            });
        }
    }
};
