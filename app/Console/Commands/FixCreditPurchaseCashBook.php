<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CashBook;
use App\Models\Purchase;

class FixCreditPurchaseCashBook extends Command
{
    protected $signature = 'cashbook:fix-credit-purchases';
    protected $description = 'Remove incorrect CashBook cash_out entries created for Credit-type purchases';

    public function handle()
    {
        // Find all cash_out CashBook entries linked to purchases with payment_method='cash'
        $entries = CashBook::where('reference_type', 'purchase')
            ->where('type', 'cash_out')
            ->where('payment_method', 'cash')
            ->get();

        $this->info("Found {$entries->count()} purchase cash_out entries to check...");

        $fixed = 0;
        foreach ($entries as $entry) {
            $purchase = Purchase::withTrashed()->find($entry->reference_id);
            if ($purchase && $purchase->payment_type === 'Credit') {
                $this->line("  Removing bad entry ID={$entry->id} amount={$entry->amount} for {$purchase->purchase_number}");
                $entry->forceDelete();
                $fixed++;
            }
        }

        $this->info("Done! Removed {$fixed} incorrect CashBook entries for Credit purchases.");
        $this->info("Cash balance should now be correct.");
        return 0;
    }
}
