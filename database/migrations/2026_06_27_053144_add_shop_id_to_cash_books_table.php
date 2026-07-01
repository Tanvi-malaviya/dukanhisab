<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_books', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_books', 'type')) {
                $table->string('type')->after('shop_id');               // cash_in / cash_out
            }
            if (!Schema::hasColumn('cash_books', 'amount')) {
                $table->decimal('amount', 12, 2)->after('type');
            }
            if (!Schema::hasColumn('cash_books', 'payment_method')) {
                $table->string('payment_method')->after('amount')->default('cash');
            }
            if (!Schema::hasColumn('cash_books', 'description')) {
                $table->string('description')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('cash_books', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('description');
            }
            if (!Schema::hasColumn('cash_books', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('reference_id');
            }
            if (!Schema::hasColumn('cash_books', 'transaction_date')) {
                $table->timestamp('transaction_date')->useCurrent()->after('reference_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cash_books', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'amount', 'payment_method',
                'description', 'reference_id', 'reference_type', 'transaction_date'
            ]);
        });
    }
};
