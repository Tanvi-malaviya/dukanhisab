<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Shops no longer create their own bank accounts: each shop has exactly one, its default account,
 * and every bank/UPI entry belongs to it. Fold any extra accounts a shop already created into that
 * default — their cash-book entries move over and their opening balances are added on — so every
 * balance stays the same, then remove the extra accounts.
 */
return new class extends Migration
{
    public function up(): void
    {
        $shopIds = DB::table('bank_accounts')->distinct()->pluck('shop_id');

        foreach ($shopIds as $shopId) {
            DB::transaction(function () use ($shopId) {
                $accounts = DB::table('bank_accounts')->where('shop_id', $shopId)
                    ->orderByDesc('is_default')->orderBy('id')->get();

                $default = $accounts->first();
                $extras = $accounts->slice(1);

                if ($extras->isNotEmpty()) {
                    $extraIds = $extras->pluck('id')->all();

                    DB::table('cash_books')->whereIn('bank_account_id', $extraIds)
                        ->update(['bank_account_id' => $default->id]);

                    DB::table('bank_accounts')->where('id', $default->id)->update([
                        'opening_balance' => round((float) $default->opening_balance + $extras->sum(fn ($a) => (float) $a->opening_balance), 2),
                    ]);

                    DB::table('bank_accounts')->whereIn('id', $extraIds)->delete();
                }

                DB::table('bank_accounts')->where('id', $default->id)
                    ->update(['is_default' => true, 'status' => 'active']);
            });
        }
    }

    public function down(): void
    {
        // The merge cannot be split back into the original accounts.
    }
};
