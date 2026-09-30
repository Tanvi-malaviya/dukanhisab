<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the single hardcoded "Primary Bank Account" the API used to fabricate on every
 * request with real, shop-owned accounts. Existing bank/UPI cashbook entries are backfilled onto
 * a new default account per shop so no shop's bank balance appears to reset to zero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->index(['shop_id', 'is_default']);
        });

        Schema::table('cash_books', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('payment_method')->constrained('bank_accounts')->nullOnDelete();
        });

        // Backfill: one default account per shop that already has bank/upi activity, and attach
        // every existing bank/upi cashbook row to it.
        $shopIds = DB::table('cash_books')
            ->whereIn('payment_method', ['bank', 'upi'])
            ->distinct()
            ->pluck('shop_id');

        foreach ($shopIds as $shopId) {
            $accountId = DB::table('bank_accounts')->insertGetId([
                'shop_id' => $shopId,
                'name' => 'Primary Bank Account',
                'is_default' => true,
                'status' => 'active',
                'opening_balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cash_books')
                ->where('shop_id', $shopId)
                ->whereIn('payment_method', ['bank', 'upi'])
                ->update(['bank_account_id' => $accountId]);
        }
    }

    public function down(): void
    {
        Schema::table('cash_books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
        });
        Schema::dropIfExists('bank_accounts');
    }
};
