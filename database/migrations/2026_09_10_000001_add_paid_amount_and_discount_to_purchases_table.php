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
        if (!Schema::hasColumn('purchases', 'discount')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->decimal('discount', 12, 2)->default(0.00)->after('total_amount');
            });
        }

        if (!Schema::hasColumn('purchases', 'paid_amount')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->decimal('paid_amount', 12, 2)->default(0.00)->after('discount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'discount')) {
                $table->dropColumn('discount');
            }
            if (Schema::hasColumn('purchases', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
        });
    }
};
